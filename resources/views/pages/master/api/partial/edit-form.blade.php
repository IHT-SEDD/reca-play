@extends('pages.master.partial.modal-edit-layout')

@section('edit-title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Edit API</h1>
@endsection

@section('edit-content')
<form method="PUT" action="{{ url('/master/api/update-data') }}" id="edit-form" class="ajax-edit-form"
  data-datatable="#api-table" novalidate>
  @csrf
  @method('PUT')

  <div>
    <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
  </div>

 <div>
  <x-input-label for="name-edit" :value="__('Name')" required />
  <x-inputs.text-input id="name-edit" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New api name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="url-edit" :value="__('URL')" required />
  <x-inputs.text-input id="url-edit" class="block mt-2 w-full" type="text" name="url" :value="old('url')" required
   placeholder="New api url" autocomplete="off" />
  <x-input-error id="input-url-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="username-edit" :value="__('Username')" />
  <x-inputs.text-input id="username-edit" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
   placeholder="New api username" autocomplete="off" />
  <x-input-error id="input-username-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="password-edit" :value="__('Password')" />
  <x-inputs.text-input id="password-edit" class="block mt-2 w-full" type="text" name="password" :value="old('password')"
   placeholder="New api password" autocomplete="off" />
  <x-input-error id="input-password-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="is_active-edit" :value="__('Active?')" required />
  <x-inputs.toggle-input id="is_active-edit" name="is_active" required />
  <x-input-error id="input-is_active-error"></x-input-error>
 </div>

 <hr class="mt-2">

    <!-- Update btn -->
    <x-primary-button class="w-full mt-4">
        {{ __('Update') }}
    </x-primary-button>

</form>
@endsection
