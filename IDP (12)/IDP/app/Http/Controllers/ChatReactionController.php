<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Chat;
use App\Models\ChatReaction;

use App\Events\ChatReactionUpdated;

class ChatReactionController extends Controller
{

    public function toggle(Chat $chat, Request $request)
    {
        $request->validate([
            'reaction' => 'required|string|max:10'
        ]);

        ChatReaction::toggle(
            $chat->id,
            Auth::id(),
            $request->reaction
        );

        $summary = $chat->reactions()
            ->selectRaw('reaction, COUNT(*) as total')
            ->groupBy('reaction')
            ->pluck('total','reaction');

        broadcast(
            new ChatReactionUpdated($chat->id, $chat->project_id, $summary)
        )->toOthers();

        return response()->json([
            'status' => 'ok',
            'reactions' => $summary
        ]);
    }

}
