<x-app-layout :pageTitle="'Master Port'">
 <div class="w-full mx-auto">
  <!-- Text Header :begin -->
  <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot w-full mb-4">Master Port</h1>
  <!-- Text Header :end -->

  <div class="w-full flex flex-col lg:flex-row justify-between items-start gap-4">
   @include('pages.master.port.partial.port-list')
   @include('pages.master.port.partial.add-form')
  </div>
 </div>

  @include('pages.master.port.partial.edit-form')

 @push('styles')
 <link rel="stylesheet" href="{{ asset('vendors/tom-select/tom-select.css') }}">
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('vendors/form-request/formValidation.js') }}"></script>
 <script src="{{ asset('vendors/tom-select/tom-select.complete.min.js') }}"></script>
 <script src="{{ asset('assets/js/master/port.js') }}"></script>
 @endpush
</x-app-layout>
