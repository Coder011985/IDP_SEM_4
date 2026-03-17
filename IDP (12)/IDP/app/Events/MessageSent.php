<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | Message Object
    |--------------------------------------------------------------------------
    */

    public $chat;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(Chat $chat)
    {
        $this->chat = $chat->load('sender:id,name');
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcast Channel
    |--------------------------------------------------------------------------
    */

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('project.' . $this->chat->project_id),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Event Name
    |--------------------------------------------------------------------------
    */

    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcast Payload
    |--------------------------------------------------------------------------
    */

    public function broadcastWith(): array
    {
        return [

            'id' => $this->chat->id,

            'project_id' => $this->chat->project_id,

            'sender_id' => $this->chat->sender->id,

            'sender_name' => $this->chat->sender->name,

            'message' => $this->chat->message,

            'file' => $this->chat->file_path,

            'time' => $this->chat->created_at->format('H:i'),

            'delivered_at' => $this->chat->delivered_at,

            'seen_at' => $this->chat->seen_at,

            'created_at' => $this->chat->created_at->toDateTimeString(),

        ];
    }
}
