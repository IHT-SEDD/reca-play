<x-app-layout :pageTitle="'New Recording'">
 <div class="py-8 flex justify-center items-center">
  <div class="w-full max-w-5xl mx-auto md:px-8 px-5">
   <div class="flex justify-center items-center flex-col gap-4">
    <!-- Title -->
    <div class="flex flex-col justify-center items-center gap-1 mb-4">
     <h1 class="md:text-4xl text-2xl text-center font-bold text-black">
      Lights, Camera, Action!
     </h1>
     <h3 class="md:text-2xl text-xl text-center font-bold text-hot-shot">
      Pick Your Mode
     </h3>
    </div>

    <!-- Record or streaming panel -->
    <div class="rounded-xl p-3 bg-white w-full max-w-lg h-auto inline-block shadow-sm border border-white-edgar">
     <!-- Record or streaming -->
     <div class="rounded-xl flex justify-between items-center w-full p-2 gap-2">
      <!-- Record button -->
      <button id="recordBtn"
       class="p-2.5 rounded-full bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition justify-center items-center gap-2 flex w-full disabled:cursor-not-allowed">
       <i data-lucide="video" class="w-4 h-4"></i>
       <p class="text-xs md:text-sm font-semibold">I'm Recording</p>
      </button>
      <!-- Streaming button -->
      <button id="streamingBtn"
       class="p-2.5 rounded-full bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition justify-center items-center gap-2 flex w-full disabled:cursor-not-allowed">
       <i data-lucide="airplay" class="w-4 h-4"></i>
       <p class="text-xs md:text-sm font-semibold">I'm Streaming</p>
      </button>
     </div>
    </div>

    <!-- Form panel -->
    <div class="rounded-xl p-4 bg-white w-full max-w-lg h-auto shadow-sm border border-white-edgar hidden"
     id="formPanel">
     <!-- Mode choosed text & information -->
     <div class="flex flex-col justify-center items-center gap-2 mb-2" id="choosedModePanel">
      <!-- Choosed mode text -->
      <div class="flex justify-center items-center gap-2">
       <h1 class="md:text-xl text-md text-center font-bold text-black">
        Great choice!
       </h1>
       <h3 class="md:text-lg text-sm text-center font-bold text-hot-shot" id="choosedMode"></h3>
      </div>

      <!-- Choosed mode description text -->
      <p class="text-center text-md text-after-midnight font-mono" id="descriptionChoosedMode"></p>
     </div>

     <!-- Form Section -->
     <div id="formStreaming" class="hidden p-4">
      @include('pages.creator.partials.form-streaming')
     </div>
     <div id="formRecording" class="hidden p-4">
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