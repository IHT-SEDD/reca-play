@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new field</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/field/add-data') }}" class="ajax-form" data-datatable="#field-table"
 novalidate>
 @csrf
 <div>
  <x-input-label for="name" :value="__('Name')" required />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New field name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="initial" :value="__('Initial')" />
  <x-inputs.text-input id="initial" class="block mt-2 w-full" type="text" name="initial" :value="old('initial')"
   placeholder="New field initial" autocomplete="off" />
  <x-input-error id="input-initial-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="description" :value="__('Description')" />
  <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
   placeholder="New field description" :value="$field->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-description-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="category_id" :value="__('Category')" required />
  <select id="select-category" placeholder="Select category..." autocomplete="off" name="category_id" class="my-2"
   required>
  </select>
 </div>

 <div class="mt-2">
  <x-input-label for="venue_id" :value="__('Venue')" required />
  <select id="select-venue" placeholder="Select venue..." autocomplete="off" name="venue_id" class="my-2" required>
  </select>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection