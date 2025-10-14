<x-app-layout :pageTitle="'Record a Moment'">
 <div class="py-8 flex justify-center items-center">
  <div class="w-full max-w-6xl mx-auto md:px-8 px-5">
   <div class="flex justify-center items-center flex-col gap-4">
    <!-- Title -->
    <div class="flex flex-col justify-center items-center gap-1 mb-4">
     <h1 class="md:text-4xl text-2xl text-center font-bold text-black">
      Your moment is ready to record
     </h1>
    </div>

    <!-- Loading bar indicator :begin -->
    <div id="loading_bar" class="hidden w-full max-w-4xl h-1 bg-gray-200 rounded-full overflow-hidden">
     <div id="loading_progress" class="h-full bg-hot-shot"></div>
    </div>
    <!-- Loading bar indicator :end -->

    <!-- Record panel :begin -->
    <div class="rounded-xl p-3 bg-white w-full max-w-4xl h-auto inline-block shadow-sm border border-white-edgar">
     <div class="flex lg:flex-row flex-col justify-between items-start w-full p-2 gap-2">
      <!-- Live Preview :begin -->
      <div class="w-full space-y-2">
       <!-- Video -->
       <video id="preview_cam" autoplay playsinline muted class="rounded-2xl bg-after-midnight block w-full"></video>

       <!-- Control Panel :begin -->
       <div class="flex items-center justify-between bg-orochimaru/70 p-3 rounded-2xl w-full space-x-4">
        <!-- Cam Data Section :begin -->
        <div class="flex flex-col">
         <div class="flex items-center gap-2 text-md font-semibold text-after-midnight">
          <i data-lucide="cctv" class="w-5 h-5"></i>
          <span id="cam_name"></span> - <span id="cam_code"></span>
         </div>
        </div>
        <!-- Cam Data Section :end -->

        <!-- Button Section :begin -->
        <div class="flex justify-center items-center gap-2">
         <!-- Change Cam Button -->
         <button id="change_cam_btn"
          class="flex items-center justify-center rounded-full p-3 bg-info text-white-edgar hover:bg-info/70 transition-colors duration-100 tooltip tooltip-bottom"
          data-tip="Change camera preview">
          <i data-lucide="cctv" class="w-4 h-4"></i>
         </button>

         <!-- Full Screen Button -->
         <button id="full_screen_btn"
          class="flex items-center justify-center rounded-full p-3 bg-hot-shot text-white-edgar hover:bg-hot-shot/80 transition-colors duration-100 tooltip tooltip-bottom"
          data-tip="Full screen preview">
          <i data-lucide="maximize-2" class="w-4 h-4"></i>
         </button>

         <!-- Share Button -->
         <button id="shareButton"
          class="flex items-center justify-center rounded-full p-3 bg-secondary text-white-edgar hover:bg-secondary/80 transition-colors duration-100 tooltip tooltip-bottom"
          data-tip="Share">
          <i data-lucide="share-2" class="w-4 h-4"></i>
         </button>
        </div>
        <!-- Button Section :end -->
       </div>
       <!-- Control Panel :end -->
      </div>
      <!-- Live Preview :end -->

      <!-- Tool Panel :begin -->
      <div class="w-full space-y-2">
       <!-- Timer Panel :begin -->
       <div
        class="flex items-center justify-between bg-base-200/50 border border-white-edgar p-4 rounded-2xl w-full space-x-4">
        <!-- Timer Section :begin -->
        <div class="flex flex-col">
         <div class="flex items-center gap-2 text-sm text-carbon">
          <i data-lucide="timer" class="w-4 h-4"></i>
          <span>Timer</span>
         </div>
         <p id="timer"></p>
        </div>
        <!-- Timer Section :end -->

        <!-- Stop Button -->
        <button id="stop_record"
         class="flex items-center justify-center rounded-full p-3 bg-hot-shot text-white hover:bg-hot-shot/80 transition tooltip tooltip-bottom"
         data-tip="Stop recording">
         <i data-lucide="circle-stop" class="w-5 h-5"></i>
        </button>
       </div>
       <!-- Timer Panel :end -->

       <!-- Data Panel :begin -->
       <div class="flex items-center justify-between border border-base-300 p-5 rounded-2xl w-full">
        <!-- Field Section -->
        <div class="flex flex-col gap-4">
         <!-- Venue & Field -->
         <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2 text-sm text-carbon">
           <i data-lucide="land-plot" class="w-4 h-4"></i>
           <span id="venue_name" class="font-medium"></span>
          </div>
          <p id="field_name" class="text-lg font-semibold tracking-wide text-after-midnight"></p>
         </div>

         <!-- Video & Duration -->
         <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2 text-sm text-carbon">
           <i data-lucide="video" class="w-4 h-4"></i>
           <span id="video_name" class="font-medium"></span>
          </div>
          <p id="duration_video" class="text-lg font-semibold tracking-wide text-after-midnight"></p>
         </div>
        </div>
       </div>
       <!-- Data Panel :end -->
      </div>
      <!-- Tool Panel :end -->
     </div>
    </div>
    <!-- Record panel :end-->
   </div>
  </div>
 </div>

 @push('styles')
 <style>
  @keyframes loading {
   0% {
    width: 0%;
   }

   100% {
    width: 100%;
   }
  }

  .animate-loading {
   animation: loading 2s linear infinite;
  }
 </style>

 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('assets/js/creator/record/record2.js') }}"></script>
 @endpush
</x-app-layout>