<x-app-layout :pageTitle="'User Management'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header :begin -->
   <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot">User Management</h1>
   <!-- Text Header :end -->

   <div class="w-full flex flex-col lg:flex-row justify-between items-start gap-4">
    @include('pages.user-management.partial.user-list')
    @include('pages.user-management.partial.add-form')
   </div>
  </div>
 </div>

 @push('styles')
 <link rel="stylesheet" href="{{ asset('vendors/tom-select/tom-select.css') }}">
 <style>
  #users-table tbody td {
   white-space: normal;
   word-break: break-word;
   overflow-wrap: anywhere;
  }
 </style>
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('vendors/form-request/formValidation.js') }}"></script>
 <script src="{{ asset('vendors/tom-select/tom-select.complete.min.js') }}"></script>
 <script src="{{ asset('assets/js/user-management/index.js') }}"></script>
 @endpush
</x-app-layout>