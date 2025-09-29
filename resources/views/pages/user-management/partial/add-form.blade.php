@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new user</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/user-management/add-data') }}" class="ajax-form" data-datatable="#users-table"
 novalidate>
 @csrf
 <!-- Name -->
 <div>
  <x-input-label for="name" :value="__('Name')" required />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New user name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <!-- Username -->
 <div class="mt-2">
  <x-input-label for="username" :value="__('Username')" required />
  <x-inputs.text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
   required placeholder="New user username" autocomplete="off" />
  <x-input-error id="input-username-error"></x-input-error>
 </div>

 <!-- Email -->
 <div class="mt-2">
  <x-input-label for="email" :value="__('Email')" required />
  <x-inputs.text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required
   placeholder="New user email" autocomplete="off" />
  <x-input-error id="input-email-error"></x-input-error>
 </div>

 <!-- Password -->
 <div class="mt-2">
  <x-input-label for="password" :value="__('Password')" required />
  <x-inputs.text-input id="password" class="block mt-2 w-full" type="password" name="password" :value="old('password')"
   required placeholder="New user password" autocomplete="off" />
  <x-input-error id="input-password-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="role_id" :value="__('Role')" required />
  <select id="select-role" placeholder="Select role..." autocomplete="off" name="role_id" class="my-2" required>
  </select>
 </div>

 <div class="mt-2" id="select_venue_owner">
  <x-input-label for="venue_id" :value="__('Venue')" />
  <select id="select-venue" placeholder="Select venue..." autocomplete="off" name="venue_id" class="my-2">
  </select>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection