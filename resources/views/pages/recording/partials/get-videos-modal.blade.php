<dialog id="getVideoModal" class="modal modal-bottom sm:modal-middle">
 <div class="modal-box">
  <h3 class="text-lg font-bold mb-2">Get Video</h3>
  <x-input-label for="access_code" :value="__('Access Code')" />
  <x-inputs.text-input id="access_code" class="block mt-2 w-full" type="text" name="access_code" required
   placeholder="Enter your access code" />

  <!-- Actions -->
  <div class="modal-action flex justify-between w-full">
   <!-- Submit Button -->
   <button id="submitAccessCode" type="button"
    class="rounded-xl bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white text-sm p-3 font-medium inline-flex items-center justify-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-auto"></i>
    Submit
   </button>

   <!-- Close Button -->
   <form method="dialog">
    <button type="submit"
     class="rounded-xl bg-vivaldi-red/20 text-vivaldi-red hover:bg-vivaldi-red hover:text-white text-sm p-3 font-medium inline-flex items-center justify-center gap-2">
     <i data-lucide="x" class="w-4 h-auto "></i>
     Close
    </button>
   </form>
  </div>
 </div>
</dialog>