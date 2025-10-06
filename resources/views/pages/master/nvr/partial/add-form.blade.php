@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new nvr</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/nvr/add-data') }}" class="ajax-form" data-datatable="#nvr-table" novalidate>
 @csrf
 <div>
  <x-input-label for="name" :value="__('Name')" required />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New nvr name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="initial" :value="__('Initial')" />
  <x-inputs.text-input id="initial" class="block mt-2 w-full" type="text" name="initial" :value="old('initial')"
   placeholder="New nvr initial" autocomplete="off" />
  <x-input-error id="input-initial-error"></x-input-error>
 </div>

 <div class="flex justify-between items-center gap-2 mt-2">
  <div class="w-full">
   <x-input-label for="brand" :value="__('Brand')" />
   <x-inputs.text-input id="brand" class="block mt-2 w-full" type="text" name="brand" :value="old('brand')"
    placeholder="New nvr brand" autocomplete="off" />
   <x-input-error id="input-brand-error"></x-input-error>
  </div>

  <div class="w-full">
   <x-input-label for="type" :value="__('Type')" />
   <x-inputs.text-input id="type" class="block mt-2 w-full" type="text" name="type" :value="old('type')"
    placeholder="New nvr type" autocomplete="off" />
   <x-input-error id="input-type-error"></x-input-error>
  </div>
 </div>

 <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mt-2">
  <div class="w-full">
   <x-input-label for="ip_address" :value="__('IP Address')" required />
   <x-inputs.text-input id="ip_address" class="block mt-2 w-full" type="text" name="ip_address"
    :value="old('ip_address')" placeholder="New nvr ip address" autocomplete="off" required />
   <x-input-error id="input-ip_address-error"></x-input-error>
  </div>

  <div class="md:w-1/2 w-full">
   <x-input-label for="port_id" :value="__('Port')" />
   <select id="select-port" placeholder="Select port..." autocomplete="off" name="port_id" class="my-1.5">
   </select>
  </div>
 </div>

 <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mt-2">
  <div class="w-full">
   <x-input-label for="username" :value="__('Username')" required />
   <x-inputs.text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
    required placeholder="New nvr username" autocomplete="off" required />
   <x-input-error id="input-username-error"></x-input-error>
  </div>

  <div class="w-full">
   <x-input-label for="password" :value="__('Password')" required />
   <x-inputs.text-input id="password" class="block mt-2 w-full" type="text" name="password" :value="old('password')"
    required placeholder="New nvr password" autocomplete="off" required />
   <x-input-error id="input-password-error"></x-input-error>
  </div>
 </div>

 <div class="mt-2">
  <x-input-label for="auth_type" :value="__('Auth Type')" required />
  <x-inputs.text-input id="auth_type" class="block mt-2 w-full" type="text" name="auth_type" :value="old('auth_type')"
   required placeholder="New nvr auth type" autocomplete="off" required />
  <x-input-error id="input-auth_type-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="description" :value="__('Description')" />
  <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
   placeholder="New nvr description" :value="$nvr->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-description-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="venue_id" :value="__('Venue')" required />
  <select id="select-venue" placeholder="Select venue..." autocomplete="off" name="venue_id" class="my-1.5" required>
  </select>
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