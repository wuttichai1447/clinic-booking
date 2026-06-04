<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full session lifecycle on Render (StartSession was removed; must save + set cookie).
 */
class EnsureSessionStarted
{
    public function __construct(
        protected SessionManager $manager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->sessionConfigured()) {
            return $next($request);
        }

        $session = $this->getSession($request);

        $request->setLaravelSession($this->startSession($request, $session));

        $response = $next($request);

        $this->addCookieToResponse($response, $session);

        if (! $request->isPrecognitive()) {
            $session->save();
        }

        return $response;
    }

    protected function getSession(Request $request): Session
    {
        return tap($this->manager->driver(), function (Session $session) use ($request) {
            $session->setId($request->cookies->get($session->getName()) ?? '');
        });
    }

    protected function startSession(Request $request, Session $session): Session
    {
        return tap($session, function (Session $session) use ($request) {
            $session->setRequestOnHandler($request);
            $session->start();
        });
    }

    protected function addCookieToResponse(Response $response, Session $session): void
    {
        $config = $this->manager->getSessionConfig();

        if (is_null($config['driver'] ?? null)) {
            return;
        }

        $response->headers->setCookie(new Cookie(
            $session->getName(),
            $session->getId(),
            $config['expire_on_close'] ? 0 : Date::instance(
                Carbon::now()->addRealMinutes($config['lifetime'])
            ),
            $config['path'],
            $config['domain'],
            $config['secure'],
            $config['http_only'] ?? true,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        ));
    }

    protected function sessionConfigured(): bool
    {
        return ! is_null($this->manager->getSessionConfig()['driver'] ?? null);
    }
}
