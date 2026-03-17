<?php

namespace App\Events;

use App\Models\ActivityLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $activity;

    public function __construct(ActivityLog $activity)
    {
        $this->activity = $activity->load('user','project');
    }

    public function broadcastOn()
    {
        return new Channel('activity');
    }

    public function broadcastAs()
    {
        return 'activity.created';
    }
}
