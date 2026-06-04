<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reliable session start on Render (replaces broken StartSession in some environments).
 */
class EnsureSessionStarted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            $session = app('session');
            $session->start();
            $request->setLaravelSession($session->driver());
        }

        return $next($request);
    }
}
