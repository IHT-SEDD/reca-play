@extends('pages.master.partial.modal-edit-layout')

@section('edit-title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Edit venue</h1>
@endsection

@section('edit-content')
<form method="PUT" action="{{ url('/master/venue/update-data') }}" id="edit-form" class="ajax-edit-form"
    data-datatable="#venue-table" novalidate>
    @csrf
    @method('PUT')

    <div>
        <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
    </div>

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
        <x-input-label for="edit-logo" :value="__('Logo')" />
        <x-inputs.file-input id="edit-logo" name="logo">
            PNG, JPG, or JPEG
        </x-inputs.file-input>
    </div>

    <div class="mt-2">
        <x-input-label for="edit-venue-type" :value="__('Venue Type')" />
        <select id="edit-venue-type" placeholder="Select venue type..." autocomplete="off" name="venue_type_id"
            class="my-2">
        </select>
    </div>

    <!-- Update btn -->
    <x-primary-button class="w-full mt-6">
        {{ __('Update') }}
    </x-primary-button>
</form>
@endsection