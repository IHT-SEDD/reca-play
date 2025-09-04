<div class="bg-white p-4 rounded-xl shadow-sm w-full max-w-md" id="add-form-wrapper">
 <form method="POST" action="{{ url('/master/field/add-data') }}" id="add-field-form">
  @csrf
  <!-- Name -->
  <div>
   <x-input-label for="name" :value="__('Name')" />
   <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
    autofocus placeholder="your full name" />
   <x-input-error :messages="$errors->get('name')" class="mt-2" />
  </div>

  <!-- Submit btn -->
  <x-primary-button class="w-full mt-4">
   {{ __('Submit') }}
  </x-primary-button>
 </form>
</div>