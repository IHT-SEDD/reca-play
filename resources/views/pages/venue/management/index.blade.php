<x-app-layout :pageTitle="'Venue Management'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header :begin -->
   <div class="mb-4 w-full flex lg:flex-row flex-col justify-between items-center">
    <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot">Venue Management</h1>
    <h1 class=" md:text-2xl text-xl font-bold text-after-midnight" id="venue_name"></h1>
   </div>
   <!-- Text Header :end -->

   <!-- Text sub header :begin -->
   <div class="w-full flex items-center justify-start gap-1.5 mb-4">
    <h3 class="md:text-2xl text-lg tracking-wider font-bold text-after-midnight">
     Statistics
    </h3>
    <i data-lucide="chart-no-axes-column-increasing" class="w-7 h-7 text-hot-shot"></i>
   </div>
   <!-- Text sub header :end -->

   @include('pages.venue.management.partial.statistic')

   <!-- Text sub header :begin -->
   <h3 class="md:text-2xl text-lg tracking-wider font-bold text-after-midnight mb-4">
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