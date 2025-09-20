<div id="venue_loader" class="w-full justify-center absolute py-6 hidden">
 <span class="loading loading-spinner text-hot-shot loading-lg"></span>
</div>

<div class="w-full grid grid-cols-1 md:grid-cols-6 gap-4" id="container_venue_list">
 <!-- See more btn -->
 <div class="items-center flex justify-center md:col-span-6 w-full mt-8">
  <x-secondary-button class="w-fit max-w-xs" btnId="seemore_btn" bg="bg-base-200" hoverBg="hover:bg-base-300"
   textColor="text-after-midnight">
   {{ __('See More') }}
   <i data-lucide="ellipsis" class="w-4 h-4 ms-2"></i>
  </x-secondary-button>
 </div>
</div>