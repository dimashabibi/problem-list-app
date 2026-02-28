<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Microsoft\Provider as MicrosoftProvider;

class MicrosoftAuthController extends Controller
{
    public function redirect()
    {
        $cfg = config('services.microsoft');
        $cfg['tenant'] = env('MICROSOFT_TENANT_ID'); // paksa dari env

        return Socialite::buildProvider(MicrosoftProvider::class, $cfg)
            ->scopes(['openid', 'profile', 'email', 'offline_access', 'Mail.Send', 'Mail.Read'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        $cfg = config('services.microsoft');
        $cfg['tenant'] = env('MICROSOFT_TENANT_ID');
        
        try {
            $user = Socialite::buildProvider(MicrosoftProvider::class, $cfg)->user();
            $token = $user->token;
            $refreshToken = $user->refreshToken ?? null;
            $expiresIn = $user->expiresIn ?? 3600;
            Session::put('ms_access_token', $token);
            Session::put('ms_refresh_token', $refreshToken);
            Session::put('ms_token_expires_at', now()->addSeconds($expiresIn)->toDateTimeString());
            return redirect()->intended('/');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login failed: ' . $e->getMessage());
        }
    }
}
