<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Project;
use App\Models\User;

class Bid extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'project_id',
        'designer_id',
        'amount',
        'proposal',
        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'amount' => 'decimal:2',
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

    public function designer()
    {
        return $this->belongsTo(User::class,'designer_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status','pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status','accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status','rejected');
    }


    /*
    |--------------------------------------------------------------------------
    | Analytics Scopes
    |--------------------------------------------------------------------------
    */

    // Average bid amount for project
    public function scopeAverageBid($query,$projectId)
    {
        return $query->where('project_id',$projectId)
            ->avg('amount');
    }

    // Total bids for project
    public function scopeTotalBids($query,$projectId)
    {
        return $query->where('project_id',$projectId)
            ->count();
    }

    // Designer bid history
    public function scopeDesignerBids($query,$designerId)
    {
        return $query->where('designer_id',$designerId);
    }


    /*
    |--------------------------------------------------------------------------
    | Marketplace Intelligence
    |--------------------------------------------------------------------------
    */

    // Average marketplace bid
    public static function averageMarketplaceBid()
    {
        return self::avg('amount');
    }

    // Highest bid in marketplace
    public static function highestBid()
    {
        return self::max('amount');
    }

    // Lowest bid in marketplace
    public static function lowestBid()
    {
        return self::min('amount');
    }


    /*
    |--------------------------------------------------------------------------
    | Status Actions
    |--------------------------------------------------------------------------
    */

    public function accept()
    {
        $this->update([
            'status' => 'accepted'
        ]);
    }

    public function reject()
    {
        $this->update([
            'status' => 'rejected'
        ]);
    }

}
