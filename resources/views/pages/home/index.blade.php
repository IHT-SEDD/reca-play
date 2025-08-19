<x-app-layout :pageTitle="'Home'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header :begin -->
   <div class="grid grid-cols-1 md:grid-cols-6 md:gap-4 mb-6">
    <!-- Text Header 1 -->
    <div
     class="mb-4 tracking-wide flex flex-col justify-between gap-2 md:col-start-1 md:col-end-3 text-center md:text-start">
     <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot">CAPTURE YOUR MOMENTS <br />
      <span class="text-black">ON AND OFF <br class="md:block hidden" /> THE FIELD</span>
     </h1>
    </div>
    <!-- Text Header 2 -->
    <div class="text-center md:text-end md:text-2xl text-md font-medium mb-4 tracking-wide md:col-end-7 md:col-span-2">
     <h2 class="text-after-midnight">
      Our platform is ready to level up your <br class="md:block hidden" />
      activities with modern vibes and features that <br class="md:block hidden" />
      fit your sporty lifestyle.
     </h2>
    </div>
   </div>
   <!-- Text Header :end -->

   <!-- Highlighted Videos :begin -->
   <div class="flex flex-col md:flex-row w-full justify-between items-center gap-4 mb-8">
    <!-- Title -->
    <h1 class="text-black md:text-2xl text-lg font-semibold tracking-wide">HIGHLIGHTED VIDEOS</h1>

    <!-- My Recordings Button (auth only) -->
    @auth
    <x-secondary-button class="w-fit md:w-full max-w-xs" btnId="myrecordings_btn">
     <i data-lucide="disc" class="w-4 h-4 md:me-2"></i>
     {{ __('See My Recordings') }}
    </x-secondary-button>
    @endauth
   </div>
   @include('pages.home.partials.highlight_videos')
   <!-- Highlighted Videos :end -->
  </div>
 </div>
</x-app-layout>