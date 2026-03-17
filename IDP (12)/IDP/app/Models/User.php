<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

use App\Models\Project;
use App\Models\Chat;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Bid;
use App\Models\DesignerPortfolio;

/**
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany favoriteDesigners()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'is_verified',
    'status',
    'last_seen_at',
    'specialization',
    'bio',
    'profile_photo',
];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_seen_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Online Status
    |--------------------------------------------------------------------------
    */

    public function isOnline()
    {
        return $this->last_seen_at &&
               $this->last_seen_at->gt(now()->subMinutes(2));
    }

    public function availabilityStatus()
{
    if(!$this->last_seen_at){
        return 'offline';
    }

    if($this->last_seen_at->gt(now()->subMinutes(2))){
        return 'online';
    }

    if($this->last_seen_at->gt(now()->subMinutes(10))){
        return 'recent';
    }

    return 'offline';
}

    public function lastSeenHuman()
    {
        if (!$this->last_seen_at) {
            return 'Never Online';
        }

        if ($this->isOnline()) {
            return 'Online';
        }

        return 'Last seen '.$this->last_seen_at->diffForHumans();
    }

    /*
    |--------------------------------------------------------------------------
    | Client Projects
    |--------------------------------------------------------------------------
    */

    public function clientProjects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Designer Projects
    |--------------------------------------------------------------------------
    */

    public function designerProjects()
    {
        return $this->hasMany(Project::class, 'designer_id');
    }

    public function completedProjects()
    {
        return $this->designerProjects()
            ->where('status','completed');
    }

    /*
    |--------------------------------------------------------------------------
    | Bids
    |--------------------------------------------------------------------------
    */

    public function bids()
    {
        return $this->hasMany(Bid::class,'designer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Reviews
    |--------------------------------------------------------------------------
    */

    public function receivedReviews()
    {
        return $this->hasMany(Review::class,'designer_id');
    }

    public function writtenReviews()
    {
        return $this->hasMany(Review::class,'reviewer_id');
    }

    public function averageRating()
    {
        return round($this->receivedReviews()->avg('rating') ?: 0,1);
    }

    public function reviewsCount()
    {
        return $this->receivedReviews()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Completed Projects Count
    |--------------------------------------------------------------------------
    */

    public function completedProjectsCount()
    {
        return $this->designerProjects()
            ->where('status','completed')
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Reputation Badge
    |--------------------------------------------------------------------------
    */

    public function reputationBadge()
    {
        if ($this->averageRating() >= 4.5) {
            return 'top_rated';
        }

        if ($this->completedProjectsCount() >= 5) {
            return 'verified_designer';
        }

        if ($this->reviewsCount() >= 3) {
            return 'rising_talent';
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Chat
    |--------------------------------------------------------------------------
    */

    public function sentMessages()
    {
        return $this->hasMany(Chat::class,'sender_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)
            ->where('is_read',false);
    }

    /*
    |--------------------------------------------------------------------------
    | Cached Notification Count
    |--------------------------------------------------------------------------
    */

    public function unreadNotificationsCount()
    {
        return Cache::remember(
            'user_unread_notifications_'.$this->id,
            60,
            function () {
                return Notification::where('user_id',$this->id)
                    ->where('is_read',false)
                    ->count();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Portfolio
    |--------------------------------------------------------------------------
    */

    public function portfolio()
    {
        return $this->hasOne(DesignerPortfolio::class,'designer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Designer Ranking Score
    |--------------------------------------------------------------------------
    */

    public function rankingScore()
    {
        $projects = $this->completedProjectsCount();
        $rating = $this->averageRating();
        $reviews = $this->reviewsCount();

        return ($projects * 5) + ($rating * 10) + ($reviews * 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Detect Inactive Designers
    |--------------------------------------------------------------------------
    */

    public function isInactiveDesigner()
    {
        if ($this->role !== 'designer') {
            return false;
        }

        return $this->designerProjects()
            ->where('created_at','>=',now()->subMonths(3))
            ->count() === 0;
    }

    /*
     |--------------------------------------------------------------------------
       | Scope: Designers Only
     |--------------------------------------------------------------------------
     */

         public function scopeDesigners($query)
      {
          return $query->where('role','designer');
      }

    /*
    |--------------------------------------------------------------------------
    | Top Designers
    |--------------------------------------------------------------------------
    */

    public static function topDesigners($limit = 5)
    {
        return Cache::remember("top_designers_$limit",300,function () use ($limit) {

            return self::designers()
                ->withCount([
                    'designerProjects as completed_projects_count' => function ($q) {
                        $q->where('status','completed');
                    },
                    'receivedReviews as reviews_count'
                ])
                ->withAvg('receivedReviews','rating')
                ->orderByDesc('received_reviews_avg_rating')
                ->orderByDesc('completed_projects_count')
                ->limit($limit)
                ->get();

        });
    }

    /*
|--------------------------------------------------------------------------
| Top Rated Designers
|--------------------------------------------------------------------------
*/

public static function topRated($limit = 3)
{
    return self::designers()
        ->whereHas('receivedReviews')
        ->withAvg('receivedReviews','rating')
        ->withCount([
            'receivedReviews',
            'designerProjects as completed_projects_count' => function ($q) {
                $q->where('status','completed');
            }
        ])
        ->having('received_reviews_avg_rating','>=',4.5)
        ->orderByDesc('received_reviews_avg_rating')
        ->limit($limit)
        ->get();
}


/*
|--------------------------------------------------------------------------
| Trending Designers
|--------------------------------------------------------------------------
*/

public static function trending($limit = 3)
{
    return self::designers()
        ->withAvg('receivedReviews','rating')
        ->withCount([
            'designerProjects as completed_projects_count' => function ($q) {
                $q->where('status','completed');
            },
            'receivedReviews as reviews_count'
        ])
        ->orderByDesc('completed_projects_count')
        ->orderByDesc('reviews_count')
        ->limit($limit)
        ->get();
}


/*
|--------------------------------------------------------------------------
| Rising Talent Designers
|--------------------------------------------------------------------------
*/

public static function risingTalent($limit = 3)
{
   return self::designers()
        ->withAvg('receivedReviews','rating')
        ->withCount([
            'designerProjects as completed_projects_count' => function ($q) {
                $q->where('status','completed');
            }
        ])
        ->having('received_reviews_avg_rating','>=',4)
        ->having('completed_projects_count','<=',3)
        ->orderByDesc('received_reviews_avg_rating')
        ->limit($limit)
        ->get();
}

/*
|--------------------------------------------------------------------------
| Recommended Designers
|--------------------------------------------------------------------------
*/

public static function recommended($limit = 6)
{
    return Cache::remember("recommended_designers_$limit",300,function() use ($limit){

        return self::designers()
            ->withAvg('receivedReviews','rating')
            ->withCount([
                'designerProjects as completed_projects_count' => function ($q) {
                    $q->where('status','completed');
                },
                'receivedReviews as reviews_count'
            ])
            ->limit(30)
            ->get()
            ->sortByDesc(function ($designer) {

                $rating = $designer->received_reviews_avg_rating ?? 0;
                $projects = $designer->completed_projects_count ?? 0;
                $reviews = $designer->reviews_count ?? 0;

                return ($rating * 10) + ($projects * 5) + ($reviews * 2);

            })
            ->take($limit)
            ->values();

    });
}

/*
|--------------------------------------------------------------------------
| Smart Designer Matching
|--------------------------------------------------------------------------
*/

public static function matchForProject($project, $limit = 6)
{
    return self::designers()
        ->withAvg('receivedReviews','rating')
        ->withCount([
            'designerProjects as completed_projects_count' => function ($q) {
                $q->where('status','completed');
            }
        ])
        ->limit(50)
        ->get()
        ->sortByDesc(function ($designer) use ($project) {

            $score = 0;

            // specialization match
            if ($project->style && $designer->specialization) {
                if (str_contains(
                    strtolower($designer->specialization),
                    strtolower($project->style)
                )) {
                    $score += 20;
                }
            }

            // rating weight
            $score += ($designer->received_reviews_avg_rating ?? 0) * 10;

            // experience weight
            $score += ($designer->completed_projects_count ?? 0) * 5;

            return $score;

        })
        ->take($limit)
        ->values();
}
    /*
    |--------------------------------------------------------------------------
     | Favorite Designers
     |--------------------------------------------------------------------------
    */

       public function favoriteDesigners()
      {
       return $this->belongsToMany(
        User::class,
        'favorites',
        'client_id',
        'designer_id'
       );
       }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isClient()
    {
        return $this->role === 'client';
    }

    public function isDesigner()
    {
        return $this->role === 'designer';
    }
}
