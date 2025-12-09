<dialog id="editUserModal" class="modal modal-bottom sm:modal-middle">
 <div class="modal-box">
  <h3 class="text-lg font-bold mb-2">Edit detail account</h3>

  <form method="PUT" action="{{ url('/my-profile/update') }}" class="ajax-form" novalidate>
   @csrf
   <!-- Name -->
   <div>
    <x-input-label for="name" :value="__('Name')" />
    <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')"
     autocomplete="off" />
    <x-input-error id="input-name-error"></x-input-error>
   </div>

   <!-- Username -->
   <div class="mt-2">
    <x-input-label for="username" :value="__('Username')" />
    <x-inputs.text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
     autocomplete="off" />
    <x-input-error id="input-username-error"></x-input-error>
   </div>

   <!-- Phone number -->
   <div class="mt-2">
    <x-input-label for="phone_number" :value="__('Phone Number')" />
    <x-inputs.text-input id="phone_number" class="block mt-2 w-full" type="text" name="phone_number"
     :value="old('phone_number')" autocomplete="off" />
    <x-input-error id="input-phone_number-error"></x-input-error>
   </div>

   <!-- Phone number -->
   <div class="mt-2">
    <x-input-label for="instagram" :value="__('Instagram')" />
    <x-inputs.text-input id="instagram" class="block mt-2 w-full" type="text" name="instagram" :value="old('instagram')"
     autocomplete="off" />
    <x-input-error id="input-instagram-error"></x-input-error>
   </div>

   <div class="grid grid-cols-2 gap-4">
       <!-- Submit btn -->
   <x-primary-button class="w-full mt-6">
    {{ __('Submit') }}
   </x-primary-button>
  </form>

     <!-- Close Button -->
   <form method="dialog">
    <button type="submit"
     class="rounded-xl bg-vivaldi-red/20 text-vivaldi-red hover:bg-vivaldi-red hover:text-white text-sm p-3 font-medium inline-flex items-center justify-center gap-2 w-full mt-6">
     Close
    </button>
   </form>
   </div>
 </div>
</dialog>
