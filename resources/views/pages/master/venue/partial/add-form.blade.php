@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new venue</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/venue/add-data') }}" class="ajax-form" data-datatable="#venue-table"
 novalidate>
 @csrf
 <!-- Name -->
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New venue name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="description" :value="__('Description')" />
  <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
   placeholder="New venue description" :value="$venue->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-description-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="address" :value="__('Address')" />
  <x-inputs.textarea-input id="address" name="address" class="block mt-2 w-full" placeholder="New venue address"
   :value="$venue->address ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-address-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="venue_type_id" :value="__('Venue Type')" />
  <select id="select-venue-type" placeholder="Select venue type..." autocomplete="off" name="venue_type_id" class="my-2">
  </select>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection