<x-app-layout :pageTitle="'Field Management'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
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

    <div class="flex items-center gap-3 justify-center">
     <!-- Generate code -->
     <div class="flex items-center gap-3 justify-center">
      <h1 class="lg:text-lg md:text-md text-md font-bold text-after-midnight">Generate code</h1>
      <button id="generate_code_button"
       class="p-2 rounded-xl inline-flex gap-2 items-center justify-center hover:bg-hot-shot hover:text-white bg-white border border-base-200 me-4">
       <i data-lucide="key-round" class="lg:w-4 md:w-3 w-2 h-auto"></i>
      </button>
     </div>
     <!-- Toggle active -->
     <div class="flex items-center gap-3 justify-center">
      <h1 class="lg:text-lg md:text-md text-md font-bold text-after-midnight" id="label_toggle"></h1>
      <button id="toggle_button"
       class="p-1 rounded-full inline-flex gap-2 items-center justify-center bg-white border border-base-200 me-4">
       <!-- Active -->
       <div id="toggle_active" class="lg:p-1 p-0.5 rounded-full transition">
        <i data-lucide="check" class="lg:w-4 md:w-3 w-2 h-auto"></i>
       </div>
       <!-- Non Active -->
       <div id="toggle_inactive" class="lg:p-1 p-0.5 rounded-full transition">
        <i data-lucide="x" class="lg:w-4 md:w-3 w-2 h-auto"></i>
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
    Last Activity
   </h3>
   <!-- Text sub header :end -->

   <div class="w-full">
    @include('pages.venue.management.partial.last-activity')
   </div>
  </div>
 </div>

 <dialog id="access_code_modal" class="modal modal-bottom sm:modal-middle">
  <div class="modal-box">
   <h3 class="text-lg font-bold">Here's your access code!</h3>
   <div class="py-4 flex items-center gap-3">
    <p id="access_code" class="text-xl font-semibold tracking-wide text-hot-shot"></p>
    <button id="copy_code_btn" class="btn btn-sm">Copy</button>
   </div>
   <p class="py-2 text-sm text-gray-500">Press ESC or click the button below to close</p>
   <div class="modal-action">
    <form method="dialog">
     <button class="btn">Close</button>
    </form>
   </div>
  </div>
 </dialog>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('assets/js/venue/management/detail.js') }}"></script>
 @endpush
</x-app-layout>