@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new role</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/role/add-data') }}">
 @csrf
 <!-- Name -->
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required autofocus
   placeholder="New role name" />
  <x-input-error :messages="$errors->get('name')" class="mt-2" />
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection