<x-app-layout :pageTitle="'Get Video'">
 <div class="w-full mx-auto">
  <!-- Text Header :begin -->
  <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot pt-2 pb-6">Get Video</h1>
  <!-- Text Header :end -->

  <div class="w-full flex justify-between items-stretch gap-4">
   @include('pages.get-video.partial.search-form')
   @include('pages.get-video.partial.search-result')
   @include('pages.get-video.partial.add-form')
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('vendors/form-request/formValidation.js') }}"></script>
 <script src="{{ asset('assets/js/get-video/index.js') }}"></script>
 @endpush
</x-app-layout>