<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class BlockSuspiciousIPsMiddleware
{
    protected int $maxAttempts = 5;

    protected int $decayMinutes = 1;

    protected int $blockMinutes = 5;

    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $key = $this->throttleKey($ip);

        if (Cache::has($key.':blocked')) {
            Session::flash('errors', "Your IP has been blocked for $this->blockMinutes minute(s) due to suspicious activity.");

            return redirect()->back();
        }
        if (Cache::has($key)) {
            $attempts = Cache::increment($key);
            if ($attempts > $this->maxAttempts) {
                Cache::put($key.':blocked', true, $this->blockMinutes * 60);
                Log::warning("IP $ip has been blocked for $this->blockMinutes minute(s) due to too many requests.");
                Session::flash('errors', "Your IP has been blocked for $this->blockMinutes minute(s) due to too many requests.");

                return redirect()->back();
            }
        } else {
            Cache::put($key, 1, $this->decayMinutes * 60);
        }

        return $next($request);
    }

    protected function throttleKey($ip): string
    {
        return 'throttle:'.sha1($ip);
    }
}
