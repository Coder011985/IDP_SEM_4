<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\RevenueExport;
use App\Models\Payment;
use App\Models\User;
use App\Models\Project;

use App\Services\Analytics\AnalyticsService;

class AnalyticsController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Admin Analytics Dashboard
    |--------------------------------------------------------------------------
    */

    public function index()
    {

        $range = request('range','monthly');

        $analytics = Cache::remember(
            "analytics_dashboard_$range",
            now()->addSeconds(60),
            fn() => $this->buildAnalytics($range)
        );


        /*
        |--------------------------------------------------------------------------
        | AI Insights (Cached)
        |--------------------------------------------------------------------------
        */

        $aiInsights = Cache::remember(
            'analytics_ai_insights',
            now()->addMinutes(5),
            function () {

                $ai = new AnalyticsService();

                return [
                    'predictedRevenue' => $ai->predictedRevenue(),
                    'inactiveDesigners' => $ai->inactiveDesigners(),
                    'topCities' => $ai->topCities()
                ];

            }
        );

        $analytics = array_merge($analytics,$aiInsights);

        return view('admin.analytics.index',$analytics);

    }



    /*
    |--------------------------------------------------------------------------
    | Build Analytics Data
    |--------------------------------------------------------------------------
    */

    private function buildAnalytics($range)
    {

        /*
        |--------------------------------------------------------------------------
        | Platform Stats
        |--------------------------------------------------------------------------
        */

        $totalRevenue = Payment::where('status','released')
            ->sum('commission_amount');

        $totalTransactions = Payment::count();

        $totalProjects = Project::count();

        $totalDesigners = User::where('role','designer')->count();

        $totalClients = User::where('role','client')->count();

        $onlineUsers = User::where(
            'last_seen_at',
            '>=',
            now()->subMinutes(2)
        )->count();



        /*
        |--------------------------------------------------------------------------
        | Date Group Format
        |--------------------------------------------------------------------------
        */

        $groupFormat = match ($range) {

            'daily' => 'DATE(%s)',
            'weekly' => 'WEEK(%s)',
            'yearly' => 'YEAR(%s)',
            default => 'MONTH(%s)'

        };



        /*
        |--------------------------------------------------------------------------
        | Growth Analytics
        |--------------------------------------------------------------------------
        */

        $revenueGrowth = $this->growthQuery(
            Payment::where('status','released'),
            'released_at',
            'SUM(commission_amount)',
            $groupFormat
        );

        $userGrowth = $this->growthQuery(
            User::query(),
            'created_at',
            'COUNT(*)',
            $groupFormat
        );

        $projectGrowth = $this->growthQuery(
            Project::query(),
            'created_at',
            'COUNT(*)',
            $groupFormat
        );



        /*
        |--------------------------------------------------------------------------
        | Project Status Distribution
        |--------------------------------------------------------------------------
        */

        $projectStatus = Project::select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->pluck('total','status');



        /*
        |--------------------------------------------------------------------------
        | Revenue Comparison (Fixed Year Bug)
        |--------------------------------------------------------------------------
        */

        $currentRevenue = Payment::where('status','released')
            ->whereYear('released_at',now()->year)
            ->whereMonth('released_at',now()->month)
            ->sum('commission_amount');

        $previousRevenue = Payment::where('status','released')
            ->whereYear('released_at',now()->subMonth()->year)
            ->whereMonth('released_at',now()->subMonth()->month)
            ->sum('commission_amount');



        /*
        |--------------------------------------------------------------------------
        | Success Rate
        |--------------------------------------------------------------------------
        */

        $completedProjects = Project::where('status','completed')->count();

        $successRate = $totalProjects
            ? round(($completedProjects / $totalProjects) * 100,2)
            : 0;



        /*
        |--------------------------------------------------------------------------
        | Top Designers
        |--------------------------------------------------------------------------
        */

        $topDesigners = User::where('role','designer')
            ->withCount([
                'designerProjects as completed_projects_count' => function ($q) {
                    $q->where('status','completed');
                }
            ])
            ->withAvg('receivedReviews','rating')
            ->orderByDesc('completed_projects_count')
            ->take(5)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Recent Users
        |--------------------------------------------------------------------------
        */

        $recentUsers = User::latest()
            ->take(5)
            ->get([
                'id',
                'name',
                'role',
                'last_seen_at'
            ]);



        return [

            'range'=>$range,

            'totalRevenue'=>$totalRevenue,
            'totalTransactions'=>$totalTransactions,
            'totalProjects'=>$totalProjects,
            'totalDesigners'=>$totalDesigners,
            'totalClients'=>$totalClients,
            'onlineUsers'=>$onlineUsers,

            'revenueGrowth'=>$revenueGrowth,
            'userGrowth'=>$userGrowth,
            'projectGrowth'=>$projectGrowth,

            'projectStatus'=>$projectStatus,

            'currentRevenue'=>$currentRevenue,
            'previousRevenue'=>$previousRevenue,

            'successRate'=>$successRate,
            'topDesigners'=>$topDesigners,
            'recentUsers'=>$recentUsers

        ];

    }



    /*
    |--------------------------------------------------------------------------
    | Generic Growth Query Builder
    |--------------------------------------------------------------------------
    */

    private function growthQuery($query,$column,$aggregate,$groupFormat)
    {

        return $query
            ->selectRaw(
                sprintf($groupFormat,$column).' as period, '.$aggregate.' as total'
            )
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total','period');

    }



    /*
    |--------------------------------------------------------------------------
    | Live Analytics API
    |--------------------------------------------------------------------------
    */

    public function liveData()
    {

        return response()->json([

            'revenue' => Payment::where('status','released')
                ->sum('commission_amount'),

            'transactions' => Payment::count(),

            'projects' => Project::count(),

            'designers' => User::where('role','designer')->count(),

            'clients' => User::where('role','client')->count(),

            'online' => User::where(
                'last_seen_at',
                '>=',
                now()->subMinutes(2)
            )->count()

        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Export Revenue Excel
    |--------------------------------------------------------------------------
    */

    public function exportRevenue($year)
    {

        return Excel::download(
            new RevenueExport($year),
            "IDP_Revenue_$year.xlsx"
        );

    }

}
