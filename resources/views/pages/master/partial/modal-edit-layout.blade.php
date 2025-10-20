<dialog id="modal_master" class="modal modal-bottom sm:modal-middle">
 <div class="modal-box">
  <div class="mb-6 flex items-center justify-between">
   @yield('edit-title')
   <form method="dialog">
    <button class="text-after-midnight hover:text-hot-shot"><i data-lucide="x" class="w-5 h-5"></i></button>
   </form>
  </div>

  @yield('edit-content')
 </div>
</dialog>