@props([
// Global variable
'pageTitle' => '',
'title' => '',

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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendors/simplebar/simplebar.css') }}" />
    @stack('styles')
    <!-- Scripts CSS :end -->
</head>

<body class="font-sans text-gray-900 antialiased">
    <div data-simplebar style="height: 100vh;">
        <div
            class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white-chalk dark:bg-reversed-grey">
            <div class="flex flex-col items-center justify-center mb-6 gap-6 w-full">
                <a href="/">
                    <img src="{{ asset('assets/img/logos/reca-black.png') }}" alt="Logo RECA"
                        class="w-14 h-14 md:w-20 md:h-20">
                </a>
            </div>

            <div class="w-full sm:max-w-md p-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <h1 class="text-lg font-bold text-start mb-6">{{ $title }}</h1>
                {{ $slot }}
            </div>
        </div>
    </div>

    <!-- Scripts JS :begin -->
    <script src="{{ asset('vendors/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendors/zxcvbn/zxcvbn.js') }}"></script>
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
    @stack('scripts')
    <!-- Scripts JS :end -->
</body>

</html>