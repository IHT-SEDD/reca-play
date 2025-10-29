@extends('pages.master.partial.modal-edit-layout')

@section('edit-title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Edit NVR</h1>
@endsection

@section('edit-content')
<form method="PUT" action="{{ url('/master/nvr/update-data') }}" id="edit-form" class="ajax-edit-form"
  data-datatable="#nvr-table" novalidate>
  @csrf
  @method('PUT')

  <div>
    <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
  </div>

 <div>
  <x-input-label for="name-edit" :value="__('Name')" required />
  <x-inputs.text-input id="name-edit" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New nvr name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="initial-edit" :value="__('Initial')" />
  <x-inputs.text-input id="initial-edit" class="block mt-2 w-full" type="text" name="initial" :value="old('initial')"
   placeholder="New nvr initial" autocomplete="off" />
  <x-input-error id="input-initial-error"></x-input-error>
 </div>

 <div class="flex justify-between items-center gap-2 mt-2">
  <div class="w-full">
   <x-input-label for="brand-edit" :value="__('Brand')" />
   <x-inputs.text-input id="brand-edit" class="block mt-2 w-full" type="text" name="brand" :value="old('brand')"
    placeholder="New nvr brand" autocomplete="off" />
   <x-input-error id="input-brand-error"></x-input-error>
  </div>

  <div class="w-full">
   <x-input-label for="type-edit" :value="__('Type')" />
   <x-inputs.text-input id="type-edit" class="block mt-2 w-full" type="text" name="type" :value="old('type')"
    placeholder="New nvr type" autocomplete="off" />
   <x-input-error id="input-type-error"></x-input-error>
  </div>
 </div>

 <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mt-2">
  <div class="w-full">
   <x-input-label for="ip_address_edit" :value="__('IP Address')" required />
   <x-inputs.text-input id="ip_address_edit" class="block mt-2 w-full" type="text" name="ip_address"
    :value="old('ip_address')" placeholder="New nvr ip address" autocomplete="off" required />
   <x-input-error id="input-ip_address-error"></x-input-error>
  </div>

  <div class="md:w-1/2 w-full">
   <x-input-label for="select-port-edit" :value="__('Port')" />
   <select id="select-port-edit" placeholder="Select port..." autocomplete="off" name="port_id" class="my-1.5">
   </select>
  </div>
 </div>

 <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mt-2">
  <div class="w-full">
   <x-input-label for="username-edit" :value="__('Username')" required />
   <x-inputs.text-input id="username-edit" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
    required placeholder="New nvr username" autocomplete="off" required />
   <x-input-error id="input-username-error"></x-input-error>
  </div>

  <div class="w-full">
   <x-input-label for="password-edit" :value="__('Password')" required />
   <x-inputs.text-input id="password-edit" class="block mt-2 w-full" type="text" name="password" :value="old('password')"
    required placeholder="New nvr password" autocomplete="off" required />
   <x-input-error id="input-password-error"></x-input-error>
  </div>
 </div>

 <div class="mt-2">
  <x-input-label for="auth_type_edit" :value="__('Auth Type')" required />
  <x-inputs.text-input id="auth_type_edit" class="block mt-2 w-full" type="text" name="auth_type" :value="old('auth_type')"
   required placeholder="New nvr auth type" autocomplete="off" required />
  <x-input-error id="input-auth_type-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="description-edit" :value="__('Description')" />
  <x-inputs.textarea-input id="description-edit" name="description" class="block mt-2 w-full"
   placeholder="New nvr description" :value="$nvr->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-description-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="select-venue-edit" :value="__('Venue')" required />
  <select id="select-venue-edit" placeholder="Select venue..." autocomplete="off" name="venue_id" class="my-1.5" required>
  </select>
 </div>

 <div class="mt-2">
  <x-input-label for="is_active_edit" :value="__('Active?')" required />
  <x-inputs.toggle-input id="is_active_edit" name="is_active" required />
  <x-input-error id="input-is_active-error"></x-input-error>
 </div>

 <hr class="mt-2">

     <!-- Update btn -->
    <x-primary-button class="w-full mt-4">
        {{ __('Update') }}
    </x-primary-button>

</form>
@endsection
