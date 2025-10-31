<div class="overflow-x-hidden" id="formStreaming">
 <form method="POST" action="{{ url('creator/new/add/stream') }}" class="ajax-form" novalidate>
  @csrf
  <div>
   <x-input-label for="session_code" :value="__('Code Access')" :required="true">
    <x-slot:extras>
     <p class="tracking-wide text-xs font-medium italic">Get the access code from cashier.</p>
    </x-slot:extras>
   </x-input-label>
   <x-inputs.text-input id="session_code" class="block mt-2 w-full" type="text" name="session_code"
    :value="old('session_code')" :required="true" placeholder="Input access code" />
   <x-input-error id="input-session_code-error"></x-input-error>
  </div>

  <!-- Submit btn -->
  <x-primary-button class="w-full mt-6" id="start_streaming">
   {{ __('Start Streaming') }}
  </x-primary-button>
 </form>
</div>