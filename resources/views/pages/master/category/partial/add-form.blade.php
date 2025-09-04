@extends('pages.master.partial.add-form-layout')

@section('title')
<h1 class=" md:text-xl text-md font-bold text-hot-shot w-full">Add new category</h1>
@endsection

@section('content')
<form method="POST" action="{{ url('/master/category/add-data') }}" class="ajax-form" data-datatable="#category-table"
 novalidate>
 @csrf
 <!-- Name -->
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New category name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="description" :value="__('Description')" />
  <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
   placeholder="New category description" :value="$category->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-category-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="is_active" :value="__('Active?')" />
  <x-inputs.toggle-input id="is_active" name="is_active" />
  <x-input-error id="input-is_active-error"></x-input-error>
 </div>

 <!-- Submit btn -->
 <x-primary-button class="w-full mt-6">
  {{ __('Submit') }}
 </x-primary-button>
</form>
@endsection