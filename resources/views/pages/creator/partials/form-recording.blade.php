<form method="POST" action="{{ url('creator/new/add/record') }}" class="ajax-form" novalidate>
 @csrf
 <div>
  <x-input-label for="video_name" :value="__('Video Name')" />
  <x-inputs.text-input id="video_name" class="block mt-2 w-full" type="text" name="video_name" :value="old('video_name')" required
   placeholder="New video name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="duration" :value="__('Duration Recording')" />
  <div class="flex justify-between items-center w-full">
   <x-inputs.radio-input id="duration" name="duration" value="30">30 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="60">60 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="120">120 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="240">240 Min</x-inputs.radio-input>
   <x-inputs.radio-input id="duration" name="duration" value="300">300 Min</x-inputs.radio-input>
  </div>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Start Recording') }}
 </x-primary-button>
</form>