<x-app-layout :pageTitle="'My Profile'">
 <div class="w-full mx-auto">
  <!-- Text Header :begin -->
  <h1 class="md:text-4xl text-2xl font-bold text-hot-shot pt-4 pb-6 text-center">My Profile</h1>
  <!-- Text Header :end -->

  <div class="w-full flex flex-col lg:flex-row justify-center items-center lg:items-start gap-3">
   <!-- Information & Summary Card :begin -->
   <div class="w-full max-w-md flex flex-col justify-center items-center gap-3">
    @include('pages.my-profile.partial.summary-card')
    @include('pages.my-profile.partial.information-card')
   </div>
   <!-- Information & Summary Card :end -->

   <!-- last Activity Card :begin -->
   {{-- <div class="w-full max-w-md flex flex-col justify-center items-center gap-3">
    @include('pages.my-profile.partial.summary-card')
   </div> --}}
   <!-- last Activity Card :end -->
  </div>
 </div>

 @include('pages.my-profile.partial.edit-modal')

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('assets/js/my-profile/index.js') }}"></script>
 @endpush
</x-app-layout>