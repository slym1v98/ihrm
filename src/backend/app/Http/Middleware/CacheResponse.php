<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $key = 'response:'.md5($request->fullUrl());

        return Cache::remember($key, $ttl, fn () => $next($request));
    }
}
