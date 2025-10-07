<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $sessionToken = session('qr_session_token');

        if ($sessionToken) {
            QrSession::where('session_token', $sessionToken)
                ->update([
                    'user_id' => Auth::user()->id,
                    'last_active_at' => now(),
                ]);

            session()->forget('qr_session_token');
        }

        $userId = Auth::id();
        $qrSession = QrSession::where('user_id', $userId)->latest()->first();
        $recordSession = RecordSession::where('user_id', $userId)->latest()->first();

        if ($recordSession && $qrSession) {
            return redirect()->route('creator.redirect');
        }

        if ($qrSession && !$recordSession) {
            return redirect()->route('creator.qr-success');
        }

        return redirect()->intended(route('home.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
