@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new port</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/port/add-data') }}" class="ajax-form" data-datatable="#port-table"
 novalidate>
 @csrf
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New port name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="port_number" :value="__('Port Number')" />
  <x-inputs.text-input id="port_number" class="block mt-2 w-full" type="text" name="port_number"
   :value="old('port_number')" required placeholder="New port number" autocomplete="off" />
  <x-input-error id="input-port_number-error"></x-input-error>
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