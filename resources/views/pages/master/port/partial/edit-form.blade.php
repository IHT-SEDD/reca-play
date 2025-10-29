@extends('pages.master.partial.modal-edit-layout')

@section('edit-title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Edit port</h1>
@endsection

@section('edit-content')
<form method="PUT" action="{{ url('/master/port/update-data') }}" id="edit-form" class="ajax-edit-form"
  data-datatable="#port-table" novalidate>
  @csrf
  @method('PUT')

  <div>
    <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
  </div>

 <!-- Name -->
  <div>
  <x-input-label for="name" :value="__('Name')" required />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New port name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="port_number" :value="__('Port Number')" required />
  <x-inputs.text-input id="port_number" class="block mt-2 w-full" type="text" name="port_number"
   :value="old('port_number')" required placeholder="New port number" autocomplete="off" />
  <x-input-error id="input-port_number-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="is_active" :value="__('Active')" required />
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
