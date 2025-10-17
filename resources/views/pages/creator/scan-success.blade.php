<x-app-layout :pageTitle="'New Recording'">
 <div class="py-8 flex justify-center items-center">
  <div class="w-full max-w-5xl mx-auto md:px-8 px-5">
   <div class="flex flex-col items-center gap-5">
    <!-- Title -->
    <div class="text-center space-y-2">
     <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-black leading-tight">
      Lights, Camera, Action!
     </h1>
     <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-hot-shot">
      Pick Your Mode
     </h3>
    </div>

    <!-- Record or streaming panel -->
    <div class="bg-white rounded-xl shadow-sm border border-white-edgar w-fit flex flex-col sm:flex-row p-4 sm:p-6">
     <!-- Record button -->
     <button id="recordBtn"
      class="flex items-center justify-center gap-2 w-full p-3 sm:p-4 rounded-xl bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition-all duration-200 disabled:cursor-not-allowed">
      <i data-lucide="video" class="w-5 h-5 sm:w-6 sm:h-6"></i>
      <p class="text-sm sm:text-base font-semibold">I'm Recording</p>
     </button>

     <!-- Streaming button -->
     <button id="streamingBtn"
      class="flex items-center justify-center gap-2 w-full p-3 sm:p-4 rounded-xl bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition-all duration-200 disabled:cursor-not-allowed">
      <i data-lucide="airplay" class="w-5 h-5 sm:w-6 sm:h-6"></i>
      <p class="text-sm sm:text-base font-semibold">I'm Streaming</p>
     </button>
    </div>

    <!-- Form panel -->
    <div id="formPanel"
     class="hidden bg-white rounded-2xl p-5 sm:p-6 w-full max-w-md sm:max-w-lg shadow-sm border border-white-edgar transition-all duration-300">
     <!-- Mode choosed text & information -->
     <div id="choosedModePanel" class="flex flex-col justify-center items-center gap-3 mb-4 text-center">
      <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-black">Great choice!</h1>
      <h3 id="choosedMode" class="text-sm sm:text-lg md:text-xl font-bold text-hot-shot"></h3>
      <p id="descriptionChoosedMode" class="text-sm sm:text-base md:text-md text-after-midnight font-mono"></p>
     </div>

     <!-- Form Section -->
     <div id="formStreaming" class="hidden p-4 sm:p-5">
      @include('pages.creator.partials.form-streaming')
     </div>
     <div id="formRecording" class="hidden p-4 sm:p-5">
      @include('pages.creator.partials.form-recording')
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