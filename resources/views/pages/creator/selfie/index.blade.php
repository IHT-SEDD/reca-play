<x-app-layout :pageTitle="'Record a Moment'">
  <div class="flex justify-center items-center">
    <div class="w-full max-w-8xl mx-auto">
      <div class="flex justify-center items-center flex-col gap-4">
        <!-- Title -->
        <div class="flex flex-col justify-center items-center gap-1 mb-4">
          <h1 class="md:text-4xl text-2xl text-center font-bold text-black">
            Your moment is ready to get live!
          </h1>
        </div>

        <!-- Loading bar indicator :begin -->
        <div id="loading_bar" class="hidden w-full max-w-4xl h-1 bg-gray-200 rounded-full overflow-hidden">
          <div id="loading_progress" class="h-full bg-hot-shot"></div>
        </div>
        <!-- Loading bar indicator :end -->

        <!-- Main :begin -->
        <div class="flex flex-col lg:flex-row justify-between items-stretch w-full max-w-5xl gap-3">

          <!-- Live Section :begin -->
          <div class="flex flex-col w-full gap-2">

            <!-- Live Preview -->
            <div class="w-full">
              <video id="preview_cam" autoplay playsinline muted
                class="block w-full rounded-xl bg-eerie-black aspect-video"></video>
            </div>

            <!-- Live Data -->
            <div
              class="flex flex-col md:flex-row items-center justify-between w-full p-3 rounded-xl bg-orochimaru/70 space-x-4 gap-3 md:gap-0">
              <!-- Camera Info -->
              <div class="flex items-center gap-2 text-md font-semibold text-after-midnight">
                <i data-lucide="cctv" class="w-5 h-5"></i>
                <span id="cam_name"></span>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-center gap-2">
                <!-- Change Camera -->
                <button id="change_cam_btn" data-tip="Change camera preview"
                  class="flex items-center justify-center p-3 rounded-full bg-info text-white-edgar hover:bg-info/70 transition-colors duration-100 tooltip tooltip-bottom">
                  <i data-lucide="cctv" class="w-4 h-4"></i>
                </button>

                <!-- Full Screen -->
                <button id="full_screen_btn" data-tip="Full screen preview"
                  class="flex items-center justify-center p-3 rounded-full bg-hot-shot text-white-edgar hover:bg-hot-shot/80 transition-colors duration-100 tooltip tooltip-bottom">
                  <i data-lucide="maximize-2" class="w-4 h-4"></i>
                </button>
              </div>
            </div>
          </div>
          <!-- Live Section :end -->

          <!-- Control Panel :begin -->
          <div
            class="flex flex-col items-start justify-start w-full lg:w-1/3 h-fit p-4 rounded-xl bg-white border border-white-edgar">
            <!-- Timer Panel -->
            <div
              class="flex items-center justify-between w-full p-4 rounded-2xl bg-base-200/50 border border-white-edgar space-x-4">
              <div class="flex flex-col">
                <div class="flex items-center gap-2 text-sm text-carbon">
                  <i data-lucide="timer" class="w-4 h-4"></i>
                  <span>Timer</span>
                </div>
                <p id="timer"></p>
              </div>

              <button id="stop_stream" data-tip="Stop streaming"
                class="flex items-center justify-center p-3 rounded-full bg-hot-shot text-white hover:bg-hot-shot/80 transition tooltip tooltip-bottom">
                <i data-lucide="square" class="w-4 h-4"></i>
              </button>
            </div>

            <!-- Data Panel -->
            <div class="flex items-center justify-between w-full p-5 mt-3 rounded-xl border border-base-300">
              <div class="flex flex-col gap-4">
                <!-- Venue & Field -->
                <div class="flex flex-col gap-1">
                  <div class="flex items-center gap-2 text-sm text-carbon">
                    <i data-lucide="land-plot" class="w-4 h-4"></i>
                    <span id="venue_name" class="font-medium"></span>
                  </div>
                  <p id="field_name" class="text-lg font-semibold tracking-wide text-after-midnight"></p>
                </div>

                <!-- Stream & Duration -->
                <div class="flex flex-col gap-1">
                  <div class="flex items-center gap-2 text-sm text-carbon">
                    <i data-lucide="airplay" class="w-4 h-4"></i>
                    <span id="stream_name" class="font-medium"></span>
                  </div>
                  <p id="duration_stream" class="text-lg font-semibold tracking-wide text-after-midnight"></p>
                </div>
              </div>
            </div>
          </div>
          <!-- Control Panel :end -->
        </div>
        <!-- Main :end -->
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
  <script src="{{ asset('assets/js/creator/stream/index.js') }}"></script>
  @endpush
</x-app-layout>