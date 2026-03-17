<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Chat;
use App\Models\User;

class ChatReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'reaction'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function toggle($chatId,$userId,$reaction)
    {
        $existing = self::where([
            'chat_id'=>$chatId,
            'user_id'=>$userId,
            'reaction'=>$reaction
        ])->first();

        if($existing){
            $existing->delete();
            return false;
        }

        return self::create([
            'chat_id'=>$chatId,
            'user_id'=>$userId,
            'reaction'=>$reaction
        ]);
    }
}
