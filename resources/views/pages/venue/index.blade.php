<x-app-layout :pageTitle="'Venue'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header 1 -->
   <div class="mb-8 w-full flex justify-center items-center">
    <h1 class=" md:text-4xl text-2xl font-bold text-after-midnight">
     LIST ALL VENUES THAT HAS
     <span class="text-hot-shot">INTEGRATED</span>
     WITH US
    </h1>
   </div>

   <!-- Text Header 2 -->
   <div class="mb-10 w-full flex justify-center items-center">
    <h1 class=" md:text-2xl text-lg font-medium text-after-midnight">
     Hit our contact for integrating your venue with us!
    </h1>
   </div>

   <!-- Search bar -->
   <div class="mb-6 w-full flex justify-end items-center">
    <div class="w-full max-w-xs">
     <x-inputs.text-input id="search_venue" class="block mt-2 w-full" type="text" autofocus
      placeholder="search venues..." />
    </div>
   </div>

   <!-- Venue List -->
   <div class="mb-6 w-full flex justify-center items-center">
    @include('pages.venue.partials.venue_list')
   </div>
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('assets/js/venue/index.js') }}"></script>
 @endpush
</x-app-layout>