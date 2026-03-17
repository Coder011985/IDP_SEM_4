<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Dispute;
use App\Models\Project;
use App\Models\Notification;

class DisputeController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Store New Dispute
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Project $project)
    {

        $request->validate([
            'reason' => 'required|string|max:2000',
            'milestone_id' => 'nullable|exists:milestones,id'
        ]);

        $dispute = Dispute::create([

            'project_id' => $project->id,
            'milestone_id' => $request->milestone_id,
            'opened_by' => Auth::id(),
            'reason' => $request->reason,
            'status' => 'open'

        ]);

        /*
        |--------------------------------------------------------------------------
        | Notify Admin
        |--------------------------------------------------------------------------
        */

        $admins = \App\Models\User::where('role','admin')->get();

        foreach($admins as $admin){

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'dispute_opened',
                'message' => "Dispute opened for project: ".$project->title,
                'link' => route('admin.disputes'),
                'is_read' => false
            ]);

        }

        return back()->with('success','Dispute submitted to admin.');

    }

}
