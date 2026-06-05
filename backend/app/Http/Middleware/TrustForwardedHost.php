<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * When admin is accessed via Vercel/Nuxt proxy, generate URLs for the public host.
 */
class TrustForwardedHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->header('X-Forwarded-Host');

        if (is_string($host) && $host !== '') {
            $scheme = $request->header('X-Forwarded-Proto', 'https');
            if (! in_array($scheme, ['http', 'https'], true)) {
                $scheme = 'https';
            }

            $root = "{$scheme}://{$host}";
            URL::forceRootUrl($root);
            URL::forceScheme($scheme);
        }

        return $next($request);
    }
}
