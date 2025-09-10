@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new camera</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/camera/add-data') }}" class="ajax-form" data-datatable="#camera-table"
 novalidate>
 @csrf
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New camera name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="initial" :value="__('Initial')" />
  <x-inputs.text-input id="initial" class="block mt-2 w-full" type="text" name="initial" :value="old('initial')"
   required placeholder="New camera initial" autocomplete="off" />
  <x-input-error id="input-initial-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="brand" :value="__('Brand')" />
  <x-inputs.text-input id="brand" class="block mt-2 w-full" type="text" name="brand" :value="old('brand')" required
   placeholder="New camera brand" autocomplete="off" />
  <x-input-error id="input-brand-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="type" :value="__('Type')" />
  <x-inputs.text-input id="type" class="block mt-2 w-full" type="text" name="type" :value="old('type')" required
   placeholder="New camera type" autocomplete="off" />
  <x-input-error id="input-type-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="ip_address" :value="__('IP Address')" />
  <x-inputs.text-input id="ip_address" class="block mt-2 w-full" type="text" name="ip_address"
   :value="old('ip_address')" required placeholder="New camera ip address" autocomplete="off" />
  <x-input-error id="input-ip_address-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="channel" :value="__('Channel')" />
  <x-inputs.text-input id="channel" class="block mt-2 w-full" type="text" name="channel" :value="old('channel')"
   required placeholder="New camera channel" autocomplete="off" />
  <x-input-error id="input-channel-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="description" :value="__('Description')" />
  <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
   placeholder="New camera description" :value="$camera->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-description-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="field_id" :value="__('Field')" />
  <select id="select-field" placeholder="Select field..." autocomplete="off" name="field_id" class="my-2">
  </select>
 </div>

 <div class="mt-2">
  <x-input-label for="is_active" :value="__('Active?')" />
  <x-inputs.toggle-input id="is_active" name="is_active" />
  <x-input-error id="input-is_active-error"></x-input-error>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection