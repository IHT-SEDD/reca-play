@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new role</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/role/add-data') }}" class="ajax-form" data-datatable="#role-table"
 novalidate>
 @csrf
 <!-- Name -->
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New role name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection