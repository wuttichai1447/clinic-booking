<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSRF check via session token only (no Encrypter — avoids APP_KEY cipher errors on Render).
 */
class VerifyFormCsrf
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            return $next($request);
        }

        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

        if (! is_string($token) || ! $request->hasSession() || ! hash_equals($request->session()->token(), $token)) {
            abort(419, 'Page expired');
        }

        return $next($request);
    }
}
