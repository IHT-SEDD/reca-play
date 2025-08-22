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

    <!-- Scripts CSS :begin -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <!-- Scripts CSS :end -->
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col bg-white-chalk">
        @include('layouts.navigation')

        <!-- Super admin menu -->
        @if (Auth::user() && Auth::user()->isSuperAdmin())
        @include('layouts.superadmin-navigation')
        @endif

        <!-- Page Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <footer class="mt-auto">
            @include('layouts.footer')
        </footer>
    </div>

    <!-- Scripts JS :begin -->
    <script src="{{ asset('vendors/jquery/jquery-3.7.1.min.js') }}"></script>
    @stack('scripts')
    <!-- Scripts JS :end -->
</body>

</html>