<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $underMaintenance = config('services.under_maintenance.status', false);

        if ($underMaintenance) {

            $superAdmin = Auth::check() && Auth::user()->role->name === 'superadmin';

            if ($superAdmin) {
                return $next($request);
            }

            // Normalisasi path
            $path = '/' . ltrim($request->path(), '/');
            $allowedUrls = ['/login'];

            if (!in_array($path, $allowedUrls)) {
                return response()->view('maintenance', [], 503);
            }
        }

        return $next($request);
    }
}
