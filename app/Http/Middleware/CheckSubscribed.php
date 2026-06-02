<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscribed
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->subscribed()) {
            return redirect()->route('dashboard')->with('error', 'You need an active subscription to access this page.');
        }
        
        return $next($request);
    }
}