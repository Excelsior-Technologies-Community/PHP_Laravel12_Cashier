<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next) {
    if ($request->user() && ! $request->user()->subscribed('default')) {
        return redirect()->route('dashboard')->with('message', 'Please subscribe to access this feature!');
    }
    return $next($request);
}
}
