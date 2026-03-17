<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Models\User;
use App\Models\Milestone;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'client_id',
        'designer_id',
        'amount',
        'commission_amount',
        'designer_amount',
        'status',
        'paid_at',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'designer_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'released_at' => 'datetime',
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

    public function client()
    {
        return $this->belongsTo(User::class,'client_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class,'designer_id');
    }



    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeReleased($query)
    {
        return $query->where('status','released');
    }

    public function scopeFunded($query)
    {
        return $query->where('status','funded');
    }

    public function scopePending($query)
    {
        return $query->where('status','pending');
    }



    /*
    |--------------------------------------------------------------------------
    | Commission Calculator
    |--------------------------------------------------------------------------
    */

    public function calculateCommission()
    {

        $rate = config('app.commission_rate',10);

        $commission = round(($this->amount * $rate) / 100,2);

        $designerAmount = round($this->amount - $commission,2);

        return [
            'commission' => $commission,
            'designer_amount' => $designerAmount
        ];

    }



    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isReleased()
    {
        return $this->status === 'released';
    }

    public function isFunded()
    {
        return $this->status === 'funded';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }



    /*
    |--------------------------------------------------------------------------
    | Payment Actions
    |--------------------------------------------------------------------------
    */

    public function markFunded()
    {

        if($this->isFunded()){
            return;
        }

        $this->update([
            'status' => 'funded',
            'paid_at' => now()
        ]);

        if($this->milestone){
            $this->milestone->markFunded();
        }

    }



    public function releaseToDesigner()
    {

        if($this->isReleased()){
            return;
        }

        $commission = $this->calculateCommission();

        $this->update([
            'status' => 'released',
            'commission_amount' => $commission['commission'],
            'designer_amount' => $commission['designer_amount'],
            'released_at' => now()
        ]);

        if($this->milestone){
            $this->milestone->markReleased();
        }

    }



    /*
    |--------------------------------------------------------------------------
    | Refund Payment
    |--------------------------------------------------------------------------
    */

    public function refund()
    {

        if($this->status !== 'funded'){
            return;
        }

        $this->update([
            'status' => 'refunded'
        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Financial Helpers
    |--------------------------------------------------------------------------
    */

    public function platformRevenue()
    {
        return $this->commission_amount;
    }

    public function designerPayout()
    {
        return $this->designer_amount;
    }

}
