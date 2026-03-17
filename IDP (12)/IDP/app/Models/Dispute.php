<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{

    protected $fillable = [

        'project_id',
        'milestone_id',
        'opened_by',
        'reason',
        'status',
        'resolution',
        'resolved_by',
        'resolved_at'

    ];

    protected $casts = [

        'resolved_at' => 'datetime'

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

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class,'opened_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class,'resolved_by');
    }

}
