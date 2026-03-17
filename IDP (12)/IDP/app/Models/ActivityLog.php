<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Project;
use App\Events\ActivityCreated;


class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'action',
        'description',
        'ip_address',
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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }



    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeForProject($query,$projectId)
    {
        return $query->where('project_id',$projectId);
    }

    public function scopeByUser($query,$userId)
    {
        return $query->where('user_id',$userId);
    }

    public function scopeAction($query,$action)
    {
        return $query->where('action',$action);
    }



    /*
    |--------------------------------------------------------------------------
    | Record Activity
    |--------------------------------------------------------------------------
    */


   public static function record($userId,$projectId,$action,$description=null)
{
    $activity = self::create([
        'user_id' => $userId,
        'project_id' => $projectId,
        'action' => $action,
        'description' => $description,
        'ip_address' => request()->ip(),
    ]);

    broadcast(new ActivityCreated($activity))->toOthers();

    return $activity;
}



    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isSystem()
    {
        return $this->user_id === null;
    }



    /*
    |--------------------------------------------------------------------------
    | Activity Labels (for UI display)
    |--------------------------------------------------------------------------
    */

    public function getActionLabelAttribute()
    {

        return match($this->action) {

            'project_created' => 'Project Created',
            'project_approved' => 'Project Approved',
            'project_rejected' => 'Project Rejected',

            'milestone_funded' => 'Milestone Funded',
            'milestone_completed' => 'Milestone Completed',
            'milestone_released' => 'Milestone Payment Released',

            'bid_submitted' => 'Designer Submitted Bid',
            'bid_accepted' => 'Bid Accepted',

            'dispute_opened' => 'Dispute Opened',
            'dispute_resolved' => 'Dispute Resolved',

            default => ucfirst(str_replace('_',' ',$this->action))

        };

    }

}
