<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Project;
use App\Models\Milestone;
use App\Models\Notification;

class MilestoneController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | View Project Milestones
    |--------------------------------------------------------------------------
    */

    public function index(Project $project)
    {
        if ($project->client_id !== Auth::id()) {
            abort(403);
        }

        $milestones = $project->milestones()
            ->orderBy('position')
            ->get();

        return view('client.milestones.index', compact('project', 'milestones'));
    }


    /*
    |--------------------------------------------------------------------------
    | Fund Milestone (Escrow)
    |--------------------------------------------------------------------------
    */

    public function fund(Milestone $milestone)
    {
        $project = $milestone->project;

        if ($project->client_id !== Auth::id()) {
            abort(403);
        }

        if ($milestone->status !== 'pending') {
            return back()->with('error', 'Milestone cannot be funded.');
        }

        DB::transaction(function () use ($milestone, $project) {

            /*
            |--------------------------------------------------------------
            | Mark milestone funded
            |--------------------------------------------------------------
            */

            $milestone->update([
                'status' => 'funded'
            ]);

            /*
            |--------------------------------------------------------------
            | Notify Designer
            |--------------------------------------------------------------
            */

            Notification::create([
                'user_id' => $project->designer_id,
                'type' => 'milestone_funded',
                'message' => 'Milestone funded for project: '.$project->title,
                'link' => route('designer.projects.show', $project),
                'is_read' => false
            ]);

            /*
            |--------------------------------------------------------------
            | Log Activity
            |--------------------------------------------------------------
            */

            if (method_exists($project, 'logActivity')) {

                $project->logActivity(
                    Auth::id(),
                    'milestone_funded',
                    'Client funded milestone.'
                );

            }

        });

        return back()->with('success','Milestone funded successfully.');

    }


    /*
    |--------------------------------------------------------------------------
    | Approve Completed Milestone
    |--------------------------------------------------------------------------
    */

    public function approve(Milestone $milestone)
    {
        $project = $milestone->project;

        if ($project->client_id !== Auth::id()) {
            abort(403);
        }

        if ($milestone->status !== 'completed') {
            return back()->with('error','Milestone not completed yet.');
        }

        DB::transaction(function () use ($milestone, $project) {

            /*
            |--------------------------------------------------------------
            | Mark milestone approved
            |--------------------------------------------------------------
            */

            $milestone->update([
                'status' => 'approved'
            ]);

            /*
            |--------------------------------------------------------------
            | Notify Admin
            |--------------------------------------------------------------
            */

            Notification::create([
                'user_id' => 1, // admin id (temporary)
                'type' => 'milestone_ready_for_release',
                'message' => 'Milestone ready for payment release.',
                'link' => route('admin.projects.show', $project),
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
                    'milestone_approved',
                    'Client approved milestone work.'
                );

            }

        });

        return back()->with('success','Milestone approved.');

    }

}
