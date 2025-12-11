<x-app-layout :pageTitle="'Recording'">
 <div class="w-full mx-auto">
  <div class="mb-4 flex items-center gap-3 bg-hot-shot/20 rounded-lg p-2 w-fit hidden" id="download_video_alert">
   <i data-lucide="lightbulb" class="w-5 h-5 text-hot-shot"></i>
   <h1 class="lg:text-base text-sm font-bold text-after-midnight dark:text-white-chalk">
    Please immediately download your video, as it will be permanently
    deleted after 5
    days.
   </h1>
   <button id="close_download_video_alert"
    class="p-2 rounded-full bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition flex justify-center items-center gap-1">
    <i data-lucide="x" class="w-3 h-3"></i>
    <p class="text-xs font-semibold md:block hidden">Close</p>
   </button>
  </div>

  <!-- Header -->
  <div class="mb-6 w-full flex flex-col lg:flex-row justify-between items-center">
   <div class="flex justify-between items-center gap-3 lg:mb-0 mb-4">
    <!-- Title -->
    <h1 class="lg:text-4xl text-2xl font-bold text-after-midnight dark:text-white-chalk">
     MY RECORDINGS
    </h1>

    <!-- Add new recording -->
    {{-- <a href="{{ url('creator/scan-qr') }}"
     class="p-2.5 rounded-full bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition flex justify-center items-center gap-1">
     <i data-lucide="plus" class="w-4 h-4"></i>
     <p class="text-sm font-semibold md:block hidden">Start New Recording</p>
    </a> --}}

    <button
     class="get-videos p-2.5 rounded-full bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white transition flex justify-center items-center gap-1">
     <i data-lucide="video" class="w-4 h-4"></i>
     <p class="text-sm font-semibold md:block hidden">Get Videos</p>
    </button>
   </div>
   <!-- Search Bar -->
   <div class="w-full max-w-xs">
    <x-inputs.text-input id="search" class="block w-full" type="text" autofocus placeholder="search recordings..." />
   </div>
  </div>

  <!-- Toolbar Pagination -->
  <div class="mb-8 w-full flex flex-col md:flex-row justify-between items-center">
   <!-- Showing Data -->
   <h1 id="showing-info" class="md:text-lg text-sm md:mb-0 mb-4 font-medium text-after-midnight dark:text-white-edgar">
    Showing 0 to 0 of 0 videos
   </h1>

   <!-- Pagination -->
   <div class="flex items-center justify-end gap-2" id="pagination">
    <button id="prevPage" class="disabled:opacity-50 dark:text-white-edgar text-after-midnight">
     <i data-lucide="circle-chevron-left" class="w-5 h-5"></i>
    </button>
    <span id="pageNumbers" class="flex gap-2 text-sm font-medium"></span>
    <button id="nextPage" class="disabled:opacity-50 dark:text-white-edgar text-after-midnight">
     <i data-lucide="circle-chevron-right" class="w-5 h-5"></i>
    </button>
   </div>
  </div>

  <!-- Recording List -->
  <div class="mb-6 w-full flex justify-center items-center">
   <div id="recordingList"
    class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 transition-all duration-500 ease-in-out">
   </div>
  </div>
 </div>

 @include('pages.partials.share-modal')
 @include('pages.recording.partials.get-videos-modal')

 @push('styles')
 <link rel="stylesheet" href="{{ asset('assets/css/recording/index.css') }}">
 @endpush

 @push('scripts')
 <script src="{{ asset('assets/js/recording/index.js') }}"></script>
 @endpush
</x-app-layout>