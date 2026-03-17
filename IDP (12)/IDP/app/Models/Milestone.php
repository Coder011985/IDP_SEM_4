<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Models\Payment;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'amount',
        'percentage',
        'position',
        'status',
        'due_date',
        'approved_at',
        'funded_at',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'integer',
        'position' => 'integer',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'funded_at' => 'datetime',
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

    public function payment()
    {
        return $this->hasOne(Payment::class,'milestone_id');
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

    public function isFunded()
    {
        return $this->status === 'funded';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isReleased()
    {
        return $this->status === 'released';
    }

    public function isCompleted()
    {
        return $this->status === 'released';
    }

    public function isEscrowed()
    {
        return in_array($this->status,['funded','approved','released']);
    }



    /*
    |--------------------------------------------------------------------------
    | Payment Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaymentPending()
    {
        return $this->payment && $this->payment->status === 'pending';
    }

    public function isPaymentFunded()
    {
        return $this->payment && $this->payment->status === 'funded';
    }

    public function isPaymentReleased()
    {
        return $this->payment && $this->payment->status === 'released';
    }



    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

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
    | Status Actions
    |--------------------------------------------------------------------------
    */

    public function markFunded()
    {

        if($this->isFunded()){
            return;
        }

        $this->update([
            'status' => 'funded',
            'funded_at' => now()
        ]);

    }

    public function markApproved()
    {

        if($this->isApproved()){
            return;
        }

        $this->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

    }

    public function markReleased()
    {

        if($this->isReleased()){
            return;
        }

        $this->update([
            'status' => 'released',
            'released_at' => now()
        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Financial Helpers
    |--------------------------------------------------------------------------
    */

    public function escrowAmount()
    {
        return $this->amount;
    }



    /*
    |--------------------------------------------------------------------------
    | Auto Position Generator
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {

        static::creating(function ($milestone) {

            if (!$milestone->position) {

                $maxPosition = self::where('project_id', $milestone->project_id)
                    ->max('position');

                $milestone->position = $maxPosition ? $maxPosition + 1 : 1;

            }

        });

    }

}
