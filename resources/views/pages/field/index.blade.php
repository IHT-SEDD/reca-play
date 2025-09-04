<x-app-layout :pageTitle="'Field'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header 1 -->
   <div class="mb-8 w-full flex justify-center items-center">
    <h1 class=" md:text-4xl text-2xl font-bold text-after-midnight">
     LIST ALL FIELDS THAT HAS
     <span class="text-hot-shot">INTEGRATED</span>
     WITH US
    </h1>
   </div>

   <!-- Text Header 2 -->
   <div class="mb-10 w-full flex justify-center items-center">
    <h1 class=" md:text-2xl text-lg font-medium text-after-midnight">
     Hit our contact for integrating your field with us!
    </h1>
   </div>

   <!-- Search bar -->
   <div class="mb-6 w-full flex justify-end items-center">
    <div class="w-full max-w-xs">
     <x-inputs.text-input id="search" class="block mt-2 w-full" type="text" autofocus placeholder="search fields..." />
    </div>
   </div>

   <!-- Field List -->
   <div class="mb-6 w-full flex justify-center items-center">
    @include('pages.field.partials.field_list')
   </div>
  </div>
 </div>
</x-app-layout>