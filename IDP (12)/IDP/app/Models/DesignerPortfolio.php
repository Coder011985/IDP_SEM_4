<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\PortfolioImage;

class DesignerPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'designer_id',
        'title',
        'description',
        'category',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function images()
    {
        return $this->hasMany(PortfolioImage::class, 'portfolio_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function imageCount()
    {
        return $this->images()->count();
    }

}
