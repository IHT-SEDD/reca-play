<x-app-layout :pageTitle="'Home'">
 <div class="w-full mx-auto">
  <!-- Text Header :begin -->
  <div class="grid grid-cols-1 md:grid-cols-6 md:gap-4 mb-6">
   <!-- Text Header 1 -->
   <div
    class="mb-4 tracking-wide flex flex-col justify-between gap-2 md:col-start-1 md:col-end-3 text-center md:text-start">
    <h1 class="md:text-4xl text-2xl text-highlighted-default">CAPTURE YOUR MOMENTS <br />
     <span class="text-black dark:text-white">ON AND OFF <br class="md:block hidden" /> THE FIELD</span>
    </h1>
   </div>
   <!-- Text Header 2 -->
   <div class="text-center md:text-end md:text-2xl text-md font-medium mb-4 tracking-wide md:col-end-7 md:col-span-2">
    <h2 class="text-color-default">
     Our platform is ready to level up your <br class="md:block hidden" />
     activities with modern vibes and features that <br class="md:block hidden" />
     fit your sporty lifestyle.
    </h2>
   </div>
  </div>
  <!-- Text Header :end -->

  <!-- Latest Videos :begin -->
  <div class="flex flex-col md:flex-row w-full justify-between items-center gap-4 mb-8">
   <!-- Title -->
   <h1 class="text-title-default">LATEST VIDEOS</h1>

   <!-- My Recordings Button (auth only) -->
   @auth
   <x-buttons.cta-button url="{{ url('/my-recording') }}">
    <i data-lucide="disc" class="w-4 h-4 me-2"></i>
    See My Recording
   </x-buttons.cta-button>
   @endauth
  </div>

  <!-- Latest videos list -->
  <div
   class="w-full min-h-44 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 transition-all duration-500 ease-in-out"
   id="latestVideoList">
  </div>
  <!-- Latest Videos :end -->
 </div>

 @include('pages.partials.share-modal')

 @push('styles')
 <link rel="stylesheet" href="{{ asset('assets/css/home/index.css') }}">
 @endpush

 @push('scripts')
 <script src="{{ asset('assets/js/home/index.js') }}"></script>
 @endpush
</x-app-layout>