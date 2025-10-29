@extends('pages.master.partial.modal-edit-layout')

@section('edit-title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Edit role</h1>
@endsection

@section('edit-content')
<form method="PUT" action="{{ url('/master/role/update-data') }}" id="edit-form" class="ajax-edit-form"
  data-datatable="#role-table" novalidate>
  @csrf
  @method('PUT')

  <div>
    <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
  </div>

 <!-- Name -->
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New role name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

  <!-- Guard Name -->
 <div class="mt-2">
  <x-input-label for="guard_name" :value="__('Guard Name')" />
  <x-inputs.text-input id="guard_name" class="block mt-2 w-full" type="text" name="guard_name" :value="old('name')" required
   placeholder="New role name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>


 <hr class="mt-2">

     <!-- Update btn -->
    <x-primary-button class="w-full mt-4">
        {{ __('Update') }}
    </x-primary-button>

</form>
@endsection
