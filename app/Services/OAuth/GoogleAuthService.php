<?php

namespace App\Services\OAuth;

use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    protected $authCheck;

    public function authCheck()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'role_id' => 3,
                    'name' => $googleUser->getName(),
                    'username' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'password'  => bcrypt(Str::random(16)),
                ]);

                $user->assignRole('public');
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Google authentication failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
