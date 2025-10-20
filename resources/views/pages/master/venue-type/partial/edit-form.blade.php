<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">

@section('content')
<form method="PUT" action="{{ url('/master/venue-type/update-data') }}" id="edit-form" class="ajax-edit-form" data-datatable="#venue-type-table"
 novalidate>
 @csrf
   @method('PUT')

 <div>
  <x-inputs.text-input id="id" class="block mt-2 w-full" type="hidden" name="id" />
 </div>

 <!-- Name -->
 <div>
  <x-input-label for="name" :value="__('Name')" />
  <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
   placeholder="New venue type name" autocomplete="off" />
  <x-input-error id="input-name-error"></x-input-error>
 </div>

 <div class="mt-2">
  <x-input-label for="description" :value="__('Description')" />
  <x-inputs.textarea-input id="description" name="description" class="block mt-2 w-full"
   placeholder="New venue type description" :value="$venueType->description ?? ''">
  </x-inputs.textarea-input>
  <x-input-error id="input-description-error"></x-input-error>
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

</div>
