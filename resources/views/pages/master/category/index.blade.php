<x-app-layout :pageTitle="'Master Category'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5">
   <!-- Text Header :begin -->
   <h1 class=" md:text-4xl text-2xl font-bold text-hot-shot w-full mb-4">Master Category</h1>
   <!-- Text Header :end -->

   <div class="w-full flex flex-col lg:flex-row justify-between items-start gap-4">
    @include('pages.master.category.partial.category-list')
    @include('pages.master.category.partial.add-form')
   </div>
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 <script src="{{ asset('vendors/jquery/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('vendors/custom-datatable/index.js') }}"></script>
 <script src="{{ asset('vendors/form-request/form.js') }}"></script>
 <script src="{{ asset('assets/js/master/category.js') }}"></script>
 @endpush
</x-app-layout>