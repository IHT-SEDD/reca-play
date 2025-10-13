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
        if (!Auth::check() || $request->is('logout')) {
            return $next($request);
        }

        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        $qrSession = QrSession::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest()
            ->first();
        $recordSession = RecordSession::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest()
            ->first();
        $path = $request->path();

        $excludedRecordRoutes = ['creator/redirect*', 'creator/record*', 'logout', 'creator/scan-qr*', 'creator/new*'];
        $excludedQrRoutes = ['creator/new*', 'creator/scan-qr*', 'creator/redirect*'];

        if ($recordSession && $qrSession && !$this->match($path, $excludedRecordRoutes)) {
            return response()->view('pages.creator.redirect');
        }

        if ($qrSession && !$recordSession && !$this->match($path, $excludedQrRoutes)) {
            return redirect()->route('creator.qr-success');
        }

        return $next($request);
    }

    protected function match($path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
}
