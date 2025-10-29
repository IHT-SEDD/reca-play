<x-app-layout :pageTitle="'Field Management'">
 <div class="w-full mx-auto">
  <!-- Back to venue management button :begin -->
  <div class="w-full flex items-center justify-start gap-1.5 lg:mb-4 md:mb-3 mb-2">
   <a href="{{ url('/venue-management') }}"
    class="flex items-center justify-center gap-2 text-after-midnight hover:text-hot-shot text-xs lg:text-sm font-medium">
    <i data-lucide="arrow-left" class="lg:w-5 md:w-4 w-3 h-auto"></i>
    Back to venue management
   </a>
  </div>
  <!-- Back to venue management button :end -->

  <!-- Text Header :begin -->
  <div class="mb-4 w-full flex lg:flex-row flex-col justify-between lg:items-center md:items-center items-start">
   <h1 class="lg:text-3xl md:text-2xl text-xl font-bold text-hot-shot" id="field_name"></h1>

   <div class="flex md:flex-row flex-col md:items-center gap-3 justify-center items-start">
    <!-- Toggle active -->
    <div class="flex items-center gap-3 justify-center">
     <h1 class="lg:text-lg md:text-md text-md font-bold text-after-midnight" id="label_toggle"></h1>
     <button id="toggle_button"
      class="p-1 rounded-full inline-flex gap-2 items-center justify-center bg-white border border-base-200 me-4">
      <!-- Active -->
      <div id="toggle_active" class="lg:p-1 p-0.5 rounded-full transition">
       <i data-lucide="check" class="lg:w-5 w-4 h-auto"></i>
      </div>
      <!-- Non Active -->
      <div id="toggle_inactive" class="lg:p-1 p-0.5 rounded-full transition">
       <i data-lucide="x" class="lg:w-5 w-4 h-auto"></i>
      </div>
     </button>
    </div>
   </div>
  </div>
  <!-- Text Header :end -->

  @role('owner')
  <!-- Text sub header :begin -->
  <div class="w-full flex items-center justify-start gap-1.5 lg:mb-4 md:mb-3 mb-2">
   <h3 class="lg:text-xl md:text-lg text-md tracking-wider font-bold text-after-midnight">
    Statistics
   </h3>
   <i data-lucide="chart-no-axes-column-increasing" class="lg:w-7 md:w-6 w-5 h-auto text-hot-shot"></i>
  </div>
  <!-- Text sub header :end -->
  @include('pages.venue.management.partial.field-statistic')
  @endrole

  <!-- Text sub header :begin -->
  <h3 class="lg:text-xl md:text-lg text-md tracking-wider font-bold text-after-midnight lg:mb-4 md:mb-3 mb-2">
   Access Code
  </h3>
  <!-- Text sub header :end -->

  <div class="w-full grid lg:grid-cols-5 md:grid-cols-4 grid-cols-1 mb-4 gap-2">
   @include('pages.venue.management.partial.access-code')
   @include('pages.venue.management.partial.access-code-form')
  </div>

  <!-- Text sub header :begin -->
  <h3 class="lg:text-xl md:text-lg text-md tracking-wider font-bold text-after-midnight lg:mb-4 md:mb-3 mb-2">
   Last Activity
  </h3>
  <!-- Text sub header :end -->

  <div class="w-full">
   @include('pages.venue.management.partial.last-activity')
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('vendors/form-request/formValidation.js') }}"></script>
 <script src="{{ asset('assets/js/venue/management/detail.js') }}"></script>
 @endpush
</x-app-layout>