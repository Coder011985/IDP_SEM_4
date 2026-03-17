<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        /*
        |--------------------------------------------------------------------------
        | Global Notification Counter
        |--------------------------------------------------------------------------
        */

        View::composer('*', function ($view) {

            if (Auth::check()) {

                $unreadNotificationCount = Notification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                $view->with('globalUnreadNotifications', $unreadNotificationCount);

            }

        });
    }
}
