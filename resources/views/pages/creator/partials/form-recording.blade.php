<div class="overflow-x-hidden" id="formRecording">
 <form method="POST" action="{{ url('creator/new/add/record') }}" class="ajax-form"
  novalidate>
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

  {{-- <div class="mt-2">
   <x-input-label for="video_name" :value="__('Video Name')" :required="true" />
   <x-inputs.text-input id="video_name" class="block mt-2 w-full" type="text" name="video_name"
    :value="old('video_name')" :required="true" placeholder="New video name" />
   <x-input-error id="input-video_name-error"></x-input-error>
  </div> --}}

  {{-- <div class="mt-2">
   <x-input-label for="duration" :value="__('Duration Recording')" :required="true" />
   <div class="flex lg:flex-row flex-col justify-between lg:items-center items-start w-full mt-2">
    <x-inputs.radio-input id="duration" name="duration" value="30">30 Min</x-inputs.radio-input>
    <x-inputs.radio-input id="duration" name="duration" value="60">60 Min</x-inputs.radio-input>
    <x-inputs.radio-input id="duration" name="duration" value="120">120 Min</x-inputs.radio-input>
    <x-inputs.radio-input id="duration" name="duration" value="240">240 Min</x-inputs.radio-input>
    <x-inputs.radio-input id="duration" name="duration" value="300">300 Min</x-inputs.radio-input>
   </div>
  </div> --}}

  <!-- Submit btn -->
  <x-primary-button class="w-full mt-6" id="start_recording">
   {{ __('Start Recording') }}
  </x-primary-button>
 </form>
</div>