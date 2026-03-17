<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDelivered implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $projectId;
    public $messageIds;

    public function __construct($projectId, $messageIds)
    {
        $this->projectId = $projectId;
        $this->messageIds = $messageIds;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('project.' . $this->projectId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.delivered';
    }

    public function broadcastWith(): array
    {
        return [
            'project_id' => $this->projectId,
            'message_ids' => $this->messageIds
        ];
    }
}
