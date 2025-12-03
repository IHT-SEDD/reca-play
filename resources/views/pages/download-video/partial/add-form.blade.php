@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Download Video</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/download-video/add-data') }}" class="ajax-form" novalidate>
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

 <!-- Password -->
 <div class="mt-2">
  <x-input-label for="uri" :value="__('Playback URI')" required />
  <x-inputs.text-input id="uri" class="block mt-2 w-full" type="text" name="uri" :value="old('uri')" required
   placeholder="Playback URI from NVR" autocomplete="off" />
  <x-input-error id="input-uri-error"></x-input-error>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection