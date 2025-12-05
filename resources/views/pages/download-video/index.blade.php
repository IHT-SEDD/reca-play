<x-app-layout :pageTitle="'Download Video'">
 <div class="w-full mx-auto">
  <!-- Text Header :begin -->
  <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot pt-4 pb-6">Download Video</h1>
  <!-- Text Header :end -->

  <div class="w-full flex justify-center items-center gap-4">
   @include('pages.download-video.partial.add-form')
  </div>
 </div>

 @push('styles')
 <link rel="stylesheet" href="{{ asset('vendors/tom-select/tom-select.css') }}">
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('vendors/form-request/formValidation.js') }}"></script>
 <script src="{{ asset('vendors/tom-select/tom-select.complete.min.js') }}"></script>
 <script src="{{ asset('assets/js/download-video/index.js') }}"></script>
 @endpush
</x-app-layout>