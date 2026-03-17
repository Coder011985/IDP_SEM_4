<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use App\Models\User;
use App\Models\Bid;
use App\Models\Payment;
use App\Models\Milestone;
use App\Models\ActivityLog;
use App\Models\Chat;
use App\Models\Dispute;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'designer_id',
        'title',
        'description',
        'budget_min',
        'budget_max',
        'type',
        'location',
        'area_sqft',
        'deadline',
        'style',
        'property_type',
        'requirements',
        'status',
        'approval_status',
        'progress',
        'rating',
        'review',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'deadline'   => 'date',
        'area_sqft'  => 'integer',
        'progress'   => 'integer',
        'rating'     => 'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client()
    {
        return $this->belongsTo(User::class,'client_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class,'designer_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function acceptedBid()
    {
        return $this->hasOne(Bid::class)->where('status','accepted');
    }

    public function activities()
    {
        return $this->hasMany(ActivityLog::class)
            ->with('user')
            ->latest();
    }

    public function chats()
    {
        return $this->hasMany(Chat::class)->latest();
    }

    public function latestChat()
    {
        return $this->hasOne(Chat::class)->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class)
            ->orderBy('position');
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeClientProjects($query,$clientId)
    {
        return $query->where('client_id',$clientId);
    }

    public function scopeDesignerProjects($query,$designerId)
    {
        return $query->where('designer_id',$designerId);
    }

    public function scopePending($query)
    {
        return $query->where('status','pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status','completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status','approved');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status',['pending','in_progress']);
    }


    /*
    |--------------------------------------------------------------------------
    | Chat Helpers
    |--------------------------------------------------------------------------
    */

    public function unreadMessagesCount($userId)
    {
        return Cache::remember(
            "project_unread_{$this->id}_{$userId}",
            10,
            fn() => $this->chats()
                ->where('sender_id','!=',$userId)
                ->where('is_read',0)
                ->count()
        );
    }

    public function hasUnreadMessages($userId)
    {
        return $this->unreadMessagesCount($userId) > 0;
    }

    public function lastMessage()
    {
        return $this->latestChat;
    }

    public function chatPreview()
    {
        $last = $this->lastMessage();

        if(!$last){
            return null;
        }

        if($last->message){
            return Str::limit($last->message,60);
        }

        if($last->file_path){
            return '📎 File shared';
        }

        return null;
    }

    public function latestMessageTime()
    {
        return optional($this->latestChat)->created_at;
    }


    /*
    |--------------------------------------------------------------------------
    | Milestone Helpers
    |--------------------------------------------------------------------------
    */

    public function completedMilestones()
    {
        return $this->milestones()->where('status','released');
    }

    public function fundedMilestones()
    {
        return $this->milestones()->where('status','funded');
    }

    public function approvedMilestones()
    {
        return $this->milestones()->where('status','approved');
    }

    public function pendingMilestones()
    {
        return $this->milestones()->where('status','pending');
    }


    /*
    |--------------------------------------------------------------------------
    | Progress Calculator
    |--------------------------------------------------------------------------
    */

    public function calculateProgress()
    {
        return Cache::remember(
            "project_progress_{$this->id}",
            30,
            function(){

                $total = $this->milestones()->count();

                if($total === 0){
                    return 0;
                }

                $released = $this->completedMilestones()->count();

                return round(($released / $total) * 100);

            }
        );
    }

    public function milestoneCompletionPercentage()
    {
        return $this->calculateProgress();
    }


    /*
    |--------------------------------------------------------------------------
    | Escrow Helpers
    |--------------------------------------------------------------------------
    */

    public function escrowAmount()
    {
        return Cache::remember(
            "project_escrow_{$this->id}",
            30,
            fn() => $this->milestones()
                ->whereIn('status',['funded','approved'])
                ->sum('amount')
        );
    }

    public function releasedAmount()
    {
        return Cache::remember(
            "project_released_{$this->id}",
            30,
            fn() => $this->completedMilestones()
                ->sum('amount')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cache Clear Helper
    |--------------------------------------------------------------------------
    */

    public function clearCache()
    {
        Cache::forget("project_progress_{$this->id}");
        Cache::forget("project_escrow_{$this->id}");
        Cache::forget("project_released_{$this->id}");
    }


    /*
    |--------------------------------------------------------------------------
    | Activity Logger
    |--------------------------------------------------------------------------
    */

    public function logActivity($userId,$action,$description=null)
    {
        return ActivityLog::record(
            $userId,
            $this->id,
            $action,
            $description
        );
    }

}
