<dialog id="master-delete-modal" class="modal modal-bottom sm:modal-middle" data-datatable="#{{ $type }}-table">
  <div class="modal-box text-center">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <h1 class="w-full text-center text-hot-shot font-bold text-xl md:text-2xl">
        Delete {{ $tittle }}
      </h1>
      <form method="dialog">
        <button
          class="text-after-midnight hover:text-hot-shot transition-colors duration-200"
        >
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </form>
    </div>

    <!-- Icon -->
    <div class="flex justify-center mb-4">
      <i data-lucide="octagon-alert" class="w-24 h-24 text-warning"></i>
    </div>

    <!-- Text -->
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Are you sure?</h3>
    <h4 class="text-gray-600">You won't be able to revert this!</h4>

    <!-- Action buttons -->
    <div class="modal-action mt-6 flex justify-center gap-4">
      <form method="dialog">
        <button class="btn">Cancel</button>
      </form>
      <x-secondary-button btnId="master-delete-btn" bg="bg-error" hoverBg="hover:bg-after-red-400">Yes, delete it!</x-secondary-button>
    </div>
  </div>
</dialog>
