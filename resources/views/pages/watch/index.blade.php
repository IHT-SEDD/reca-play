<x-app-layout :pageTitle="'Watch'">
  <div class="py-8">
    <div class="w-full mx-auto md:px-8 px-5">
      <!-- Text Header :begin -->
      <div class="w-full max-w-4xl mx-auto flex flex-col gap-3">
        <!-- Video Player :begin -->
        <video controls playsinline class="rounded-xl w-full bg-after-midnight" id="video_player">
          <source type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <!-- Video Player :end -->

        <!-- Detail Video :begin -->
        <div class="rounded-xl w-full p-2">
          <h3 class="font-semibold lg:text-xl text-lg" id="video_name"></h3>
          <div class="flex lg:flex-row flex-col justify-between lg:items-center items-start">
            <div class="flex flex-col justify-center items-start">
              <p class="text-md tracking-wide" id="owner_video"></p>
              <p class="text-md tracking-wide" id="date_created"></p>
            </div>
            <di class="flex justify-center items-end gap-2">
              <button
                class="share-btn flex items-center justify-center rounded-full h-8 w-8 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
                data-tip="share">
                <i data-lucide="forward" class="w-4 h-4"></i>
              </button>
          </div>
        </div>
      </div>
      <!-- Detail Video :end -->
    </div>
    <!-- Text Header :end -->
  </div>
  </div>

  @push('styles')
  @endpush

  @push('scripts')
  <script src="{{ asset('assets/js/watch/index.js') }}"></script>
  @endpush
</x-app-layout>