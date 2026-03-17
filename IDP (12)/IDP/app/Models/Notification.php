<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'link',
        'is_read'
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function markAsRead()
    {
        $this->update([
            'is_read' => true
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cache Auto Reset (PERFORMANCE FIX)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::created(function ($notification) {

            Cache::forget('user_unread_notifications_'.$notification->user_id);

        });

        static::updated(function ($notification) {

            Cache::forget('user_unread_notifications_'.$notification->user_id);

        });

        static::deleted(function ($notification) {

            Cache::forget('user_unread_notifications_'.$notification->user_id);

        });
    }

}
