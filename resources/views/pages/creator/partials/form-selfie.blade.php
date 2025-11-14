<div class="overflow-x-hidden" id="formSelfie">
 <form method="POST" action="{{ url('creator/new/add/selfie') }}" class="ajax-form" novalidate>
  @csrf
  <div>
   <x-input-label for="session_code" :value="__('Code Access')" :required="true">
    <x-slot:extras>
     <p class="tracking-wide text-xs font-medium italic">Get the access code from cashier.</p>
    </x-slot:extras>
   </x-input-label>
   <x-inputs.text-input id="session_code" class="block mt-2 w-full" type="text" name="session_code"
    :value="old('session_code')" :required="true" placeholder="Input access code" />
   <x-input-error id="input-session_code-error"></x-input-error>
  </div>

  <div class="mt-2">
   <x-input-label for="pict_name" :value="__('Picture Name')" :required="true">
    <x-slot:extras>
     <p class="tracking-wide text-xs font-medium italic">Picture name will appear in your photo file name</p>
    </x-slot:extras>
   </x-input-label>
   <x-inputs.text-input id="pict_name" class="block mt-2 w-full" type="text" name="pict_name" :value="old('pict_name')"
    :required="true" placeholder="Input access code" />
   <x-input-error id="input-pict_name-error"></x-input-error>
  </div>

  <!-- Submit btn -->
  <x-primary-button class="w-full mt-6" id="start_selfie">
   {{ __('Start Selfie') }}
  </x-primary-button>
 </form>
</div>