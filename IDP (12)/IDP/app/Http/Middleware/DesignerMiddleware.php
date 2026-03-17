<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DesignerMiddleware
{
    /**
     * Handle an incoming request.
     */

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDesigner()) {
            return $next($request);
        }

        abort(403, 'Unauthorized Access');
    }
}
