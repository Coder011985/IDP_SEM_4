<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatReaction;
use App\Events\MessageSeen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Mark Messages As Seen
    |--------------------------------------------------------------------------
    */
    public function seen($projectId)
    {
        $messages = Chat::where('project_id', $projectId)
            ->whereNull('seen_at')
            ->where('sender_id','!=',Auth::id())
            ->get();

        if($messages->isEmpty()){
            return response()->json(['status'=>'ok']);
        }

        $ids = $messages->pluck('id');

        Chat::whereIn('id',$ids)->update([
            'seen_at'=>now()
        ]);

        broadcast(new MessageSeen($projectId,$ids))->toOthers();

        return response()->json([
            'status'=>'ok'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | React To Message
    |--------------------------------------------------------------------------
    */
    public function react(Request $request, Chat $chat)
    {
        $request->validate([
            'reaction' => 'required|string|max:10'
        ]);

        $reaction = ChatReaction::updateOrCreate(
            [
                'chat_id' => $chat->id,
                'user_id' => Auth::id()
            ],
            [
                'reaction' => $request->reaction
            ]
        );

        return response()->json([
            'status' => 'success',
            'reaction' => $reaction->reaction
        ]);
    }

}
