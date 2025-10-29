@props([
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

  <title>{{ config('app.name', 'RECA') }} - Creator Redirect</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logos/reca-black.png') }}">

  <!-- Meta SEO -->
  @include('layouts.meta-seo')

  <!-- Scripts CSS :begin -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <!-- Scripts CSS :end -->
</head>

<body class="font-sans antialiased relative overflow-hidden bg-white-chalk">
  <!-- Background Overlay Lottie -->
  <div class="hidden md:block absolute bottom-0 left-0 z-0 pointer-events-none w-full h-auto">
    <dotlottie-wc src="{{ asset('assets/animations/pattern-animate.lottie') }}"
      style="width: 100%; height: auto; transform: translateX(-108px) translateY(9px); opacity: 0.5;" speed="1" autoplay
      loop>
    </dotlottie-wc>
  </div>

  <!-- Main Content -->
  <div class="min-h-screen relative z-10 flex flex-col">
    <!-- Page Content -->
    <main class="w-full h-screen flex items-center justify-center p-8">
      <div class="flex flex-col md:flex-row items-center justify-center gap-16 max-w-4xl w-full">

        <!-- Main animation -->
        <div class="flex items-center justify-center w-[200px] h-[200px] md:w-[300px] md:h-[300px]">
          <span class="loading loading-spinner text-secondary"
            style="width: 100%; height: 100%; max-width: 300px; max-height: 300px;"></span>
        </div>

        <!-- Text content -->
        <div class="flex flex-col justify-center items-center md:items-start gap-4">
          <h1 class="font-bold text-7xl md:text-9xl">Stay There!</h1>
          <h2 class="font-semibold text-xl md:text-3xl">You're being redirected to creator page</h2>
        </div>

      </div>
    </main>
  </div>

  <!-- Scripts JS :begin -->
  <script>
    setTimeout(() => window.location.href = "{{ url('/creator/record') }}", 1500);
  </script>
  <!-- Scripts JS :end -->
</body>

</html>