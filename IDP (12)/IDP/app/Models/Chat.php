<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Project;
use App\Models\User;
use App\Models\ChatReaction;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'sender_id',
        'message',
        'file_path',
        'is_read',
        'delivered_at',
        'seen_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'delivered_at' => 'datetime',
        'seen_at' => 'datetime'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChatReaction::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Unread Counter Scope
    |--------------------------------------------------------------------------
    */

    public function scopeUnreadForUser($query, $userId)
    {
        return $query->where('sender_id','!=',$userId)
                     ->where('is_read',false);
    }


    /*
    |--------------------------------------------------------------------------
    | Message Status Helpers
    |--------------------------------------------------------------------------
    */

    public function markDelivered()
    {
        if (!$this->delivered_at) {

            $this->update([
                'delivered_at' => now()
            ]);

        }
    }

    public function markAsRead()
    {
        if (!$this->is_read) {

            $this->update([
                'is_read' => true,
                'seen_at' => now()
            ]);

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Status Check Helpers
    |--------------------------------------------------------------------------
    */

    public function isDelivered()
    {
        return !is_null($this->delivered_at);
    }

    public function isSeen()
    {
        return !is_null($this->seen_at);
    }


    /*
    |--------------------------------------------------------------------------
    | Reaction Helpers
    |--------------------------------------------------------------------------
    */

    public function reactionSummary()
    {
        return $this->reactions()
            ->selectRaw('reaction, COUNT(*) as total')
            ->groupBy('reaction')
            ->pluck('total','reaction');
    }

    public function userReaction($userId)
    {
        return $this->reactions()
            ->where('user_id',$userId)
            ->value('reaction');
    }


    /*
    |--------------------------------------------------------------------------
    | File Helpers
    |--------------------------------------------------------------------------
    */

    public function hasFile()
    {
        return !empty($this->file_path);
    }

    public function isImage()
    {
        if (!$this->file_path) {
            return false;
        }

        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));

        return in_array($extension, [
            'jpg','jpeg','png','gif','webp'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Message Preview
    |--------------------------------------------------------------------------
    */

    public function preview()
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        if (!empty($this->file_path)) {
            return '📎 File shared';
        }

        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | Unread Counter Helper
    |--------------------------------------------------------------------------
    */

    public static function unreadCountForUser($userId)
    {
        return self::where('sender_id','!=',$userId)
            ->where('is_read',false)
            ->count();
    }

}
