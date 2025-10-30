<x-app-layout :pageTitle="'Master Role'">
 <div class="w-full mx-auto">
  <!-- Text Header :begin -->
  <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot w-full mb-4">Master Role</h1>
  <!-- Text Header :end -->

  <div class="w-full flex flex-col lg:flex-row justify-between items-start gap-4">
   @include('pages.master.role.partial.role-list')
   @include('pages.master.role.partial.add-form')
  </div>
 </div>

  @include('pages.master.role.partial.edit-form')

  @include('pages.master.partial.master-delete-modal')

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('vendors/form-request/formValidation.js') }}"></script>
 <script src="{{ asset('assets/js/master/role.js') }}"></script>
 @endpush
</x-app-layout>
