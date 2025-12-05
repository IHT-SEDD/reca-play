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
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{
    // ============================================================
    // Display the login view.
    // ============================================================
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        User::where('id', Auth::id())->update(['last_login_at' => now()]);

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
                'message' => 'Login successful!',
                'redirect' => $redirectUrl
            ]);
        }

        return redirect()->to($redirectUrl);
    }

    public function afterLoginSessionHandling(Request $request)
    {
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
                ->update(['user_id' => Auth::user()->id]);
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
                'message' => 'Login successful!',
                'redirect' => $redirectUrl
            ]);
        }

        return redirect()->to($redirectUrl);
    }


    // ============================================================
    // Destroy an authenticated session.
    // ============================================================
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
