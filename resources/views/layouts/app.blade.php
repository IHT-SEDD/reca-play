@props([
// Global variable
'pageTitle' => '',

// Default SEO
'metaDescription' => null,
'metaKeywords' => null,
'metaAuthor' => null,

// Default Open Graph
'ogTitle' => null,
'ogDescription' => null,
'ogImage' => null,

// Twitter
'twitterTitle' => null,
'twitterDescription' => null,
'twitterImage' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="bumblebee">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RECA') }} - {{ $pageTitle }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logos/reca-black.png') }}">

    <!-- Meta SEO -->
    @include('layouts.meta-seo')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-white-chalk">
        @include('layouts.navigation')

        <!-- Super admin menu -->
        @if (Auth::user() && Auth::user()->isSuperAdmin())
        @include('layouts.superadmin-navigation')
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <footer>
            @include('layouts.footer')
        </footer>
    </div>

    {{ $scripts ?? '' }}
</body>

</html>