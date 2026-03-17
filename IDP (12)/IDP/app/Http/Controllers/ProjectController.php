<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function create()
    {
        return view('client.create_project');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric|min:1',
            'type' => 'required|in:normal,big',
        ]);

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'type' => $request->type,
            'client_id' => Auth::id(),
            'status' => 'pending',
        ]);

        // 🔥 LOG ACTIVITY
        logActivity(
            'created_project',
            'Created project: ' . $project->title,
            $project->id
        );

        return redirect()
            ->route('client.projects')
            ->with('success', 'Project Created Successfully');
    }

    // CLIENT - VIEW OWN PROJECTS
    public function clientProjects()
    {
        $projects = Project::where('client_id', Auth::id())
            ->latest()
            ->paginate(6);

        return view('client.projects.index', compact('projects'));
    }

    // DESIGNER - VIEW PENDING PROJECTS
    public function designerProjects()
    {
        $projects = Project::where('status', 'pending')
            ->latest()
            ->paginate(5);

        return view('designer.projects', compact('projects'));
    }

    // DESIGNER - VIEW ASSIGNED PROJECTS
    public function assignedProjects()
    {
        $designerId = Auth::id();

        $projects = Project::where('status', 'assigned')
            ->whereHas('bids', function ($query) use ($designerId) {
                $query->where('designer_id', $designerId)
                      ->where('status', 'approved');
            })
            ->latest()
            ->paginate(5);

        return view('designer.assigned_projects', compact('projects'));
    }

    // DESIGNER - MARK COMPLETE
    public function markComplete(Project $project)
    {
        $designerId = Auth::id();

        $approvedBid = $project->bids()
            ->where('designer_id', $designerId)
            ->where('status', 'approved')
            ->first();

        if (!$approvedBid) {
            abort(403);
        }

        if ($project->status !== 'assigned') {
            return back()->with('error', 'Project cannot be completed.');
        }

        $project->update([
            'status' => 'completed'
        ]);

        // 🔥 LOG ACTIVITY
        logActivity(
            'completed_project',
            'Marked project as completed: ' . $project->title,
            $project->id
        );

        return back()->with('success', 'Project marked as completed.');
    }
}
