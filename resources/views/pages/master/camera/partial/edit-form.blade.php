@extends('pages.master.partial.modal-edit-layout')

@section('edit-title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Edit Camera</h1>
@endsection

@section('edit-content')
<form method="PUT" action="{{ url('/master/camera/update-data') }}" id="edit-form" class="ajax-edit-form"
  data-datatable="#camera-table" novalidate>
  @csrf
  @method('PUT')

  <div>
    <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
  </div>

  <div>
  <x-input-label for="name" :value="__('Name')" required />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New camera name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="initial" :value="__('Initial')" />
  <x-inputs.text-input id="initial" class="block mt-2 w-full" type="text" name="initial" :value="old('initial')"
   placeholder="New camera initial" autocomplete="off" />
  <x-input-error id="input-initial-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="brand" :value="__('Brand')" />
  <x-inputs.text-input id="brand" class="block mt-2 w-full" type="text" name="brand" :value="old('brand')"
   placeholder="New camera brand" autocomplete="off" />
  <x-input-error id="input-brand-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="type" :value="__('Type')" />
  <x-inputs.text-input id="type" class="block mt-2 w-full" type="text" name="type" :value="old('type')"
   placeholder="New camera type" autocomplete="off" />
  <x-input-error id="input-type-error"></x-input-error>
 </div>

 <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mt-2">
  <div class="w-full">
   <x-input-label for="ip_address" :value="__('IP Address')" />
   <x-inputs.text-input id="ip_address" class="block mt-2 w-full" type="text" name="ip_address"
    :value="old('ip_address')" placeholder="New camera ip address" autocomplete="off" />
   <x-input-error id="input-ip_address-error"></x-input-error>
  </div>

  <div class="w-full">
   <x-input-label for="channel" :value="__('Channel')" />
   <x-inputs.text-input id="channel" class="block mt-2 w-full" type="text" name="channel" :value="old('channel')"
    placeholder="New camera channel" autocomplete="off" />
   <x-input-error id="input-channel-error"></x-input-error>
  </div>
 </div>

 <div class="mt-2">
  <x-input-label for="description" :value="__('Description')" />
  <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
   placeholder="New camera description" :value="$camera->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-description-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="select-field-edit" :value="__('Field')" required />
  <select id="select-field-edit" placeholder="Select field..." autocomplete="off" name="field_id" class="my-2">
  </select>
 </div>

 <div class="mt-2">
  <x-input-label for="select-nvr-edit" :value="__('NVR')" required />
  <select id="select-nvr-edit" placeholder="Select nvr..." autocomplete="off" name="nvr_id" class="my-2">
  </select>
 </div>

 <div class="mt-2">
  <x-input-label for="is_active" :value="__('Active?')" required />
  <x-inputs.toggle-input id="is_active" name="is_active" required />
  <x-input-error id="input-is_active-error"></x-input-error>
 </div>

 <hr class="mt-2">

    <!-- Update btn -->
    <x-primary-button class="w-full mt-4">
        {{ __('Update') }}
    </x-primary-button>

</form>
@endsection
