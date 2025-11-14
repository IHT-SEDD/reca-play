<nav class="main-footer-default">
  <div class="w-full mx-auto p-5 lg:p-6">
    <!-- Row 1 :begin -->
    <div
      class="flex flex-row justify-between items-center w-full gap-3 lg:gap-2 border-b border-white-edgar pb-4 lg:pb-6 mb-4 lg:mb-6">
      <!-- Section 1 :begin -->
      <div class="flex items-center">
        <a href="/"
          class="flex items-center gap-3 text-sm font-bold dark:text-white dark:hover:text-hot-shot hover:text-hot-shot">
          <img x-show="!darkMode" src="{{ asset('assets/img/logos/reca-black.png') }}" alt="Logo RECA"
            class="w-6 h-auto">
          <img x-show="darkMode" src="{{ asset('assets/img/logos/reca-white.png') }}" alt="Logo RECA"
            class="w-6 h-auto">
          RECA PLAY
        </a>
      </div>
      <!-- Section 1 :end -->

      <!-- Section 2 :begin -->
      <div class="flex items-center">
        <h1 class="text-sm font-semibold italic text-color-default">
          "Feel the Field"
        </h1>
      </div>
      <!-- Section 2 :end -->
    </div>
    <!-- Row 1 :end -->

    <!-- Row 2 :begin -->
    <div class="flex flex-col md:flex-row justify-between items-start lg:items-center w-full gap-2">
      <!-- Section 1 :begin -->
      <div class="flex flex-col md:flex-row lg:gap-10 gap-4 lg:mb-0 mb-4">
        <!-- Navigation -->
        <div class="flex flex-col">
          <h1 class="text-sm font-semibold text-color-default mb-3">Navigation</h1>
          <ul class="flex flex-col gap-1">
            <li>
              <a href="{{ '/' }}" class="text-xs hover:text-hot-shot dark:hover:text-hot-shot text-color-default">
                Home
              </a>
            </li>
            <li>
              <a href="{{ '/venue' }}" class="text-xs hover:text-hot-shot dark:hover:text-hot-shot text-color-default">
                Venues
              </a>
            </li>
            <li>
              <a href="{{ '/event' }}" class="text-xs hover:text-hot-shot dark:hover:text-hot-shot text-color-default">
                Event
              </a>
            </li>
            <li>
              <a href="{{ '/my-recording' }}"
                class="text-xs hover:text-hot-shot dark:hover:text-hot-shot text-color-default">
                My Recording
              </a>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="flex flex-col">
          <h1 class="text-sm font-semibold text-color-default mb-3">Contact</h1>
          <ul class="flex flex-col gap-2">
            <li>
              <a href="https://www.instagram.com/reca.play/"
                class="flex items-center text-xs hover:text-hot-shot dark:hover:text-hot-shot text-color-default">
                <i data-lucide="instagram" class="w-4 h-auto me-2"></i> @reca.play
              </a>
            </li>
            <li>
              <a href="mailto:reca.indohadetama@gmail.com"
                class="flex items-center text-xs hover:text-hot-shot dark:hover:text-hot-shot text-color-default">
                <i data-lucide="mail" class="w-4 h-auto me-2"></i> reca.indohadetama@gmail.com
              </a>
            </li>
            <li>
              <a href="https://maps.app.goo.gl/xH3BXjygxrKZ68do8"
                class="flex items-center text-xs hover:text-hot-shot dark:hover:text-hot-shot text-color-default">
                <i data-lucide="map-pin" class="w-4 h-auto me-2"></i> Bandung, West Java, Indonesia
              </a>
            </li>
          </ul>
        </div>
      </div>
      <!-- Section 1 :end -->

      <!-- Copyright -->
      <div class="flex items-center">
        <h1 class="text-sm font-semibold italic text-color-default">&copy; 2025 RECA PLAY All rights</h1>
      </div>
    </div>
    <!-- Row 2 :end -->
  </div>
  </div>
</nav>