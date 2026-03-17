<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Project;
use App\Models\Payment;
use App\Models\Milestone;
use App\Models\Chat;
use App\Models\Notification;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Events\MessageSeen;

class ProjectController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Marketplace Projects
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $designer = Auth::user();

        $projects = Project::whereNull('designer_id')
            ->where('status','pending')
            ->where('approval_status','approved')
            ->when($designer->specialization,function($q) use ($designer){
                $q->where('property_type',$designer->specialization);
            })
            ->latest()
            ->paginate(6);

        $availableCount = Project::whereNull('designer_id')
            ->where('status','pending')
            ->where('approval_status','approved')
            ->count();

        $assignedCount = Project::where('designer_id',$designer->id)->count();

        $completedCount = Project::where('designer_id',$designer->id)
            ->where('status','completed')
            ->count();

        $releasedTotal = Payment::where('designer_id',$designer->id)
            ->where('status','released')
            ->sum('designer_amount');

        $fundedTotal = Payment::where('designer_id',$designer->id)
            ->where('status','funded')
            ->sum('amount');

        $monthlyEarnings = Payment::select(
                DB::raw('MONTH(released_at) as month'),
                DB::raw('SUM(designer_amount) as total')
            )
            ->where('designer_id',$designer->id)
            ->where('status','released')
            ->whereNotNull('released_at')
            ->groupBy(DB::raw('MONTH(released_at)'))
            ->pluck('total','month');

        return view('designer.projects',compact(
            'projects',
            'availableCount',
            'assignedCount',
            'completedCount',
            'releasedTotal',
            'fundedTotal',
            'monthlyEarnings'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | Assigned Projects
    |--------------------------------------------------------------------------
    */

    public function assigned()
    {
        $projects = Project::where('designer_id',Auth::id())
            ->with('milestones.payment')
            ->latest()
            ->paginate(6);

        return view('designer.assigned_projects',compact('projects'));
    }


    /*
    |--------------------------------------------------------------------------
    | Chat Page
    |--------------------------------------------------------------------------
    */

    public function chat(Project $project)
    {
        if ($project->designer_id !== Auth::id()) {
            abort(403);
        }

        $messages = Chat::where('project_id',$project->id)
            ->with('sender:id,name')
            ->orderBy('id','desc')
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Mark Delivered
        |--------------------------------------------------------------------------
        */

        $delivered = Chat::where('project_id',$project->id)
             ->where('sender_id','!=',Auth::id())
             ->whereNull('delivered_at')
             ->pluck('id');

         if($delivered->count()){

              Chat::whereIn('id',$delivered)->update([
                'delivered_at' => now()
    ]);
}

        /*
        |--------------------------------------------------------------------------
        | Mark Seen
        |--------------------------------------------------------------------------
        */

        $seenMessages = Chat::where('project_id',$project->id)
            ->where('sender_id','!=',Auth::id())
            ->whereNull('seen_at')
            ->pluck('id');

        if($seenMessages->count()){

            Chat::whereIn('id',$seenMessages)->update([
                'seen_at' => now(),
                'is_read' => true
            ]);

            broadcast(new MessageSeen(
                $project->id,
                $seenMessages
            ))->toOthers();
        }

        return view('designer.chat.index',compact(
            'project',
            'messages'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | Load Older Messages (Infinite Scroll)
    |--------------------------------------------------------------------------
    */

    public function loadMessages(Project $project)
    {
        if ($project->designer_id !== Auth::id()) {
            abort(403);
        }

        $lastId = request('last_id');

        $messages = Chat::where('project_id',$project->id)
            ->where('id','<',$lastId)
            ->with('sender:id,name')
            ->orderBy('id','desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }


    /*
    |--------------------------------------------------------------------------
    | Send Chat Message
    |--------------------------------------------------------------------------
    */

    public function sendMessage(Request $request, Project $project)
    {
        if ($project->designer_id !== Auth::id()) {
            abort(403);
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
                ->store('chat_files/'.date('Y/m'),'public');
        }

        DB::beginTransaction();

        try{

            $chat = Chat::create([
                'project_id' => $project->id,
                'sender_id' => Auth::id(),
                'message' => $request->message,
                'file_path' => $filePath,
                'is_read' => false
            ]);

            $chat->load('sender:id,name');

            broadcast(new MessageSent($chat))->toOthers();

            broadcast(new \App\Events\MessageNotification($project->client_id))->toOthers();

            /*
            |--------------------------------------------------------------------------
            | Notification
            |--------------------------------------------------------------------------
            */

            Notification::create([
                'user_id' => $project->client_id,
                'type' => 'chat',
                'message' => 'New message on project: '.$project->title,
                'link' => route('client.projects.chat',$project),
                'is_read' => false
            ]);

            DB::commit();

        }catch(\Throwable $e){

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
        if ($project->designer_id !== Auth::id()) {
            abort(403);
        }

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
    | Complete Milestone
    |--------------------------------------------------------------------------
    */

    public function completeMilestone(Project $project, Milestone $milestone)
    {
        if ($project->designer_id !== Auth::id()) {
            abort(403);
        }

        if ($milestone->project_id !== $project->id) {
            return back()->with('error','Invalid milestone.');
        }

        if (!$milestone->payment || $milestone->payment->status !== 'funded') {
            return back()->with('error','Escrow not funded.');
        }

        if ($milestone->status !== 'funded') {
            return back()->with('error','Milestone not ready.');
        }

        $milestone->update([
            'status'=>'approved',
            'approved_at'=>now(),
        ]);

        Notification::create([
            'user_id' => $project->client_id,
            'type' => 'milestone_completed',
            'message' => "Milestone '{$milestone->title}' completed for project ".$project->title,
            'link' => route('client.projects.show',$project),
            'is_read' => false
        ]);

        $project->logActivity(
            Auth::id(),
            'milestone_submitted',
            "Milestone '{$milestone->title}' submitted."
        );

        return back()->with('success','Milestone submitted.');
    }


    /*
    |--------------------------------------------------------------------------
    | Designer Earnings
    |--------------------------------------------------------------------------
    */

    public function earnings()
    {
        $designerId = Auth::id();

        $releasedTotal = Payment::where('designer_id',$designerId)
            ->where('status','released')
            ->sum('designer_amount');

        $fundedTotal = Payment::where('designer_id',$designerId)
            ->where('status','funded')
            ->sum('amount');

        $payments = Payment::with(['project','milestone'])
            ->where('designer_id',$designerId)
            ->latest()
            ->get();

        return view('designer.earnings',compact(
            'releasedTotal',
            'fundedTotal',
            'payments'
        ));
    }

}
