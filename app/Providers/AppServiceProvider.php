<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ========== SEO GENERATOR ==========
        $defaultSEO = [
            // SEO
            'metaDescription' => 'Capture your moments on and off the field with RECA – your sporty lifestyle companion.',
            'metaKeywords' => 'sports, capture moments, field, lifestyle, reca, sport virtual assistance, sva',
            'metaAuthor' => config('app.name', 'RECA PLAY'),

            // Open Graph / Facebook
            'ogTitle' => config('app.name', 'RECA PLAY'),
            'ogDescription' => 'Capture your moments on and off the field with RECA – your sporty lifestyle companion.',
            'ogImage' => asset('assets/img/logos/reca-black.png'),

            // Twitter Card
            'twitterTitle' => config('app.name', 'RECA PLAY'),
            'twitterDescription' => 'Capture your moments on and off the field with RECA – your sporty lifestyle companion.',
            'twitterImage' => asset('assets/img/logos/reca-black.png'),
        ];

        View::composer(
            ['layouts.app', 'layouts.guest'],
            function ($view) use ($defaultSEO) {
                $view->with($defaultSEO);
            }
        );

        // ========== RATE LIMITER REQUEST ==========
        // ---------- scan qr process ----------
        RateLimiter::for('scan-qr', function ($request) {
            return Limit::perMinute(config('ratelimiter.scan-qr'))
                ->by($this->userOrIp($request))
                ->response(function () use ($request) {
                    $message = 'Too many QR scan attempts. Please try again in 1 minute.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                        ], 429);
                    }

                    return response()
                        ->view('errors.429', [
                            'title' => 'Rate Limit Exceeded',
                            'message' => $message
                        ], 429);
                });
        });

        // ---------- add data creator ----------
        RateLimiter::for('add-data-creator', function ($request) {
            return Limit::perMinute(config('ratelimiter.add-data-creator'))
                ->by($this->userOrIp($request))
                ->response(function () use ($request) {
                    $message = 'Too many add data requests. Please try again in 1 minute.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                        ], 429);
                    }

                    return response()
                        ->view('errors.429', [
                            'title' => 'Rate Limit Exceeded',
                            'message' => $message
                        ], 429);
                });
        });

        // ---------- stop recording ----------
        RateLimiter::for('stop-record', function ($request) {
            return Limit::perMinute(config('ratelimiter.stop-record'))
                ->by($this->userOrIp($request))
                ->response(function () use ($request) {
                    $message = 'Too many stop recording requests. Please try again in 1 minute.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                        ], 429);
                    }

                    return response()
                        ->view('errors.429', [
                            'title' => 'Rate Limit Exceeded',
                            'message' => $message
                        ], 429);
                });
        });

        // ---------- stop streaming ----------
        RateLimiter::for('stop-stream', function ($request) {
            return Limit::perMinute(config('ratelimiter.stop-stream'))
                ->by($this->userOrIp($request))
                ->response(function () use ($request) {
                    $message = 'Too many stop streaming requests. Please try again in 1 minute.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                        ], 429);
                    }

                    return response()
                        ->view('errors.429', [
                            'title' => 'Rate Limit Exceeded',
                            'message' => $message
                        ], 429);
                });
        });

        // ---------- stop selfie ----------
        RateLimiter::for('stop-selfie', function ($request) {
            return Limit::perMinute(config('ratelimiter.stop-selfie'))
                ->by($this->userOrIp($request))
                ->response(function () use ($request) {
                    $message = 'Too many stop selfie requests. Please try again in 1 minute.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                        ], 429);
                    }

                    return response()
                        ->view('errors.429', [
                            'title' => 'Rate Limit Exceeded',
                            'message' => $message
                        ], 429);
                });
        });

        // ---------- share video ----------
        RateLimiter::for('share-video', function ($request) {
            return Limit::perMinute(config('ratelimiter.share-video'))
                ->by($this->userOrIp($request))
                ->response(function () use ($request) {
                    $message = 'Too many share video requests. Please try again in 1 minute.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                        ], 429);
                    }

                    return response()
                        ->view('errors.429', [
                            'title' => 'Rate Limit Exceeded',
                            'message' => $message
                        ], 429);
                });
        });

        // ---------- download video ----------
        RateLimiter::for('download-video', function ($request) {
            return Limit::perMinute(config('ratelimiter.download-video'))
                ->by($this->userOrIp($request))
                ->response(function () use ($request) {
                    $message = 'Too many download video requests. Please try again in 1 minute.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                        ], 429);
                    }

                    return response()
                        ->view('errors.429', [
                            'title' => 'Rate Limit Exceeded',
                            'message' => $message
                        ], 429);
                });
        });
    }

    // ========== HELPER ==========
    private function userOrIp($request): string
    {
        return $request->user()?->id ?: $request->ip();
    }
}
