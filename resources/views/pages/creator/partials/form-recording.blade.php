<form method="POST" action="{{ url('creator/new/add/record') }}" class="ajax-form" novalidate>
 @csrf
 <div>
  <div class="flex justify-between items-center">
   <x-input-label for="session_code" :value="__('Code Access')" />
   <button class="p-1 rounded-full text-after-midnight font-medium tooltip tooltip-info" type="button"
    data-tip="Get the access code from cashier.">
    <i data-lucide="info" class="w-4 h-4 md:w-5 md:h-5"></i>
   </button>
  </div>
  <x-inputs.text-input id="session_code" class="block mt-2 w-full" type="text" name="session_code"
   :value="old('session_code')" required placeholder="Input access code" autocomplete="off" />
  <x-input-error id="input-session_code-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="video_name" :value="__('Video Name')" />
  <x-inputs.text-input id="video_name" class="block mt-2 w-full" type="text" name="video_name"
   :value="old('video_name')" required placeholder="New video name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="duration" :value="__('Duration Recording')" />
  <div class="flex lg:flex-row flex-col justify-between lg:items-center items-start w-full">
   <x-inputs.radio-input id="duration" name="duration" value="30">30 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="60">60 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="120">120 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="240">240 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="300">300 Min</x-inputs.radio-input>
  </div>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6" id="start_recording">
  {{ __('Start Recording') }}
 </x-primary-button>
</form>