<x-app-layout :pageTitle="'Venue Detail'">
  <div class="w-full mx-auto">
    <!-- Back to venue list button :begin -->
    <div class="w-full flex items-center justify-start gap-1.5 lg:mb-4 md:mb-3 mb-2">
      <a href="{{ url('/venue') }}"
        class="flex items-center justify-center gap-2 text-after-midnight hover:text-hot-shot text-xs lg:text-sm font-medium">
        <i data-lucide="arrow-left" class="lg:w-5 md:w-4 w-3 h-auto"></i>
        Back to venue list
      </a>
    </div>
    <!-- Back to venue list button :end -->

    <!-- Text Header 1 -->
    <div class="mb-4 grid lg:grid-cols-2 grid-cols-1 gap-2 w-full">
      <h1 class="lg:text-4xl md:text-3xl text-2xl font-bold text-hot-shot text-start" id="venue_name"></h1>
      <h1 class="lg:text-2xl md:text-xl text-lg font-bold text-carbon lg:text-end text-start" id="venue_address"></h1>
    </div>

    <!-- Venue description -->
    <div class="mb-4 w-full flex flex-col justify-center items-start gap-1">
      <h1 class="lg:text-2xl md:text-xl text-lg font-bold text-after-midnight" id="venue_type"></h1>
      <div class="flex items-center justify-start gap-2 w-full">
        <h1 class="lg:text-lg md:text-md text-sm font-bold text-after-midnight">TOTAL COURT</h1>
        <div class="lg:text-lg md:text-md text-sm font-semibold text-hot-shot" id="total_court"></div>
      </div>
    </div>

    <!-- Search bar -->
    <div class="md:mb-6 mb-3 w-full flex justify-end items-center">
      <div class="w-full lg:max-w-xs md:max-w-xs">
        <x-inputs.text-input id="search_field" class="block mt-2 w-full" type="text" autofocus
          placeholder="search fields..." />
      </div>
    </div>

    <!-- Field List -->
    <div class="mb-6 w-full flex justify-center items-center">
      @include('pages.venue.partials.field_list')
    </div>
  </div>

  @push('styles')
  @endpush

  @push('scripts')
  <script src="{{ asset('assets/js/venue/detail.js') }}"></script>
  @endpush
</x-app-layout>