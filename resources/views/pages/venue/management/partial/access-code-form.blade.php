<div class="bg-white p-4 rounded-xl shadow-sm w-full lg:col-span-1 md:col-span-2 col-span-1">
 <div class="pb-4 border-b border-orochimaru mb-4">
  <h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new access code</h1>
 </div>

 <form method="POST" action="{{ url('/venue-management/detail/access-code/add/' . $hashedId) }}" class="ajax-form"
  data-datatable="#access-code-table" novalidate>
  @csrf

  <x-input-label for="type" :value="__('Type')" :required="true" />
  <div class="flex lg:flex-row flex-col justify-start gap-2 lg:items-center items-start w-full mt-2">
   <x-inputs.radio-input id="type" name="type" value="record">Record</x-inputs.radio-input>
   <x-inputs.radio-input id="type" name="type" value="stream">Stream</x-inputs.radio-input>
  </div>

  <div class="mt-4">
   <x-input-label id="name_label" for="name" :value="__('Video Name')" required />
   <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
    placeholder="Video name" autocomplete="off" />
   <x-input-error id="input-name-error"></x-input-error>
  </div>

  <div class="mt-2">
   <x-input-label for="start_time" :value="__('Start time')" required />
   <x-inputs.time-picker id="start_time" class="block mt-2 w-full" type="text" name="start_time"
    :value="old('start_time')" required placeholder="Choose start time" />
   <x-input-error id="input-start_time-error"></x-input-error>
  </div>

  <div class="mt-2">
   <x-input-label for="end_time" :value="__('End time')" required />
   <x-inputs.time-picker id="end_time" class="block mt-2 w-full" type="text" name="end_time" :value="old('end_time')"
    required placeholder="Choose end time" />
   <x-input-error id="input-end_time-error"></x-input-error>
  </div>

  <!-- Submit btn -->
  <x-primary-button class="w-full mt-6">
   {{ __('Submit') }}
  </x-primary-button>
 </form>
</div>