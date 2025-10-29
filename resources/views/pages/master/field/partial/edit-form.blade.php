@extends('pages.master.partial.modal-edit-layout')

@section('edit-title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Edit field</h1>
@endsection

@section('edit-content')
<form method="PUT" action="{{ url('/master/field/update-data') }}" id="edit-form" class="ajax-edit-form"
  data-datatable="#field-table" novalidate>
  @csrf
  @method('PUT')

  <div>
    <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
  </div>

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
  <x-input-label for="edit-pict" :value="__('Picture')" />
  <x-inputs.file-input id="edit-pict" name="pict">
   PNG, JPG, or JPEG
  </x-inputs.file-input>
 </div>

 <div class="mt-2">
  <x-input-label for="edit-select-category" :value="__('Category')" required />
  <select id="edit-select-category" placeholder="Select category..." autocomplete="off" name="category_id" class="my-2"
   required>
  </select>
 </div>

 <div class="mt-2">
  <x-input-label for="edit-select-venue" :value="__('Venue')" required />
  <select id="edit-select-venue" placeholder="Select venue..." autocomplete="off" name="venue_id" class="my-2" required>
  </select>
 </div>

 <hr class="mt-2">

    <!-- Update btn -->
    <x-primary-button class="w-full mt-4">
        {{ __('Update') }}
    </x-primary-button>

</form>
@endsection
