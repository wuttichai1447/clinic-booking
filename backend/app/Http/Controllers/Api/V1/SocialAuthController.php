<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectGoogle(): RedirectResponse
    {
        if (! $this->oauthConfigured('google')) {
            return $this->redirectFrontend(['error' => 'oauth_not_configured', 'provider' => 'google']);
        }

        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callbackGoogle(): RedirectResponse
    {
        return $this->handleCallback('google');
    }

    public function redirectFacebook(): RedirectResponse
    {
        if (! $this->oauthConfigured('facebook')) {
            return $this->redirectFrontend(['error' => 'oauth_not_configured', 'provider' => 'facebook']);
        }

        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function callbackFacebook(): RedirectResponse
    {
        return $this->handleCallback('facebook');
    }

    private function handleCallback(string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            return $this->redirectFrontend(['error' => 'oauth_failed']);
        }

        $user = User::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (! $user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        if ($user) {
            if ($user->role !== 'customer') {
                return $this->redirectFrontend(['error' => 'not_customer']);
            }
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'name' => $user->name ?: $socialUser->getName(),
            ]);
        } else {
            $user = User::create([
                'name' => $socialUser->getName() ?: 'ลูกค้า',
                'email' => $socialUser->getEmail() ?: $provider.'_'.$socialUser->getId().'@oauth.local',
                'phone' => null,
                'password' => Hash::make(Str::random(32)),
                'role' => 'customer',
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        $user->tokens()->where('name', 'customer')->delete();
        $token = $user->createToken('customer')->plainTextToken;

        return $this->redirectFrontend([
            'token' => $token,
            'provider' => $provider,
        ]);
    }

    private function oauthConfigured(string $provider): bool
    {
        $id = config("services.{$provider}.client_id");
        $secret = config("services.{$provider}.client_secret");

        return filled($id) && filled($secret);
    }

    private function redirectFrontend(array $params): RedirectResponse
    {
        $base = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');
        $query = http_build_query($params);

        return redirect("{$base}/auth/callback?{$query}");
    }
}
