<x-app-layout :pageTitle="'Watch'">
  <div class="w-full mx-auto">
    <!-- Text Header :begin -->
    <div class="w-full max-w-6xl mx-auto flex flex-col gap-3">
      <!-- Video Player :begin -->
      <video controls playsinline class="rounded-xl w-full bg-after-midnight hidden" id="video_player">
        <source type="video/mp4">
        Your browser does not support the video tag.
      </video>
      <div class="w-full h-52 bg-magnesium rounded-lg animate-pulse my-1" id="video_player_placeholder"></div>
      <!-- Video Player :end -->

      <!-- Detail Video :begin -->
      <div class="w-full px-1 py-4">
        <div class="w-full border-b border-magnesium/50 pb-6 flex justify-between items-center">
          <h1 class="hidden font-bold lg:text-2xl text-xl" id="video_name"></h1>
          <div class="w-40 h-7 bg-magnesium rounded-lg animate-pulse" id="video_name_placeholder"></div>

          <div class="flex justify-center items-start gap-2">
            <!-- Share button -->
            <button
              class="share-btn hidden flex items-center justify-center rounded-full h-fit w-fit p-2.5 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
              data-tip="Share this video">
              <i data-lucide="forward" class="w-5.5 h-auto"></i>
            </button>
            <div class="w-12 h-12 bg-magnesium rounded-full animate-pulse" id="share_btn_placeholder"></div>


            <!-- Download button -->
            <button
              class="download-btn hidden flex items-center justify-center rounded-full h-fit w-fit p-2.5 bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
              data-tip="Download this video">
              <i data-lucide="download" class="w-5.5 h-auto"></i>
            </button>
            <div class="w-12 h-12 bg-magnesium rounded-full animate-pulse" id="download_btn_placeholder"></div>

            <!-- Like button -->
            <button
              class="like-btn hidden flex items-center justify-center rounded-full h-fit w-fit p-2.5 bg-swiss-plum/20 text-swiss-plum hover:bg-swiss-plum hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
              data-tip="Like this video">
              <i data-lucide="thumbs-up" class="w-5.5 h-auto"></i>
            </button>
            <div class="w-12 h-12 bg-magnesium rounded-full animate-pulse" id="like_btn_placeholder"></div>

            <!-- Dislike button -->
            <button
              class="dislike-btn hidden flex items-center justify-center rounded-full h-fit w-fit p-2.5 bg-vivaldi-red/30 text-vivaldi-red hover:bg-vivaldi-red hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
              data-tip="Dislike this video">
              <i data-lucide="thumbs-down" class="w-5.5 h-auto"></i>
            </button>
            <div class="w-12 h-12 bg-magnesium rounded-full animate-pulse" id="dislike_btn_placeholder"></div>
          </div>
        </div>

        <div class="flex justify-between items-start gap-1 pt-6">
          <div class="flex items-stretch justify-center gap-4">
            <div class="flex flex-col justify-center">
              <span class="text-lg hidden text-carbon font-semibold tracking-wide" id="owner_video" data-id=""></span>
              <div class="w-40 h-7 bg-magnesium rounded-lg animate-pulse my-1" id="owner_video_placeholder"></div>
              <span class="text-sm hidden text-adhesion font-semibold tracking-wide" id="owner_follower"></span>
              <div class="w-30 h-7 bg-magnesium rounded-lg animate-pulse my-1" id="owner_follower_placeholder"></div>
            </div>

            <!-- Follow button -->
            <button
              class="follow-btn hidden flex items-center justify-center rounded-lg h-full w-fit p-2 bg-hot-shot/30 text-hot-shot hover:bg-hot-shot hover:text-white dark:hover:bg-white-owl transition tooltip tooltip-bottom"
              data-tip="Follow this user">
              <i data-lucide="user-plus" class="w-4 h-auto"></i>
              <span class="ml-2 text-sm font-semibold" id="follow_btn_text">Follow</span>
            </button>
            <div class="w-40 h-12 bg-magnesium rounded-lg animate-pulse" id="follow_button_placeholder"></div>
          </div>

          <p class="text-sm hidden text-carbon font-semibold tracking-wide" id="date_created"></p>
          <div class="w-52 h-7 bg-magnesium rounded-lg animate-pulse my-1" id="date_created_placeholder"></div>
        </div>
      </div>
      <!-- Detail Video :end -->
    </div>
    <!-- Text Header :end -->
  </div>

  @include('pages.partials.share-modal')

  @push('styles')
  @endpush

  @push('scripts')
  <script src="{{ asset('assets/js/watch/index.js') }}"></script>
  <script>
    window.authUserId = {{ auth()->id() ?? 'null' }};
  </script>
  @endpush
</x-app-layout>