<x-app-layout :pageTitle="'Recording'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Header -->
   <div class="mb-6 w-full flex flex-col md:flex-row justify-between items-center">
    <!-- Title -->
    <h1 class=" md:text-4xl text-2xl md:mb-0 mb-4 font-bold text-after-midnight">
     MY RECORDINGS
    </h1>
    <!-- Search Bar -->
    <div class="w-full max-w-xs">
     <x-text-input id="search" class="block w-full" type="text" autofocus placeholder="search recordings..." />
    </div>
   </div>

   <!-- Toolbar -->
   <div class="mb-8 w-full flex flex-col md:flex-row justify-between items-center">
    <!-- Showing Data -->
    <h1 class=" md:text-lg text-sm md:mb-0 mb-4 font-medium text-after-midnight">
     Showing 10 to 10 of 10 videos
    </h1>
    <!-- Pagination -->
    <div class="flex items-center justify-end">
     <button>
      <i data-lucide="circle-chevron-left" class="w-5 h-5"></i>
     </button>
     <p class="text-sm mx-4 font-medium text-hot-shot">1</p>
     <p class="text-sm mx-4 font-medium text-magnesium">2</p>
     <p class="text-sm mx-4 font-medium text-magnesium">3</p>
     <p class="text-sm mx-4 font-medium text-magnesium">4</p>
     <button>
      <i data-lucide="circle-chevron-right" class="w-5 h-5"></i>
     </button>
    </div>
   </div>

   <!-- Recording List -->
   <div class="mb-6 w-full flex justify-center items-center">
    @include('pages.recording.partials.recording_list')
   </div>
  </div>
 </div>
</x-app-layout>