<x-app-layout :pageTitle="'Watch'">
  <div class="py-8">
    <div class="w-full mx-auto md:px-8 px-5">
      <!-- Text Header :begin -->
      <div class="w-full max-w-5xl mx-auto flex flex-col gap-3">
        <!-- Video Player :begin -->
        <video controls playsinline class="rounded-xl w-full bg-after-midnight" id="video_player">
          <source type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <!-- Video Player :end -->

        <!-- Detail Video :begin -->
        <div class="w-full p-2">
          <h3 class="font-semibold lg:text-xl text-lg" id="video_name"></h3>
          <div class="flex lg:flex-row flex-col justify-between lg:items-center items-start">
            <div class="flex flex-col justify-center items-start gap-1 mb-2">
              <p class="text-md tracking-wide" id="owner_video"></p>
              <p class="text-md tracking-wide" id="date_created"></p>
            </div>
            <button
              class="share-btn flex items-center justify-center rounded-full h-8 w-8 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
              data-tip="share">
              <i data-lucide="forward" class="w-4 h-4"></i>
            </button>
          </div>
        </div>
        <!-- Detail Video :end -->
      </div>
      <!-- Text Header :end -->
    </div>
  </div>

  @include('pages.partials.share-modal')

  @push('styles')
  @endpush

  @push('scripts')
  <script src="{{ asset('assets/js/watch/index.js') }}"></script>
  @endpush
</x-app-layout>