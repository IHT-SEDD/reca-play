<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;
use Illuminate\Support\Facades\Auth;

class CheckActiveCreatorSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if user not logged in or logging out
        if (!Auth::check()) {
            return $next($request);
        }

        $userId = Auth::id();
        $path = $request->path();
        $sessionToken = session('qr_session_token');

        if (empty($sessionToken)) {
            $latestQrSession = QrSession::where('user_id', $userId)
                ->whereNotNull('session_token')
                ->latest('last_active_at')
                ->first();

            if ($latestQrSession) {
                $sessionToken = $latestQrSession->session_token;
                session(['qr_session_token' => $sessionToken]);
                if ($latestQrSession->qr_token) {
                    session(['qr_token' => $latestQrSession->qr_token]);
                }
            }
        }

        // Base query by user_id
        $qrSessionQuery = QrSession::where('user_id', $userId)->whereNotNull('session_token');
        $recordSessionQuery = RecordSession::where('user_id', $userId)->whereNotNull('session_token');

        // Add session_token filter if available
        if (!empty($sessionToken)) {
            $qrSessionQuery->where('session_token', $sessionToken);
            $recordSessionQuery->where('session_token', $sessionToken);
        }

        // Get latest sessions
        $qrSession = $qrSessionQuery
            ->latest('last_active_at')
            ->orderByDesc('id')
            ->first();

        $recordSession = $recordSessionQuery
            ->latest('created_at')
            ->orderByDesc('id')
            ->first();

        if ($qrSession) {
            $qrSession->touch('last_active_at');
        }

        // Excluded routes
        $excludedRecordRoutes = [
            'creator/redirect*',
            'creator/record*',
            'logout',
            'creator/scan-qr*',
            'creator/new*'
        ];

        $excludedQrRoutes = [
            'creator/new*',
            'creator/scan-qr*',
            'creator/redirect*'
        ];

        // If both QR and Record sessions exist → show redirect page
        if ($recordSession && $qrSession && !$this->match($path, $excludedRecordRoutes)) {
            return response()->view('pages.creator.redirect');
        }

        // If only QR session exists → go to QR success page
        if ($qrSession && !$recordSession && !$this->match($path, $excludedQrRoutes)) {
            return redirect()->route('creator.qr-success');
        }

        return $next($request);
    }

    /**
     * Check if the current path matches excluded routes
     */
    protected function match(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
}
