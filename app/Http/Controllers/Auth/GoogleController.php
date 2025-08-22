<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\OAuth\GoogleAuthService;

class GoogleController extends Controller
{

    protected GoogleAuthService $googleService;

    public function __construct(GoogleAuthService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback()
    {
        try {
            $user = $this->googleService->authCheck();

            if (!$user) {
                return redirect('/login')->with('error', 'Failed to authenticate with Google.');
            }

            Auth::login($user);

            return redirect()->intended(route('home.index', absolute: false));
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Failed to authenticate with Google.');
        }
    }
}
