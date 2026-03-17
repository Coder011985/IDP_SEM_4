<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Project;
use App\Models\Bid;
use App\Models\Notification;
use App\Models\User;

class BidController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Submit Bid
    |--------------------------------------------------------------------------
    */

    public function submitBid(Request $request, Project $project)
    {

        /** @var User $user */
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Ensure Designer Role
        |--------------------------------------------------------------------------
        */

        if (!$user || !$user->isDesigner()) {
            abort(403, 'Only designers can submit bids.');
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Bidding On Own Project
        |--------------------------------------------------------------------------
        */

        if ($project->client_id === $user->id) {
            return back()->with('error', 'You cannot bid on your own project.');
        }

        /*
        |--------------------------------------------------------------------------
        | Allow Bidding Only On Approved Projects
        |--------------------------------------------------------------------------
        */

        if ($project->approval_status !== 'approved') {
            return back()->with('error', 'Project not approved yet.');
        }

        /*
        |--------------------------------------------------------------------------
        | Project Must Be Open For Bidding
        |--------------------------------------------------------------------------
        */

        if ($project->status !== 'pending') {
            return back()->with('error', 'Project is not open for bidding.');
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Bidding On Assigned Project
        |--------------------------------------------------------------------------
        */

        if (!is_null($project->designer_id)) {
            return back()->with('error', 'Project already assigned.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

            'amount' => [
                'required',
                'numeric',
                'min:1'
            ],

            'proposal' => [
                'required',
                'string',
                'min:10',
                'max:2000'
            ]

        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Bid Against Project Budget
        |--------------------------------------------------------------------------
        */

        if ($project->budget_min && $data['amount'] < $project->budget_min) {
            return back()->with('error', 'Bid amount cannot be lower than project minimum budget.');
        }

        if ($project->budget_max && $data['amount'] > $project->budget_max) {
            return back()->with('error', 'Bid amount exceeds project maximum budget.');
        }

        /*
        |--------------------------------------------------------------------------
        | Create Bid + Notification
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(function () use ($project, $data, $user) {

                /*
                |--------------------------------------------------------------------------
                | Prevent Duplicate Bids (Race Condition Safe)
                |--------------------------------------------------------------------------
                */

                $alreadyBid = Bid::where('project_id', $project->id)
                    ->where('designer_id', $user->id)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyBid) {
                    throw new \Exception('You already submitted a bid.');
                }

                /*
                |--------------------------------------------------------------------------
                | Create Bid
                |--------------------------------------------------------------------------
                */

                $bid = Bid::create([
                    'project_id'  => $project->id,
                    'designer_id' => $user->id,
                    'amount'      => $data['amount'],
                    'proposal'    => $data['proposal'],
                    'status'      => 'pending'
                ]);

                /*
                |--------------------------------------------------------------------------
                | Notify Client
                |--------------------------------------------------------------------------
                */

                Notification::create([
                    'user_id' => $project->client_id,
                    'type'    => 'bid_received',
                    'message' => 'You received a new bid for project: ' . $project->title,
                    'link'    => route('client.projects.bids', $project),
                    'is_read' => false
                ]);

                /*
                |--------------------------------------------------------------------------
                | Log Activity
                |--------------------------------------------------------------------------
                */

                if (method_exists($project, 'logActivity')) {

                    $project->logActivity(
                        $user->id,
                        'bid_submitted',
                        'Designer submitted a bid.'
                    );
                }

            });

        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Bid submitted successfully.');
    }
}
