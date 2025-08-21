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

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white-chalk">
        <div class="flex flex-col items-center justify-center mb-8 gap-6 w-full">
            <a href="/">
                <img src="{{ asset('assets/img/logos/reca-black.png') }}" alt="Logo RECA"
                    class="w-14 h-14 md:w-20 md:h-20">
            </a>

            <h1 class="md:text-3xl text-xl font-bold">{{ $title }}</h1>
        </div>

        <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
      <script src="{{ asset('vendors/zxcvbn/zxcvbn.js') }}"></script>
      <script src="{{ asset('assets/register/index.js') }}"></script>
</body>
</html>
