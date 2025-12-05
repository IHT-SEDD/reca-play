<x-app-layout :pageTitle="'Venue Detail'">
  <div class="w-full mx-auto">
    <!-- Back to venue list button :begin -->
    <div class="w-full flex items-center justify-start gap-1.5 lg:mb-6 md:mb-5 mb-4 pt-4">
      <a href="{{ url('/venue') }}"
        class="flex items-center justify-center gap-2 text-after-midnight hover:text-hot-shot text-xs lg:text-sm font-medium">
        <i data-lucide="arrow-left" class="lg:w-5 md:w-4 w-3 h-auto"></i>
        Back to venue list
      </a>
    </div>
    <!-- Back to venue list button :end -->

    <!-- Header Section: Venue Information -->
    <div class="mb-4 flex flex-col lg:flex-row justify-between items-start gap-4 w-full">
      <!-- Left Section: Logo + Name + Address -->
      <div class="flex flex-col gap-2">
        <div class="flex flex-col lg:flex-row lg:items-start items-center lg:gap-6 gap-2">
          <!-- Venue Logo -->
          <div class="flex items-center justify-center rounded-xl w-fit lg:h-24 h-16 overflow-hidden">
            <img class="w-full h-full object-cover" id="venue_logo">
          </div>

          <!-- Venue Name & Type -->
          <div class="flex flex-col items-start justify-center gap-2">
            <h1 class="lg:text-2xl md:text-xl text-lg font-bold text-hot-shot leading-tight" id="venue_name"></h1>
            <h1 class="lg:text-2xl md:text-xl text-lg font-bold text-after-midnight" id="venue_type"></h1>
          </div>
        </div>

        <!-- Venue Address -->
        <h2 class="lg:text-lg md:text-base text-sm font-semibold text-carbon text-center lg:text-start" id="venue_address"></h2>
      </div>

      <!-- Right Section: Venue Type and Total Court -->
      <div class="flex flex-col items-center justify-center text-center gap-1">
        <!-- Total Court Count -->
        <div class="flex items-center justify-end gap-2">
          <h1 class="lg:text-lg md:text-md text-sm font-bold text-after-midnight">TOTAL FIELDS</h1>
          <span class="lg:text-lg md:text-md text-sm font-semibold text-hot-shot" id="total_court"></span>
        </div>
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