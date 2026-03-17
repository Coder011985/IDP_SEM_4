<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Cached Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $stats = Cache::remember(
            "client_dashboard_stats_{$userId}",
            20,
            function () use ($userId) {

                return Project::where('client_id', $userId)
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed
                    ')
                    ->first();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Cached Recent Projects
        |--------------------------------------------------------------------------
        */

        $recentProjects = Cache::remember(
            "client_recent_projects_{$userId}",
            20,
            function () use ($userId) {

                return Project::where('client_id', $userId)
                    ->select(
                        'id',
                        'title',
                        'status',
                        'budget_min',
                        'budget_max',
                        'deadline'
                    )
                    ->latest()
                    ->limit(5)
                    ->get();

            }
        );


        return view('client.dashboard', [

            'total' => $stats->total ?? 0,
            'pending' => $stats->pending ?? 0,
            'completed' => $stats->completed ?? 0,
            'recentProjects' => $recentProjects

        ]);
    }
}
