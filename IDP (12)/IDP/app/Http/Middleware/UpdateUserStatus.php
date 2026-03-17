<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Carbon;

class UpdateUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {

            /** @var User $user */
            $user = Auth::user();

            $now = Carbon::now();

            if (
                !$user->last_seen_at ||
                $user->last_seen_at->diffInSeconds($now) > 30
            ) {
                $user->update([
                    'last_seen_at' => $now
                ]);
            }
        }

        return $next($request);
    }
}
