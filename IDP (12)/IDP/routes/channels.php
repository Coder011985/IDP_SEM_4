<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Project;

/*
|--------------------------------------------------------------------------
| Private User Channel
|--------------------------------------------------------------------------
*/

Broadcast::channel('user.{id}', function ($user, $id) {

    if (!$user) {
        return false;
    }

    return (int) $user->id === (int) $id;

});


/*
|--------------------------------------------------------------------------
| Project Chat Presence Channel
|--------------------------------------------------------------------------
*/

Broadcast::channel('project.{projectId}', function ($user, $projectId) {

    if (!$user) {
        return false;
    }

    $project = Project::find($projectId);

    if (!$project) {
        return false;
    }

    // allow project client
    if ($user->id == $project->client_id) {
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }

    // allow project designer
    if ($project->designer_id && $user->id == $project->designer_id) {
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }

    return false;

});
