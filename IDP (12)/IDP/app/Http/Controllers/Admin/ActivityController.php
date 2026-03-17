<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = ActivityLog::with(['user', 'project'])
                        ->latest()
                        ->paginate(10);

        return view('admin.activities', compact('activities'));
    }
}
