<x-app-layout :pageTitle="'New Recording'">
 <div class="py-8 flex justify-center items-center">
  <div class="w-full max-w-5xl mx-auto md:px-8 px-5">
   <div class="flex justify-center items-center flex-col gap-8">
    <!-- Title -->
    <h1 class=" md:text-4xl text-2xl text-center font-bold text-black">
     START CAPTURE YOUR <span class="text-hot-shot">MOMENTS</span> WITH US
    </h1>

    <!-- QR Reader Panel -->
    <div class="rounded-xl p-3 bg-white w-full max-w-lg h-auto inline-block shadow-sm border border-white-edgar">
     <!-- Panel header -->
     <div class="rounded-xl mb-3 flex justify-between items-center w-full p-2 gap-2">
      <!-- Scan Again Button -->
      <button id="scanAgain" class="rounded-full p-2 text-black font-semibold">
       <i data-lucide="refresh-ccw" class="w-4 h-4 md:w-5 md:h-5"></i>
      </button>

      <!-- Title -->
      <p class="text-sm md:text-lg font-semibold">Scan QR Code</p>

      <!-- Status Indicators -->
      @include('pages.creator.qr.partials.status-indicators')
     </div>

     <!-- Sub Title -->
     <div class="flex flex-col justify-center items-center gap-1 mb-4">
      <div class="w-full max-w-sm text-center">
       <p class="text-sm md:text-md font-medium">Place the QR code properly inside the area</p>
       <p class="text-sm md:text-md font-medium">Scanning will start automatically</p>
      </div>
     </div>

     <!-- QR Reader -->
     <div class="flex justify-center items-center mb-4">
      <div class="w-full max-w-sm">
       <div id="qr-reader" class="w-full mx-auto"></div>
      </div>
     </div>

     <!-- QR Result -->
     <div class="flex justify-center items-center mb-4">
      <div class="w-full max-w-sm">
       <div id="qr-result" class="mt-4 text-center font-medium text-md w-full mx-auto mb-4"></div>

       <!-- Continue Button -->
       <a id="continueBtn" href="{{ url('creator/new') }}"
        class="p-2.5 rounded-full bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition justify-center items-center gap-1 hidden cursor-pointer">
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
        <p class="text-sm font-semibold md:block hidden">Continue</p>
       </a>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/html5-qrcode/html5-qrcode.min.js') }}"></script>
 <script src="{{ asset('assets/js/creator/scan-qr.js') }}"></script>
 @endpush
</x-app-layout>