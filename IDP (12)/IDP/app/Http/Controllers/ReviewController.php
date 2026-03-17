<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Store Review
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Project $project)
    {

        // Only project client can review
    if ($project->client_id !== Auth::id()) {
        abort(403);
      }

    if ($project->status !== 'completed') {
      return back()->with('error','You can only review completed projects.');
       }

        // Validate input
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);


        // Prevent duplicate reviews
        $alreadyReviewed = Review::where('project_id', $project->id)
            ->where('reviewer_id', Auth::id())
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'You already reviewed this project.');
        }


        // Save review
        Review::create([
            'reviewer_id' => Auth::id(),
            'designer_id' => $project->designer_id,
            'project_id' => $project->id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);


        return back()->with('success', 'Review submitted successfully.');
    }
}
