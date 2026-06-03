<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditApiRequests
{
    public function __construct(protected AuditService $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $user = $request->user('sanctum');
            $this->audit->log(
                'api.'.$request->method().'.'.$request->path(),
                $user,
                null,
                $request,
                ['status' => $response->getStatusCode()]
            );
        }

        return $response;
    }
}
