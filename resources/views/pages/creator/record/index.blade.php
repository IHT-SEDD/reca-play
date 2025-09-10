<x-app-layout :pageTitle="'Record a Moment'">
 <div class="py-8 flex justify-center items-center">
  <div class="w-full max-w-5xl mx-auto md:px-8 px-5">
   <div class="flex justify-center items-center flex-col gap-4">
    <!-- Title -->
    <div class="flex flex-col justify-center items-center gap-1 mb-4">
     <h1 class="md:text-4xl text-2xl text-center font-bold text-black">
      Your moment is ready to record
     </h1>
    </div>

    <!-- Record panel -->
    <div class="rounded-xl p-3 bg-white w-full max-w-4xl h-auto inline-block shadow-sm border border-white-edgar">
     <div class="rounded-xl flex justify-between items-center w-full p-2 gap-2">
      <!-- Live Preview -->
      <div class="h-44 w-full bg-green-200"></div>
      <!-- Tool Panel -->
      <div class="h-44 w-full max-w-fit p-2 flex flex-col gap-2">
       <!-- Timer -->
       <div class="flex flex-col justify-center items-start">
        <div class="flex justify-center items-center gap-2 text-lg font-medium">
         <i data-lucide="clock" class="w-4 h-4"></i>
         <p>Times Left</p>
        </div>
        <p class="text-md" id="timer">00:00:00</p>
       </div>

       <!-- Field Data -->
       <div class="flex flex-col justify-center items-start">
        <div class="flex justify-center items-center gap-2 text-lg font-medium">
         <i data-lucide="land-plot" class="w-4 h-4"></i>
         <p id="venue_name">Sportive Hub</p>
        </div>
        <p class="text-md" id="field_name">Court Pink</p>
       </div>

       <!-- Record Data -->
       <div class="flex flex-col justify-start items-start">
        <div class="flex justify-center items-center gap-2 text-lg font-medium">
         <i data-lucide="land-plot" class="w-4 h-4"></i>
         <p id="video_name">Gibran Video</p>
        </div>
        <p class="text-md" id="duration">120 Min</p>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('assets/js/creator/scan-success.js') }}"></script>
 @endpush
</x-app-layout>