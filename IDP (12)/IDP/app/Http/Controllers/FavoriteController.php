<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Toggle Favorite Designer
    |--------------------------------------------------------------------------
    */

    public function toggle(User $designer)
    {
        $clientId = Auth::id();

        // Prevent saving yourself
        if ($designer->id === $clientId) {
            return back()->with('error','You cannot favorite yourself.');
        }

        // Check if favorite already exists
        $favorite = Favorite::where([
            'client_id' => $clientId,
            'designer_id' => $designer->id
        ])->first();

        // Remove from favorites
        if ($favorite) {
            $favorite->delete();
            return back()->with('success','Designer removed from favorites.');
        }

        // Save to favorites
        Favorite::create([
            'client_id' => $clientId,
            'designer_id' => $designer->id
        ]);

        return back()->with('success','Designer saved to favorites.');
    }


    /*
    |--------------------------------------------------------------------------
    | Saved Designers Page
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /** @var \App\Models\User $user */
          $user = Auth::user();

        $designers = $user->favoriteDesigners()->getQuery()
            ->withAvg('receivedReviews','rating')
            ->withCount([
                'designerProjects as completed_projects_count' => function ($q) {
                    $q->where('status','completed');
                }
            ])
            ->get();

        return view('client.favorites.index', compact('designers'));
    }

}
