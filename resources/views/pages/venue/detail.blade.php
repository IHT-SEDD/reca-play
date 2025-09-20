<x-app-layout :pageTitle="'Venue Detail'">
  <div class="py-8">
    <div class="w-full mx-auto md:px-8 px-5">
      <!-- Venue Logo -->
      <div class="mb-3 w-full bg-base-200 rounded-xl h-12 p-2">

      </div>

      <!-- Text Header 1 -->
      <div class="mb-2 w-full flex md:flex-row flex-col md:justify-between justify-center md:items-center items-start">
        <h1 class=" md:text-4xl text-2xl font-bold text-after-midnight" id="venue_name">VENUE NAME</h1>
        <h1 class=" md:text-2xl text-xl font-bold text-carbon" id="venue_address">VENUE ADDRESS</h1>
      </div>

      <!-- Venue description -->
      <div class="mb-4 w-full flex flex-col justify-center items-start gap-1">
        <h1 class=" md:text-2xl text-xl font-bold text-after-midnight" id="venue_type">VENUE TYPE</h1>
        <div class="flex items-center justify-start gap-4 w-full">
          <h1 class="md:text-lg text-md font-bold text-after-midnight">TOTAL COURT</h1>
          <div class="md:text-lg text-md font-semibold text-hot-shot" id="total_court"></div>
        </div>
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
        {{-- @include('pages.venue.partials.venue_list') --}}
      </div>
    </div>
  </div>

  @push('styles')
  @endpush

  @push('scripts')
  <script src="{{ asset('assets/js/venue/detail.js') }}"></script>
  @endpush
</x-app-layout>