<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
    }
}
