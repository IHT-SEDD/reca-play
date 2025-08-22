<x-app-layout :pageTitle="'User Management'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header :begin -->
   <div class="mb-4 tracking-wide flex flex-col justify-between gap-2">
    <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot">USER MANAGEMENT</h1>
   </div>
   <!-- Text Header :end -->

   @include('pages.user-management.partial.user-list')
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('assets/js/user-management/index.js') }}"></script>
 @endpush
</x-app-layout>