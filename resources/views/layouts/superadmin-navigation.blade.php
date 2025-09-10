<nav x-data="{ open: false }" class="bg-white border-b border-base-200 py-3">
  <!-- Primary Navigation Menu -->
  <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-end h-fit items-center">
      <!-- Navigation Links -->
      <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex">
        <!-- Masters -->
        <x-dropdown.dropdown-flowbite trigger="Masters">
          <li class="w-full">
            <a href="{{ url('master/venue') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              Venues
            </a>
            <a href="{{ url('master/venue-type') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              Venue Types
            </a>
            <a href="{{ url('master/field') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              Fields
            </a>
            <a href="{{ url('master/role') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              Roles
            </a>
            <a href="{{ url('master/category') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              Categories
            </a>
            <a href="{{ url('master/camera') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              Cameras
            </a>
            <a href="{{ url('master/nvr') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              NVR's
            </a>
            <a href="{{ url('master/qr_code') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              QR Codes
            </a>
            <a href="{{ url('master/port') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              Port
            </a>
            <a href="{{ url('master/api') }}"
              class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot inline-flex justify-start items-center w-full">
              <i data-lucide="dot" class="w-4 h-auto me-2"></i>
              API
            </a>
          </li>
        </x-dropdown.dropdown-flowbite>

        <!-- User Management -->
        <x-nav-link class="text-sm" :href="url('/user-management')" :active="request()->routeIs('user-management.*')">
          {{ __('User Management') }}
        </x-nav-link>
      </div>

      <!-- Hamburger -->
      <div class="-me-2 flex items-center sm:hidden">
        <button @click="open = ! open"
          class="inline-flex items-center justify-center p-2 rounded-md text-after-midnight hover:text-hot-shot transition duration-150 ease-in-out">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round"
              stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
              stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Responsive Navigation -->
  <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <!-- Responsive Navigation Menu -->
    <div class="pt-2 pb-3 space-y-1">
      <!-- User Management -->
      <x-responsive-nav-link class="text-sm" :href="url('/user-management')"
        :active="request()->routeIs('user-management.*')">
        {{ __('User Management') }}
      </x-responsive-nav-link>

      <!-- Masters -->
      <div x-data="{ open: false }" class="sm:hidden">
        <!-- Toggle button -->
        <button @click="open = !open"
          class="flex items-center justify-between w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start tracking-wide capitalize text-after-midnight font-medium hover:text-hot-shot hover:border-hot-shot transition duration-150 ease-in-out text-sm">
          Masters
          <!-- Chevron -->
          <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
            class="w-[15px] h-[15px] transition-transform duration-300"></i>
        </button>

        <!-- Dropdown menu -->
        <div x-show="open" x-transition x-cloak class="mt-2 space-y-1">
          <x-responsive-nav-link class="text-xs" :href="url('/master/field')"
            :active="request()->routeIs('master.field.*')">
            {{ __('Fields') }}
          </x-responsive-nav-link>

          <x-responsive-nav-link class="text-xs" :href="url('/master/role')"
            :active="request()->routeIs('master.role.*')">
            {{ __('Roles') }}
          </x-responsive-nav-link>
        </div>
      </div>
    </div>
  </div>
</nav>