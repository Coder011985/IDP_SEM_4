<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\Project;
use App\Models\Payment;
use App\Models\Review;

class AnalyticsService
{

    /*
    |--------------------------------------------------------------------------
    | Predict Next Month Revenue
    |--------------------------------------------------------------------------
    */

    public function predictedRevenue()
    {

        $lastMonths = Payment::where('status','released')
            ->whereNotNull('released_at')
            ->selectRaw('MONTH(released_at) as month, SUM(commission_amount) as total')
            ->groupBy('month')
            ->orderByDesc('month')
            ->take(6)
            ->pluck('total');

        if($lastMonths->count() === 0){
            return 0;
        }

        $average = $lastMonths->avg();

        // basic growth prediction (10%)
        return round($average * 1.1,2);

    }



    /*
    |--------------------------------------------------------------------------
    | Detect Inactive Designers
    |--------------------------------------------------------------------------
    */

    public function inactiveDesigners()
    {

        return User::where('role','designer')
            ->whereDoesntHave('designerProjects',function($q){

                $q->where('created_at','>=',now()->subMonths(3));

            })
            ->count();

    }



    /*
    |--------------------------------------------------------------------------
    | Top Cities by Project Demand
    |--------------------------------------------------------------------------
    */

    public function topCities()
    {

        return Project::selectRaw('location, COUNT(*) as total')
            ->whereNotNull('location')
            ->where('location','!=','')
            ->groupBy('location')
            ->orderByDesc('total')
            ->take(5)
            ->get();

    }



    /*
    |--------------------------------------------------------------------------
    | Most Requested Interior Styles
    |--------------------------------------------------------------------------
    */

    public function popularStyles()
    {

        return Project::selectRaw('style, COUNT(*) as total')
            ->whereNotNull('style')
            ->groupBy('style')
            ->orderByDesc('total')
            ->take(5)
            ->get();

    }



    /*
    |--------------------------------------------------------------------------
    | Top Performing Designers
    |--------------------------------------------------------------------------
    */

    public function topDesigners()
    {

        return User::where('role','designer')
            ->withCount([
                'designerProjects as completed_projects_count' => function($q){
                    $q->where('status','completed');
                }
            ])
            ->withAvg('receivedReviews','rating')
            ->orderByDesc('received_reviews_avg_rating')
            ->orderByDesc('completed_projects_count')
            ->take(5)
            ->get();

    }



    /*
    |--------------------------------------------------------------------------
    | Designer Reputation Score
    |--------------------------------------------------------------------------
    */

    public function designerScore($designerId)
    {

        $avgRating = Review::where('designer_id',$designerId)->avg('rating') ?? 0;

        $completed = Project::where('designer_id',$designerId)
            ->where('status','completed')
            ->count();

        return round(($avgRating * 20) + ($completed * 2),2);

    }

}
