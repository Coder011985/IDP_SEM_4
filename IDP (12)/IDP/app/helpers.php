<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

if (!function_exists('logActivity')) {

    function logActivity($action, $description = null, $projectId = null)
    {
        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'description'=> $description,
            'project_id' => $projectId,
            'ip_address' => request()->ip(),
        ]);
    }
}
