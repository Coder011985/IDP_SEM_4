<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Models\User;
use App\Models\Project;
use App\Models\Review;
use App\Models\DesignerPortfolio;
use Illuminate\Support\Facades\Auth;

class DesignerController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Public Designers Listing (Search + Filters + Ranking)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {

        $query = User::where('role','designer')

            ->withCount([
                'designerProjects as completed_projects_count' => function ($q) {
                    $q->where('status','completed');
                },
                'receivedReviews as reviews_count'
            ])

            ->withAvg('receivedReviews','rating');



        /*
        |--------------------------------------------------------------------------
        | Search by Name
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $query->where('name','like','%'.$request->search.'%');
        }



        /*
        |--------------------------------------------------------------------------
        | Filter by Specialization
        |--------------------------------------------------------------------------
        */

        if ($request->filled('specialization')) {

            $query->where('specialization',$request->specialization);
        }



        /*
        |--------------------------------------------------------------------------
        | Sorting Options
        |--------------------------------------------------------------------------
        */

        if ($request->filled('sort')) {

            if ($request->sort === 'rating') {

                $query->orderByDesc('received_reviews_avg_rating');
            }

            if ($request->sort === 'projects') {

                $query->orderByDesc('completed_projects_count');
            }

            if ($request->sort === 'reviews') {

                $query->orderByDesc('reviews_count');
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | Default Marketplace Ranking (Auto Ranking Algorithm)
            |--------------------------------------------------------------------------
            */

            $query
                ->orderByDesc('received_reviews_avg_rating')
                ->orderByDesc('completed_projects_count')
                ->orderByDesc('reviews_count')
                ->latest();
        }



        $designers = $query
            ->paginate(9)
            ->withQueryString();



        /*
        |--------------------------------------------------------------------------
        | Specializations List (Filter Options)
        |--------------------------------------------------------------------------
        */

        $specializations = Cache::remember(
            'designer_specializations',
            300,
            function () {

                return User::where('role','designer')
                    ->whereNotNull('specialization')
                    ->distinct()
                    ->pluck('specialization');
            }
        );



        return view('public.designers.index', compact(
            'designers',
            'specializations'
        ));
    }



    /*
    |--------------------------------------------------------------------------
    | Public Designer Profile
    |--------------------------------------------------------------------------
    */

    public function show(User $designer)
    {

        if ($designer->role !== 'designer') {
            abort(404);
        }



        /*
        |--------------------------------------------------------------------------
        | Completed Projects
        |--------------------------------------------------------------------------
        */

        $completedProjects = Project::where('designer_id',$designer->id)
            ->where('status','completed')
            ->latest()
            ->take(6)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Reviews
        |--------------------------------------------------------------------------
        */

         $reviewsQuery = Review::where('designer_id',$designer->id);

        $reviews = $reviewsQuery
            ->with('reviewer')
            ->latest()
            ->take(10)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Portfolio
        |--------------------------------------------------------------------------
        */

        $portfolio = DesignerPortfolio::where('designer_id',$designer->id)
            ->with('images')
            ->latest()
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Average Rating
        |--------------------------------------------------------------------------
        */

        $averageRating = $reviewsQuery->avg('rating');

    $ratingStats = [
      5 => Review::where('designer_id',$designer->id)->where('rating',5)->count(),
      4 => Review::where('designer_id',$designer->id)->where('rating',4)->count(),
      3 => Review::where('designer_id',$designer->id)->where('rating',3)->count(),
      2 => Review::where('designer_id',$designer->id)->where('rating',2)->count(),
      1 => Review::where('designer_id',$designer->id)->where('rating',1)->count(),
          ];

$isFavorited = false;

if(Auth::check()){

    /** @var \App\Models\User $user */
    $user = Auth::user();

    $isFavorited = $user->favoriteDesigners()
        ->where('designer_id',$designer->id)
        ->exists();
}

  return view('public.designers.show', compact(
    'designer',
    'portfolio',
    'reviews',
    'averageRating',
    'ratingStats',
    'completedProjects',
    'isFavorited'
));
    }



    /*
    |--------------------------------------------------------------------------
    | Smart Hire Redirect
    |--------------------------------------------------------------------------
    */

    public function hire(User $designer)
    {

        if ($designer->role !== 'designer') {
            abort(404);
        }

        return redirect()->route(
            'client.projects.create',
            ['designer_id' => $designer->id]
        );
    }

}
