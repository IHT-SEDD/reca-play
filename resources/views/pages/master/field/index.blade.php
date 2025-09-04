<x-app-layout :pageTitle="'Master Fields'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header :begin -->
   <div class="mb-4 tracking-wide flex flex-col justify-between gap-2">
    <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot">Master Fields</h1>
   </div>
   <!-- Text Header :end -->

   <div class="flex justify-between items-start gap-3">
    @include('pages.master.field.partial.field-list')
    @include('pages.master.field.partial.add-form')
   </div>
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('assets/js/master/field/index.js') }}"></script>
 @endpush
</x-app-layout>