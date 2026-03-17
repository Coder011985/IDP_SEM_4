<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'client_id',
        'designer_id'
    ];

    public function client()
    {
        return $this->belongsTo(User::class,'client_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class,'designer_id');
    }
}
