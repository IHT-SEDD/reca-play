<div class="bg-white p-4 rounded-xl shadow-sm w-full lg:max-w-md h-full" id="master-add-form-wrapper">
 <div class="pb-4 border-b border-orochimaru mb-4">
  <h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Search Video</h1>
 </div>

 <form method="POST" action="{{ url('/get-video/search') }}" class="ajax-form" novalidate id="search-video-form" data-result-wrapper="#search-result-output">
  @csrf
  <!-- Host -->
  <div>
   <x-input-label for="host" :value="__('IP Host')" required />
   <x-inputs.text-input id="host" class="block mt-2 w-full" type="text" name="host" :value="old('host')" required
    placeholder="IP Host NVR" autocomplete="off" />
   <x-input-error id="input-host-error"></x-input-error>
  </div>

  <!-- Username -->
  <div class="mt-2">
   <x-input-label for="username" :value="__('Username')" required />
   <x-inputs.text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
    required placeholder="Username NVR" autocomplete="off" />
   <x-input-error id="input-username-error"></x-input-error>
  </div>

  <!-- Password -->
  <div class="mt-2">
   <x-input-label for="password" :value="__('Password')" required />
   <x-inputs.text-input id="password" class="block mt-2 w-full" type="text" name="password" :value="old('password')"
    required placeholder="Password NVR" autocomplete="off" />
   <x-input-error id="input-password-error"></x-input-error>
  </div>

  <!-- Channel -->
  <div class="mt-2">
   <x-input-label for="channel" :value="__('Channel Camera')" required />
   <x-inputs.text-input id="channel" class="block mt-2 w-full" type="text" name="channel" :value="old('channel')"
    required placeholder="Channel camera based on NVR" autocomplete="off" />
   <x-input-error id="input-channel-error"></x-input-error>
  </div>

  <!-- Start time -->
  <div class="mt-2">
   <x-input-label for="start_time" :value="__('Start time')" required />
   <x-inputs.time-picker id="start_time" class="block mt-2 w-full" type="text" name="start_time"
    :value="old('start_time')" required placeholder="Choose start time" />
   <x-input-error id="input-start_time-error"></x-input-error>
  </div>

  <!-- End time -->
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