<nav x-data="{ open: false }"
  class="bg-white dark:bg-thamar-black border-b border-base-200 dark:border-transparent py-3">
  <!-- Primary Navigation Menu -->
  <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-end h-fit items-center">
      <!-- Navigation Links -->
      <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex">
        <!-- User Management -->
        <x-nav-link class="text-sm" :href="url('/venue-management')" :active="request()->routeIs('venue-management.*')">
          {{ __('Venue Management') }}
        </x-nav-link>
      </div>

      <!-- Hamburger -->
      <div class="-me-2 flex items-center sm:hidden">
        <button @click="open = ! open"
          class="inline-flex items-center justify-center p-2 rounded-md text-after-midnight dark:text-white hover:text-hot-shot dark:hover:text-hot-shot transition duration-150 ease-in-out">
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
      <x-responsive-nav-link class="text-sm" :href="url('/venue-management')"
        :active="request()->routeIs('venue-management.*')">
        {{ __('Venue Management') }}
      </x-responsive-nav-link>
    </div>
  </div>
</nav>