@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new qr code</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/qr_code/add-data') }}" class="ajax-form" data-datatable="#qr_code-table"
  novalidate>
  @csrf
  <div>
    <x-input-label for="name" :value="__('Name')" required />
    <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
      placeholder="New qr code name" autocomplete="off" />
    <x-input-error id="input-name-error"></x-input-error>
  </div>

  <div class="mt-2">
    <x-input-label for="description" :value="__('Description')" />
    <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
      placeholder="New qr code description" :value="$qr_code->description ?? ''">
    </x-inputs.textarea-input>
    <x-input-error id="input-description-error"></x-input-error>
  </div>

  <div class="mt-2">
    <x-input-label for="field_id" :value="__('Field')" />
    <select id="select-field" placeholder="Select field..." autocomplete="off" name="field_id" class="my-2">
    </select>
    <div class="flex items-center mb-4">
      <input id="disable-field-select" type="checkbox" value=""
        class="w-4 h-4 text-hot-shot bg-base-200 border-base-300 rounded-md focus:ring-hot-shot focus:ring-2">
      <label for="disable-field-select" class="ms-2 text-xs font-medium text-carbon">Not a field qr?</label>
    </div>
  </div>

  <div class="mt-2">
    <x-input-label for="venue_id" :value="__('Venue')" />
    <select id="select-venue" placeholder="Select venue..." autocomplete="off" name="venue_id" class="my-2">
    </select>
    <div class="flex items-center mb-4">
      <input id="disable-venue-select" type="checkbox" value=""
        class="w-4 h-4 text-hot-shot bg-base-200 border-base-300 rounded-md focus:ring-hot-shot focus:ring-2">
      <label for="disable-venue-select" class="ms-2 text-xs font-medium text-carbon">Not a venue qr?</label>
    </div>
  </div>

  <div class="mt-2">
    <x-input-label for="type" :value="__('QR Type')" />
    <div class="flex flex-col gap-2 justify-center items-start mt-3">
      <div class="flex">
        <div class="flex items-center h-5">
          <input id="type-radio" aria-describedby="type-radio-text" type="radio" value="qr_field" name="type"
            class="w-4 h-4 text-hot-shot bg-base-200 border-base-300 focus:ring-hot-shot focus:ring-2">
        </div>
        <div class="ms-2 text-sm">
          <label for="type-radio" class="font-medium text-after-midnight/85">QR Field</label>
          <p id="type-radio-text" class="text-xs font-normal text-carbon">QR that designed for each fields</p>
        </div>
      </div>
      <div class="flex">
        <div class="flex items-center h-5">
          <input id="type-radio" aria-describedby="type-radio-text" type="radio" value="qr_venue" name="type"
            class="w-4 h-4 text-hot-shot bg-base-200 border-base-300 focus:ring-hot-shot focus:ring-2">
        </div>
        <div class="ms-2 text-sm">
          <label for="type-radio" class="font-medium text-after-midnight/85">QR Venue</label>
          <p id="type-radio-text" class="text-xs font-normal text-carbon">QR that designed for each venues</p>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-2">
    <x-input-label for="is_active" :value="__('Active?')" required />
    <x-inputs.toggle-input id="is_active" name="is_active" required />
    <x-input-error id="input-is_active-error"></x-input-error>
  </div>

  <!-- Submit btn -->
  <x-primary-button class="w-full mt-6">
    {{ __('Submit') }}
  </x-primary-button>
</form>
@endsection
