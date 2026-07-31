<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->is_admin) {
            return redirect()->route('home')->with('message', 'Unauthorized');
        }

        return $next($request);
    }
}
