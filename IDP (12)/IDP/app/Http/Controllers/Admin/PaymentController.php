<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Milestone;
use App\Models\Payment;
use App\Models\Notification;

class PaymentController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | List Approved Milestones Waiting For Payment Release
    |--------------------------------------------------------------------------
    */

    public function index()
    {

        $milestones = Milestone::where('status','approved')
            ->with([
                'project',
                'project.client',
                'project.designer'
            ])
            ->latest()
            ->paginate(10);

        return view('admin.payments.index', compact('milestones'));

    }



    /*
    |--------------------------------------------------------------------------
    | Release Payment
    |--------------------------------------------------------------------------
    */

    public function release(Milestone $milestone)
    {

        if ($milestone->status !== 'approved') {
            return back()->with('error','Milestone not ready for payment.');
        }

        $project = $milestone->project;

        try {

            DB::transaction(function () use ($milestone,$project) {

                /*
                |--------------------------------------------------------------------------
                | Payment Calculations
                |--------------------------------------------------------------------------
                */

                $amount = $milestone->amount;

                $commissionRate = 0.10; // 10% platform fee
                $commission = $amount * $commissionRate;
                $designerAmount = $amount - $commission;


                /*
                |--------------------------------------------------------------------------
                | Create Payment Record
                |--------------------------------------------------------------------------
                */

                Payment::create([

                    'project_id'        => $project->id,
                    'milestone_id'      => $milestone->id,
                    'client_id'         => $project->client_id,
                    'designer_id'       => $project->designer_id,
                    'amount'            => $amount,
                    'commission_amount' => $commission,
                    'designer_amount'   => $designerAmount,
                    'status'            => 'released'

                ]);


                /*
                |--------------------------------------------------------------------------
                | Update Milestone Status
                |--------------------------------------------------------------------------
                */

                $milestone->update([
                    'status' => 'released'
                ]);


                /*
                |--------------------------------------------------------------------------
                | Notify Designer
                |--------------------------------------------------------------------------
                */

                Notification::create([

                    'user_id' => $project->designer_id,
                    'type'    => 'payment_released',
                    'message' => 'Payment released for milestone in project: '.$project->title,
                    'link'    => route('designer.projects.show',$project),
                    'is_read' => false

                ]);


                /*
                |--------------------------------------------------------------------------
                | Activity Log
                |--------------------------------------------------------------------------
                */

                if (method_exists($project,'logActivity')) {

                    $project->logActivity(
                        Auth::id(),
                        'payment_released',
                        'Admin released milestone payment.'
                    );

                }

            });

        } catch (\Exception $e) {

            return back()->with('error','Payment release failed.');

        }

        return back()->with('success','Payment released successfully.');

    }

}
