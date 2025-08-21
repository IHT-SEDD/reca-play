<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
                    'name'      => $googleUser->getName(),
                    'username'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    // 'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'password'  => bcrypt(Str::random(16)),
                ]);
            }

        return $user;

        }catch (\Exception $e) {
            return response()->json(['error' => 'Failed to authenticate with Google, reason: ' . $e->getMessage(), 500]);
        }
    }
}
