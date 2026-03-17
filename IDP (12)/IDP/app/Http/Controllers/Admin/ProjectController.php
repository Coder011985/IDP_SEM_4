<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Payment;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Dispute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Project List
    |--------------------------------------------------------------------------
    */

    public function index()
    {

        $projects = Project::with([
            'client',
            'designer',
            'milestones.payment',
            'disputes'
        ])
        ->latest()
        ->paginate(15);

        return view('admin.projects.index', compact('projects'));

    }



    /*
    |--------------------------------------------------------------------------
    | Approve Project
    |--------------------------------------------------------------------------
    */

    public function approve(Project $project)
    {

        if ($project->approval_status !== 'pending') {
            return back()->with('error','Project already processed.');
        }

        $project->update([
            'approval_status' => 'approved',
            'status' => 'pending'
        ]);

        Notification::create([
            'user_id' => $project->client_id,
            'type' => 'project_approved',
            'message' => 'Your project "'.$project->title.'" has been approved.',
            'link' => route('client.projects.show',$project),
            'is_read' => false
        ]);

        $project->logActivity(
            Auth::id(),
            'project_approved',
            'Admin approved the project.'
        );

        return back()->with('success','Project approved.');

    }



    /*
    |--------------------------------------------------------------------------
    | Reject Project
    |--------------------------------------------------------------------------
    */

    public function reject(Project $project)
    {

        if ($project->approval_status !== 'pending') {
            return back()->with('error','Project already processed.');
        }

        $project->update([
            'approval_status' => 'rejected',
            'status' => 'cancelled'
        ]);

        Notification::create([
            'user_id' => $project->client_id,
            'type' => 'project_rejected',
            'message' => 'Your project "'.$project->title.'" was rejected by admin.',
            'link' => route('client.projects'),
            'is_read' => false
        ]);

        $project->logActivity(
            Auth::id(),
            'project_rejected',
            'Admin rejected the project.'
        );

        return back()->with('success','Project rejected.');

    }



    /*
    |--------------------------------------------------------------------------
    | Release Milestone Payment
    |--------------------------------------------------------------------------
    */

    public function releaseMilestone(Project $project, Milestone $milestone)
    {

        if ($milestone->project_id !== $project->id) {
            return back()->with('error','Invalid milestone.');
        }

        if (!$milestone->payment || $milestone->payment->status !== 'funded') {
            return back()->with('error','Milestone not funded.');
        }

        if ($milestone->status !== 'approved') {
            return back()->with('error','Milestone must be approved first.');
        }

        DB::transaction(function () use ($project,$milestone){

            $payment = $milestone->payment;

            $payment->releaseToDesigner();

            $progress = $project->calculateProgress();

            $project->update([
                'progress' => $progress
            ]);

            if ($project->milestones()->where('status','!=','released')->count() === 0) {

                $project->update([
                    'status' => 'completed',
                    'progress' => 100
                ]);

            }

            Notification::create([
                'user_id' => $project->designer_id,
                'type' => 'milestone_released',
                'message' => "Milestone '{$milestone->title}' payment released.",
                'link' => route('designer.projects.assigned'),
                'is_read' => false
            ]);

            $project->logActivity(
                Auth::id(),
                'milestone_released',
                "Milestone '{$milestone->title}' payment released."
            );

        });

        return back()->with('success','Milestone released successfully.');

    }



    /*
    |--------------------------------------------------------------------------
    | View Disputes
    |--------------------------------------------------------------------------
    */

    public function disputes()
    {

        $disputes = Dispute::with([
            'project.client',
            'project.designer',
            'milestone'
        ])
        ->latest()
        ->paginate(20);

        return view('admin.disputes.index',compact('disputes'));

    }



    /*
    |--------------------------------------------------------------------------
    | Resolve Dispute
    |--------------------------------------------------------------------------
    */

    public function resolveDispute(Dispute $dispute)
    {

        DB::transaction(function() use ($dispute){

            $project = $dispute->project;
            $milestone = $dispute->milestone;

            if(!$milestone){
                return;
            }

            $payment = $milestone->payment;

            if(!$payment){
                return;
            }

            if($dispute->resolution === 'refund_client'){

                $payment->update([
                    'status' => 'refunded'
                ]);

                Notification::create([
                    'user_id' => $project->client_id,
                    'type' => 'dispute_refund',
                    'message' => 'Your dispute was resolved. Payment refunded.',
                    'link' => route('client.projects.show',$project),
                    'is_read' => false
                ]);

            }

            if($dispute->resolution === 'pay_designer'){

                $payment->releaseToDesigner();

                Notification::create([
                    'user_id' => $project->designer_id,
                    'type' => 'dispute_designer_paid',
                    'message' => 'Dispute resolved. Payment released to you.',
                    'link' => route('designer.projects.assigned'),
                    'is_read' => false
                ]);

            }

            $dispute->update([
                'status' => 'resolved',
                'resolved_by' => Auth::id(),
                'resolved_at' => now()
            ]);

            $project->logActivity(
                Auth::id(),
                'dispute_resolved',
                'Admin resolved a dispute.'
            );

        });

        return back()->with('success','Dispute resolved.');

    }



    /*
    |--------------------------------------------------------------------------
    | Revenue Dashboard
    |--------------------------------------------------------------------------
    */

    public function revenue()
    {

        $selectedYear = request('year', now()->year);

        $years = Payment::where('status','released')
            ->whereNotNull('released_at')
            ->selectRaw('YEAR(released_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $totalCommission = Payment::where('status','released')
            ->whereYear('released_at',$selectedYear)
            ->sum('commission_amount');

        $totalDesignerPayout = Payment::where('status','released')
            ->whereYear('released_at',$selectedYear)
            ->sum('designer_amount');

        $totalReleased = Payment::where('status','released')
            ->whereYear('released_at',$selectedYear)
            ->sum('amount');

        $totalEscrow = Payment::where('status','funded')
            ->sum('amount');

        $payments = Payment::with(['project.client','project.designer','milestone'])
            ->where('status','released')
            ->whereYear('released_at',$selectedYear)
            ->latest('released_at')
            ->paginate(20);

        $monthlyRevenue = Payment::select(
                DB::raw('MONTH(released_at) as month'),
                DB::raw('SUM(commission_amount) as total_commission'),
                DB::raw('SUM(designer_amount) as total_designer'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('status','released')
            ->whereYear('released_at',$selectedYear)
            ->whereNotNull('released_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.revenue', compact(
            'totalCommission',
            'totalDesignerPayout',
            'totalReleased',
            'totalEscrow',
            'payments',
            'monthlyRevenue',
            'years',
            'selectedYear'
        ));

    }

}
