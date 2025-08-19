<nav class="bg-white border-b border-base-200 py-3">
 <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
  <div class="flex justify-end h-fit items-center">
   <!-- Navigation Links -->
   <div class="space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <!-- User Management -->
    <x-nav-link :href="url('/')" :active="request()->routeIs('home.*')">
     {{ __('User Management') }}
    </x-nav-link>
   </div>

   <!-- Menus -->
   <div class="sm:flex sm:items-center sm:ms-6">
    <!-- Master Dropdown -->
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
        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
         <path fill-rule="evenodd"
          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
          clip-rule="evenodd" />
        </svg>
       </div>
      </button>
     </x-slot>

     <x-slot name="content">
      <x-dropdown-link :href="url('/profile/edit')" :active="request()->routeIs('profile.*')">
       {{ __('Fields') }}
      </x-dropdown-link>
      <x-dropdown-link :href="url('/profile/edit')" :active="request()->routeIs('profile.*')">
       {{ __('Roles') }}
      </x-dropdown-link>
     </x-slot>
    </x-dropdown>
   </div>
  </div>
 </div>
</nav>