<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Session\RecordSession;
use Illuminate\Http\JsonResponse;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'role_id' => 2,
            'email' => $request->email,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('member');

        event(new Registered($user));

        Auth::login($user);

        $sessionToken = session('qr_session_token');

        if ($sessionToken) {
            QrSession::where('session_token', $sessionToken)
                ->update([
                    'user_id' => Auth::user()->id,
                    'last_active_at' => now(),
                ]);

            SessionCode::where('session_token', $sessionToken)
                ->update(['user_id' => Auth::user()->id]);

            SessionLog::where('session_token', $sessionToken)
                ->update(['user_id' => Auth::user()->id,]);
        }

        $userId = Auth::id();
        $qrSession = QrSession::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest()
            ->first();

        $recordSession = RecordSession::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest()
            ->first();

        $codeSession = SessionCode::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest()
            ->first();

        $redirectUrl = route('home.index', absolute: false);
        if ($recordSession && $qrSession && $codeSession) {
            $redirectUrl = route('creator.redirect');
        } elseif ($qrSession && !$recordSession) {
            $redirectUrl = route('creator.qr-success');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Register successful!',
                'redirect' => $redirectUrl
            ]);
        }

        return redirect()->to($redirectUrl);
    }
}
