<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Milestone;
use App\Models\Notification;

class MilestoneService
{

    /*
    |--------------------------------------------------------------------------
    | Fund Milestone (Escrow Payment)
    |--------------------------------------------------------------------------
    */

    public static function fund(Milestone $milestone, $method)
    {

        DB::transaction(function () use ($milestone,$method){

            /*
            |--------------------------------------------------------------------------
            | Ensure Payment Exists
            |--------------------------------------------------------------------------
            */

            if(!$milestone->payment){
                throw new \Exception('Milestone payment record missing.');
            }


            /*
            |--------------------------------------------------------------------------
            | Fund Payment
            |--------------------------------------------------------------------------
            */

            $milestone->payment->markFunded();


            /*
            |--------------------------------------------------------------------------
            | Notify Designer
            |--------------------------------------------------------------------------
            */

            Notification::create([

                'user_id' => $milestone->project->designer_id,

                'type' => 'milestone_funded',

                'message' => 'Milestone funded for project: '.$milestone->project->title,

                'link' => route('designer.projects.assigned'),

                'is_read' => false

            ]);


            /*
            |--------------------------------------------------------------------------
            | Log Activity
            |--------------------------------------------------------------------------
            */

            $milestone->project->logActivity(

                Auth::id(),

                'milestone_funded',

                "Milestone funded using {$method}"

            );

        });

    }

}
