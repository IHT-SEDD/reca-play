<dialog id="shareModal" class="modal modal-bottom sm:modal-middle">
 <div class="modal-box">
  <h3 class="text-lg font-bold mb-2">Share Video</h3>

  {{--
  <!-- Share to social media -->
  <div class="flex items-start justify-start gap-2 w-full py-4">
   <!-- Instagram -->
   <a href="https://instagram.com/username" target="_blank" rel="noopener noreferrer" class="tooltip tooltip-right"
    data-tip="Share to instagram">
    <img src="{{ asset('assets/icons/instagram-colored.svg') }}" alt="Instagram Logo" class="w-12 h-12 cursor-pointer">
   </a>
  </div> --}}

  <!-- Share URL -->
  <input type="text" id="shareLinkInput" readonly class="w-full border rounded p-2 text-sm mb-3" />

  <!-- Actions -->
  <div class="modal-action flex justify-between w-full">
   <!-- Copy Link Button -->
   <button id="copyShareLink" type="button"
    class="rounded-xl bg-hot-shot/20 text-hot-shot hover:bg-hot-shot hover:text-white text-sm p-3 font-medium inline-flex items-center justify-center gap-2">
    <i data-lucide="clipboard" class="w-4 h-auto "></i>
    Copy Link
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