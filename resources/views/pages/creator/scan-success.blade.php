<x-app-layout :pageTitle="'New Recording'">
 <div class="flex flex-col gap-5 w-full max-w-lg mx-auto">
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
  <div class="bg-white rounded-xl shadow-sm border border-white-edgar w-full flex flex-col sm:flex-row p-4 gap-4">
   <!-- Record button -->
   <button id="recordBtn"
    class="flex-1 flex items-center justify-center gap-2 p-3 rounded-xl bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition-all duration-200 disabled:cursor-not-allowed">
    <i data-lucide="video" class="w-5 h-5"></i>
    <p class="text-sm font-semibold">I'm Recording</p>
   </button>

   <!-- Streaming button -->
   <button id="streamingBtn"
    class="flex-1 flex items-center justify-center gap-2 p-3 rounded-xl bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition-all duration-200 disabled:cursor-not-allowed">
    <i data-lucide="airplay" class="w-5 h-5"></i>
    <p class="text-sm font-semibold">I'm Streaming</p>
   </button>
  </div>

  <!-- Form panel -->
  <div id="formPanel" class="hidden bg-white rounded-2xl p-5 flex-1">
   <!-- Mode choosed text & information -->
   <div id="choosedModePanel" class="text-center space-y-2 mb-6">
    <h3 id="choosedMode" class="text-sm sm:text-lg md:text-xl font-bold text-hot-shot"></h3>
    <p id="descriptionChoosedMode" class="text-sm sm:text-base md:text-md text-after-midnight font-mono"></p>
   </div>

   <!-- Form Section -->
   @include('pages.creator.partials.form-streaming')
   @include('pages.creator.partials.form-recording')
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('assets/js/creator/scan-success.js') }}"></script>
 @endpush
</x-app-layout>