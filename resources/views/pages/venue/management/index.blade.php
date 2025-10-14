<x-app-layout :pageTitle="'Venue Management'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header :begin -->
   <div class="mb-4 w-full flex lg:flex-row flex-col justify-between lg:items-center md:items-center items-start">
    <h1 class="lg:text-4xl md:text-3xl text-2xl font-bold text-hot-shot">Venue Management</h1>
    <h1 class="lg:text-3xl md:text-2xl text-xl font-bold text-after-midnight dark:text-white-owl" id="venue_name"></h1>
   </div>
   <!-- Text Header :end -->

   @role('owner')
   <!-- Text sub header :begin -->
   <div class="w-full flex items-center justify-start gap-1.5 lg:mb-4 md:mb-3 mb-2">
    <h3 class="lg:text-xl md:text-lg text-md tracking-wider font-bold text-after-midnight dark:text-white-owl">
     Statistics
    </h3>
    <i data-lucide="chart-no-axes-column-increasing" class="lg:w-7 md:w-6 w-5 h-auto text-hot-shot"></i>
   </div>
   <!-- Text sub header :end -->

   @include('pages.venue.management.partial.statistic')
   @endrole

   <!-- Text sub header :begin -->
   <h3
    class="lg:text-xl md:text-lg text-md tracking-wider font-bold text-after-midnight dark:text-white-owl lg:mb-4 md:mb-3 mb-2">
    Field List
   </h3>
   <!-- Text sub header :end -->

   <div class="w-full">
    @include('pages.venue.management.partial.field-list')
   </div>
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('assets/js/venue/management/index.js') }}"></script>
 @endpush
</x-app-layout>