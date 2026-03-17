<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Milestone;
use App\Models\Notification;

class MilestoneController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | View Assigned Milestones
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $user = Auth::user();

        $milestones = Milestone::whereHas('project', function ($q) use ($user) {

                $q->where('designer_id', $user->id);

            })
            ->with('project')
            ->orderBy('created_at','desc')
            ->get();

        return view('designer.milestones.index', compact('milestones'));
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Milestone Completed
    |--------------------------------------------------------------------------
    */

    public function complete(Milestone $milestone)
    {

        $project = $milestone->project;

        if ($project->designer_id !== Auth::id()) {
            abort(403);
        }

        if ($milestone->status !== 'funded') {
            return back()->with('error','Milestone is not ready for completion.');
        }

        DB::transaction(function () use ($milestone,$project) {

            /*
            |--------------------------------------------------------------
            | Mark milestone completed
            |--------------------------------------------------------------
            */

            $milestone->update([
                'status' => 'completed'
            ]);

            /*
            |--------------------------------------------------------------
            | Notify Client
            |--------------------------------------------------------------
            */

            Notification::create([
                'user_id' => $project->client_id,
                'type' => 'milestone_completed',
                'message' => 'Designer completed milestone for project: '.$project->title,
                'link' => route('client.projects.show',$project),
                'is_read' => false
            ]);

            /*
            |--------------------------------------------------------------
            | Log Activity
            |--------------------------------------------------------------
            */

            if (method_exists($project,'logActivity')) {

                $project->logActivity(
                    Auth::id(),
                    'milestone_completed',
                    'Designer marked milestone completed.'
                );

            }

        });

        return back()->with('success','Milestone marked as completed.');

    }

}
