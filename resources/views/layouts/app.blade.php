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
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ darkMode: localStorage.getItem('dark') === 'true' }" x-bind:class="{ 'dark': darkMode }"
    x-init="$watch('darkMode', val => localStorage.setItem('dark', val))" data-theme="bumblebee">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RECA') }} - {{ $pageTitle }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logos/reca-black.png') }}">

    <!-- Meta SEO -->
    @include('layouts.meta-seo')

    <!-- Scripts CSS :begin -->
    <link rel="stylesheet" href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/simplebar/simplebar.css') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <!-- Scripts CSS :end -->
</head>

<body class="body-layout">
    <div data-simplebar style="height: 100vh;">
        <div class="min-h-[130vh] flex flex-col main-bg-default">
            @include('layouts.navigation')

            <!-- Super admin menu -->
            @if (Auth::user() && Auth::user()->isSuperAdmin())
            @include('layouts.superadmin-navigation')
            @endif

            <!-- Owner menu -->
            @if (Auth::user() && Auth::user()->isVenueManagement())
            @include('layouts.venue-navigation')
            @endif

            <!-- Page Content -->
            <main class="p-6 mt-4 w-full mx-auto ">
                <x-indicators.loading></x-indicators.loading>
                {{ $slot }}
            </main>

            <footer class="mt-auto p-5">
                @include('layouts.footer')
            </footer>
        </div>
    </div>

    <!-- Scripts JS :begin -->
    <script src="{{ asset('vendors/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendors/dayjs/dayjs.min.js') }}"></script>
    <script src="{{ asset('vendors/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/loading.js') }}"></script>
    @stack('scripts')
    <!-- Scripts JS :end -->
</body>

</html>