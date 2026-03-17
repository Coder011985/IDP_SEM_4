<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $chatId;
    public $projectId;
    public $reactions;

    public function __construct($chatId, $projectId, $reactions)
    {
        $this->chatId = $chatId;
        $this->projectId = $projectId;
        $this->reactions = $reactions;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('project.' . $this->projectId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.reaction.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatId,
            'reactions' => $this->reactions
        ];
    }
}
