<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Project;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'designer_id',
        'project_id',
        'rating',
        'review'
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'rating' => 'integer'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Client who wrote the review
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Designer receiving the review
    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    // Related project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    // High rating reviews
    public function scopePositive($query)
    {
        return $query->where('rating', '>=', 4);
    }

    // Low rating reviews
    public function scopeNegative($query)
    {
        return $query->where('rating', '<=', 2);
    }

    // Reviews for specific designer
    public function scopeForDesigner($query, $designerId)
    {
        return $query->where('designer_id', $designerId);
    }


    /*
    |--------------------------------------------------------------------------
    | Analytics Helpers
    |--------------------------------------------------------------------------
    */

    // Average rating of designer
    public static function averageRatingForDesigner($designerId)
    {
        return self::where('designer_id', $designerId)
            ->avg('rating');
    }

    // Total reviews count
    public static function reviewsCountForDesigner($designerId)
    {
        return self::where('designer_id', $designerId)
            ->count();
    }

}
