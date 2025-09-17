@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new api</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/api/add-data') }}" class="ajax-form" data-datatable="#api-table" novalidate>
 @csrf
 <div>
  <x-input-label for="name" :value="__('Name')" required />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New api name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="url" :value="__('URL')" required />
  <x-inputs.text-input id="url" class="block mt-2 w-full" type="text" name="url" :value="old('url')" required
   placeholder="New api url" autocomplete="off" />
  <x-input-error id="input-url-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="username" :value="__('Username')" />
  <x-inputs.text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
   placeholder="New api username" autocomplete="off" />
  <x-input-error id="input-username-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="password" :value="__('Password')" />
  <x-inputs.text-input id="password" class="block mt-2 w-full" type="text" name="password" :value="old('password')"
   placeholder="New api password" autocomplete="off" />
  <x-input-error id="input-password-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="is_active" :value="__('Active?')" required />
  <x-inputs.toggle-input id="is_active" name="is_active" required />
  <x-input-error id="input-is_active-error"></x-input-error>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection