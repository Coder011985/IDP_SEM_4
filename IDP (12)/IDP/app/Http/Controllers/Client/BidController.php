<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Project;
use App\Models\Bid;
use App\Models\Milestone;
use App\Models\Notification;

class BidController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | View Project Bids
    |--------------------------------------------------------------------------
    */

    public function index(Project $project)
    {
        if ($project->client_id !== Auth::id()) {
            abort(403);
        }

        $project->load(['activities.user']);

        $bids = $project->bids()
            ->with('designer')
            ->latest()
            ->paginate(10);

        return view('client.bids.index', compact('project', 'bids'));
    }


    /*
    |--------------------------------------------------------------------------
    | Accept Bid
    |--------------------------------------------------------------------------
    */

    public function accept(Bid $bid)
    {
        $project = $bid->project;

        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        */

        if ($project->client_id !== Auth::id()) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Double Assignment
        |--------------------------------------------------------------------------
        */

        if ($project->designer_id !== null) {
            return back()->with('error', 'Project already assigned.');
        }

        /*
        |--------------------------------------------------------------------------
        | Project Must Be Open
        |--------------------------------------------------------------------------
        */

        if ($project->status !== 'pending') {
            return back()->with('error', 'Project is not open.');
        }

        /*
        |--------------------------------------------------------------------------
        | Bid Must Be Pending
        |--------------------------------------------------------------------------
        */

        if ($bid->status !== 'pending') {
            return back()->with('error', 'This bid is no longer valid.');
        }

        try {

            DB::transaction(function () use ($bid, $project) {

                /*
                |--------------------------------------------------------------------------
                | Lock Project Row (Prevent Race Condition)
                |--------------------------------------------------------------------------
                */

                $project = Project::lockForUpdate()->find($project->id);

                /*
                |--------------------------------------------------------------------------
                | Accept Selected Bid
                |--------------------------------------------------------------------------
                */

                $bid->update([
                    'status' => 'accepted'
                ]);

                /*
                |--------------------------------------------------------------------------
                | Reject Other Bids
                |--------------------------------------------------------------------------
                */

                $project->bids()
                    ->where('id', '!=', $bid->id)
                    ->update(['status' => 'rejected']);

                /*
                |--------------------------------------------------------------------------
                | Assign Designer
                |--------------------------------------------------------------------------
                */

                $project->update([
                    'designer_id' => $bid->designer_id,
                    'status'      => 'assigned'
                ]);

                /*
                |--------------------------------------------------------------------------
                | Create Default Milestone
                |--------------------------------------------------------------------------
                */

                Milestone::create([
                    'project_id' => $project->id,
                    'title'      => 'Project Completion',
                    'amount'     => $bid->amount,
                    'position'   => 1,
                    'status'     => 'pending'
                ]);

                /*
                |--------------------------------------------------------------------------
                | Notify Designer
                |--------------------------------------------------------------------------
                */

                Notification::create([
                    'user_id' => $bid->designer_id,
                    'type'    => 'bid_accepted',
                    'message' => 'Your bid was accepted for project: ' . $project->title,
                    'link'    => route('designer.projects.show', $project),
                    'is_read' => false
                ]);

                /*
                |--------------------------------------------------------------------------
                | Activity Log
                |--------------------------------------------------------------------------
                */

                if (method_exists($project, 'logActivity')) {

                    $project->logActivity(
                        Auth::id(),
                        'bid_accepted',
                        'Client accepted a bid and assigned designer.'
                    );
                }

            });

        } catch (\Exception $e) {

            return back()->with('error', 'Something went wrong. Please try again.');
        }

        return back()->with('success', 'Bid accepted and project assigned.');
    }
}
