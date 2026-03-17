<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $projectId;
    public $userId;
    public $userName;

    public function __construct($projectId, $userId, $userName)
    {
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->userName = $userName;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('project.' . $this->projectId);
    }

    public function broadcastAs()
    {
        return 'chat.typing';
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName
        ];
    }
}
