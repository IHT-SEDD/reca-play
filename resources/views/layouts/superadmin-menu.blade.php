<!-- User Management -->
<x-nav-link :href="url('/user-management')" :active="request()->routeIs('user-management.*')">
 {{ __('User Management') }}
</x-nav-link>

<!-- Get Video -->
<x-nav-link :href="url('/get-video')" :active="request()->routeIs('get-video.*')">
 {{ __('Get Video') }}
</x-nav-link>

<!-- Masters -->
<x-dropdown.dropdown-flowbite trigger="Masters" iconTrigger="">
 <li class="w-full">
  <a href="{{ url('master/venue') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   Venues
  </a>
  <a href="{{ url('master/venue-type') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   Venue Types
  </a>
  <a href="{{ url('master/field') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   Fields
  </a>
  <a href="{{ url('master/role') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   Roles
  </a>
  <a href="{{ url('master/category') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   Categories
  </a>
  <a href="{{ url('master/camera') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   Cameras
  </a>
  <a href="{{ url('master/nvr') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   NVR's
  </a>
  <a href="{{ url('master/qr_code') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   QR Codes
  </a>
  <a href="{{ url('master/port') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   Port
  </a>
  <a href="{{ url('master/api') }}"
   class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
   <i data-lucide="dot" class="w-4 h-auto me-2"></i>
   API
  </a>
 </li>
</x-dropdown.dropdown-flowbite>