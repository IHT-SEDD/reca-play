<!-- User Management -->
<x-responsive-nav-link :href="url('/user-management')" :active="request()->routeIs('user-management.*')">
  {{ __('User Management') }}
</x-responsive-nav-link>

<!-- Get Video -->
<x-responsive-nav-link :href="url('/get-video')" :active="request()->routeIs('get-video.*')">
  {{ __('Get Video') }}
</x-responsive-nav-link>

<!-- Masters -->
<div x-data="{ open: false }" class="sm:hidden">
  <!-- Toggle button -->
  <button @click="open = !open"
    class="flex items-center justify-between w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start tracking-wide capitalize text-after-midnight dark:text-white font-medium hover:text-hot-shot dark:hover:text-hot-shot hover:border-hot-shot transition duration-150 ease-in-out text-sm">
    Masters
    <!-- Chevron -->
    <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
      class="w-[15px] h-[15px] transition-transform duration-300"></i>
  </button>

  <!-- Dropdown menu -->
  <div x-show="open" x-transition x-cloak class="mt-2 space-y-1">
    <x-responsive-nav-link class="text-xs" :href="url('/master/venue')" :active="request()->routeIs('master.venue.*')">
      {{ __('Venues') }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/venue-type')"
      :active="request()->routeIs('master.venue-type.*')">
      {{ __('Venue Types') }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/field')" :active="request()->routeIs('master.field.*')">
      {{ __('Fields') }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/role')" :active="request()->routeIs('master.role.*')">
      {{ __('Roles') }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/category')"
      :active="request()->routeIs('master.category.*')">
      {{ __('Categories') }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/camera')"
      :active="request()->routeIs('master.camera.*')">
      {{ __('Cameras') }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/nvr')" :active="request()->routeIs('master.nvr.*')">
      {{ __("NVR's") }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/qr_code')"
      :active="request()->routeIs('master.qr_code.*')">
      {{ __("QR Codes") }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/port')" :active="request()->routeIs('master.port.*')">
      {{ __("Ports") }}
    </x-responsive-nav-link>
    <x-responsive-nav-link class="text-xs" :href="url('/master/api')" :active="request()->routeIs('master.api.*')">
      {{ __("API's") }}
    </x-responsive-nav-link>
  </div>
</div>