<nav x-data="{ open: false }" class="bg-white border-b border-base-200 py-3">
  <!-- Primary Navigation Menu -->
  <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-end h-fit items-center">
      <!-- Navigation Links -->
      <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
        <!-- Masters -->
        <x-dropdown align="right" width="48">
          <!-- Button trigger -->
          <x-slot name="trigger">
            <button
              class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-after-midnight hover:text-hot-shot transition ease-in-out duration-150">
              <div class="inline-flex items-center">
                <i data-lucide="hard-drive" class="w-4 h-4 me-2"></i>
                Masters
              </div>

              <div class="ms-1">
                <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
                  class="w-[15px] h-[15px] transition-transform duration-300"></i>
              </div>
            </button>
          </x-slot>

          <x-slot name="content">
            <x-dropdown-link :href="url('/master/field')" :active="request()->routeIs('master.field.*')">
              {{ __('Fields') }}
            </x-dropdown-link>
            <x-dropdown-link :href="url('/master/role')" :active="request()->routeIs('master.role.*')">
              {{ __('Roles') }}
            </x-dropdown-link>
            <x-dropdown-link :href="url('/master/category')" :active="request()->routeIs('master.category.*')">
              {{ __('Categories') }}
            </x-dropdown-link>
          </x-slot>
        </x-dropdown>
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