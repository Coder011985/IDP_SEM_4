<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\Bid;
use App\Models\Milestone;
use App\Models\User;
use App\Models\Chat;
use App\Models\Notification;
use App\Models\Payment;

use App\Services\MilestoneService;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Events\MessageDelivered;

class ProjectController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Client Projects
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $projects = Project::clientProjects(Auth::id())
            ->with([
                'designer:id,name',
                'milestones:id,project_id,status,amount'
            ])
            ->latest()
            ->paginate(8);

        return view('client.projects.index', compact('projects'));
    }


    /*
    |--------------------------------------------------------------------------
    | Create Project
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $preselectedDesigner = null;

        if ($request->filled('designer_id')) {

            $preselectedDesigner = User::where('role','designer')
                ->select('id','name')
                ->find($request->designer_id);
        }

        return view('client.projects.create', compact('preselectedDesigner'));
    }


    /*
    |--------------------------------------------------------------------------
    | Store Project
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {

        $request->validate([
            'title'=>'required|string|max:255',
            'description'=>'required|string',
            'budget_min'=>'required|numeric|min:1',
            'budget_max'=>'required|numeric|gte:budget_min',
            'type'=>'required|string|max:100',
            'location'=>'nullable|string|max:150',
            'area_sqft'=>'nullable|integer',
            'deadline'=>'nullable|date',
            'style'=>'nullable|string|max:50',
            'property_type'=>'required|in:residential,commercial,office,luxury',
            'requirements'=>'nullable|string',
        ]);

        $designerId = $request->designer_id ?? null;

        if ($designerId) {

            $designer = User::where('role','designer')->find($designerId);

            if (!$designer) {
                return back()->with('error','Invalid designer selected.');
            }
        }

        $project = Project::create([

            'client_id'=>Auth::id(),
            'designer_id'=>$designerId,

            'title'=>$request->title,
            'description'=>$request->description,

            'budget_min'=>$request->budget_min,
            'budget_max'=>$request->budget_max,

            'type'=>$request->type,
            'location'=>$request->location,
            'area_sqft'=>$request->area_sqft,
            'deadline'=>$request->deadline,

            'style'=>$request->style,
            'property_type'=>$request->property_type,
            'requirements'=>$request->requirements,

            'status'=>$designerId ? 'assigned' : 'pending',
            'approval_status'=>'pending',
            'progress'=>0
        ]);

        $project->logActivity(
            Auth::id(),
            'project_created',
            $designerId
                ? 'Client hired designer directly.'
                : 'Client created project.'
        );

        return redirect()
            ->route('client.projects.index')
            ->with('success','Project created successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | View Project
    |--------------------------------------------------------------------------
    */

    public function show(Project $project)
    {
        abort_if($project->client_id !== Auth::id(),403);

        $project->load([
            'activities.user:id,name',
            'bids.designer:id,name',
            'milestones.payment:id,milestone_id,status'
        ]);

        return view('client.projects.show', compact('project'));
    }


    /*
    |--------------------------------------------------------------------------
    | Chat Page
    |--------------------------------------------------------------------------
    */

    public function chat(Project $project)
    {
        abort_if($project->client_id !== Auth::id(),403);

        $messages = $project->chats()
            ->with('sender:id,name')
            ->orderBy('id','asc')
            ->limit(50)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Mark Delivered
        |--------------------------------------------------------------------------
        */

        $delivered = $project->chats()
            ->where('sender_id','!=',Auth::id())
            ->whereNull('delivered_at')
            ->pluck('id');

        if($delivered->count()){

            Chat::whereIn('id',$delivered)->update([
                'delivered_at' => now()
            ]);

            broadcast(new MessageDelivered(
                $project->id,
                $delivered
            ))->toOthers();
        }

        return view('client.chat.index', compact('project','messages'));
    }


    /*
    |--------------------------------------------------------------------------
    | Send Chat Message
    |--------------------------------------------------------------------------
    */

    public function sendMessage(Request $request, Project $project)
    {
        abort_if($project->client_id !== Auth::id(),403);

        $rateKey = 'chat_rate_'.Auth::id().'_'.$project->id;

        if (Cache::has($rateKey)) {
            return back()->with('error','You are sending messages too quickly.');
        }

        $request->validate([
            'message' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,zip'
        ]);

        if (!$request->filled('message') && !$request->hasFile('file')) {
         return back()->with('error','Empty message.');
        }

        $filePath = null;

        if ($request->hasFile('file')) {

            $filePath = $request->file('file')
                ->store('chat_files/'.date('Y/m'), 'public');
        }

        DB::beginTransaction();

        try{

            $chat = Chat::create([
                'project_id'=>$project->id,
                'sender_id'=>Auth::id(),
                'message'=>$request->message,
                'file_path'=>$filePath,
                'is_read'=>false
            ]);

            $chat->load('sender:id,name');

            broadcast(new MessageSent($chat))->toOthers();

            if($project->designer_id){
          broadcast(new \App\Events\MessageNotification($project->designer_id))->toOthers();
                }

            if ($project->designer_id) {

                Notification::create([
                    'user_id' => $project->designer_id,
                    'type' => 'chat',
                    'message' => 'New message on project: '.$project->title,
                    'link' => route('designer.projects.chat',$project),
                    'is_read' => false
                ]);
            }

            Cache::put($rateKey,true,2);

            DB::commit();

        }catch (\Exception $e) {

    DB::rollBack();

    Log::error($e->getMessage());

    return back()->with('error','Message failed.');
}

        return back();
    }


    /*
    |--------------------------------------------------------------------------
    | Typing Indicator
    |--------------------------------------------------------------------------
    */

    public function typing(Project $project)
    {
        abort_if($project->client_id !== Auth::id(),403);

        broadcast(
            new UserTyping(
                $project->id,
                Auth::id(),
                Auth::user()->name
            )
        )->toOthers();

        return response()->json([
            'status' => 'typing'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | View Bids
    |--------------------------------------------------------------------------
    */

    public function viewBids(Project $project)
    {
        abort_if($project->client_id !== Auth::id(),403);

        $bids = $project->bids()
            ->with('designer:id,name')
            ->latest()
            ->get();

        return view('client.bids.index', compact('project','bids'));
    }


    /*
    |--------------------------------------------------------------------------
    | Accept Bid
    |--------------------------------------------------------------------------
    */

    public function acceptBid(Project $project, Bid $bid)
    {
        abort_if($project->client_id !== Auth::id(),403);

        if ($bid->project_id !== $project->id) {
            return back()->with('error','Invalid bid.');
        }

        if ($project->approval_status !== 'approved') {
            return back()->with('error','Project not approved by admin.');
        }

        if ($project->status !== 'pending') {
            return back()->with('error','Project not open for bidding.');
        }

        DB::transaction(function () use ($project,$bid) {

            $bid->update(['status'=>'accepted']);

            $project->bids()
                ->where('id','!=',$bid->id)
                ->update(['status'=>'rejected']);

            $project->update([
                'designer_id'=>$bid->designer_id,
                'status'=>'assigned',
                'progress'=>0
            ]);

            $milestone = Milestone::create([
                'project_id'=>$project->id,
                'title'=>'Initial Milestone',
                'amount'=>$bid->amount,
                'position'=>1,
                'status'=>'pending'
            ]);

            Payment::create([
                'project_id'=>$project->id,
                'milestone_id'=>$milestone->id,
                'client_id'=>Auth::id(),
                'designer_id'=>$bid->designer_id,
                'amount'=>$milestone->amount,
                'commission_amount'=>0,
                'designer_amount'=>0,
                'status'=>'pending'
            ]);

            Notification::create([
                'user_id' => $bid->designer_id,
                'type' => 'bid_accepted',
                'message' => 'Your bid was accepted for project: '.$project->title,
                'link' => route('designer.projects.assigned'),
                'is_read' => false
            ]);

            $project->clearCache();

            $project->logActivity(
                Auth::id(),
                'bid_accepted',
                'Designer assigned and milestone created.'
            );
        });

        return back()->with('success','Designer assigned.');
    }


    /*
    |--------------------------------------------------------------------------
    | Fund Milestone
    |--------------------------------------------------------------------------
    */

    public function fundMilestone(Request $request, Milestone $milestone)
    {
        $project = $milestone->project;

        abort_if($project->client_id !== Auth::id(),403);

        if (!$milestone->payment || $milestone->payment->status !== 'pending') {
            return back()->with('error','Milestone already funded.');
        }

        $request->validate([
            'payment_method' => 'required|in:demo,upi,bank,razorpay'
        ]);

        MilestoneService::fund($milestone,$request->payment_method);

        $project->clearCache();

        return back()->with('success','Milestone funded successfully.');
    }

}
