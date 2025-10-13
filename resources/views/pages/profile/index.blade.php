<x-app-layout :pageTitle="'Home'">
 <div class="py-8">
  <div class="w-full mx-auto md:px-8 px-5 flex justify-center items-center">
   <div class="flex flex-col w-full justify-between items-start p-1.5 gap-3">
    <!-- Update general information :begin -->
    <div class="flex-1 p-4 rounded-xl shadow-sm bg-white">
     <div class="flex items-center justify-between w-full mb-4">
      <h1 class="text-after-midnight dark:text-white md:text-xl text-md font-semibold tracking-wide">
       General Information
      </h1>
      <button id="edit_general_information_btn"
       class="rounded-full bg-hot-shot/20 hover:bg-hot-shot p-2 text-hot-shot hover:text-white tooltip"
       data-tip="Edit General Information">
       <i data-lucide="user-round-pen" class="w-5 h-auto"></i>
      </button>
     </div>

     <form method="POST" action="{{ route('login') }}">
      @csrf
      <!-- Name -->
      <div>
       <x-input-label for="name" :value="__('Name')" />
       <x-inputs.text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required />
       <x-input-error id="input-name-error">
        {{ $errors->first('name') }}
       </x-input-error>
      </div>

      <!-- Username -->
      <div class="mt-2">
       <x-input-label for="username" :value="__('Username')" />
       <x-inputs.text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
        required />
       <x-input-error id="input-username-error">
        {{ $errors->first('username') }}
       </x-input-error>
      </div>

      <!-- Email Address -->
      <div class="mt-2">
       <x-input-label for="email" :value="__('Email')" />
       <x-inputs.text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')"
        required />
       <x-input-error id="input-email-error">
        {{ $errors->first('email') }}
       </x-input-error>
      </div>

      <!-- Contact Phone -->
      <div class="mt-2">
       <x-input-label for="contact_phone" :value="__('Contact')" />
       <x-inputs.text-input id="contact_phone" class="block mt-2 w-full" type="text" name="contact_phone"
        :value="old('contact_phone')" required />
       <x-input-error id="input-contact_phone-error">
        {{ $errors->first('contact_phone') }}
       </x-input-error>
      </div>

      <!-- Update btn -->
      <x-primary-button class="w-full max-w-xs mt-4" id="update_general_information_btn">
       {{ __('Update') }}
      </x-primary-button>
     </form>
    </div>
    <!-- Update general information :end -->

    <!-- Update account information :begin -->
    <div class="flex-1 p-4 rounded-xl shadow-sm bg-white">
     <div class="flex items-center justify-between w-full mb-4">
      <h1 class="text-after-midnight dark:text-white md:text-xl text-md font-semibold tracking-wide">
       Account Information
      </h1>
      <button id="edit_account_information_btn"
       class="rounded-full bg-hot-shot/20 hover:bg-hot-shot p-2 text-hot-shot hover:text-white tooltip"
       data-tip="Edit Account Information">
       <i data-lucide="user-round-cog" class="w-5 h-auto"></i>
      </button>
     </div>

     <form method="POST" action="{{ route('login') }}">
      @csrf
      <!-- Old password -->
      <div>
       <x-input-label for="old_password" :value="__('Old Password')" />
       <x-inputs.text-input id="old_password" class="block mt-2 w-full" type="password" name="old_password"
        :value="old('old_password')" required />
       <x-input-error id="input-old_password-error">
        {{ $errors->first('old_password') }}
       </x-input-error>
      </div>

      <!-- New password -->
      <div class="mt-2">
       <x-input-label for="password" :value="__('New Password')" />
       <x-inputs.text-input id="password" class="block mt-2 w-full" type="password" name="password"
        :value="old('password')" required />
       <x-input-error id="input-password-error">
        {{ $errors->first('password') }}
       </x-input-error>
      </div>

      <!-- New password confirmation -->
      <div class="mt-2">
       <x-input-label for="new_password_confirmation" :value="__('New Password Confirmation')" />
       <x-inputs.text-input id="new_password_confirmation" class="block mt-2 w-full" type="password"
        name="new_password_confirmation" :value="old('new_password_confirmation')" required />
       <x-input-error id="input-new_password_confirmation-error">
        {{ $errors->first('new_password_confirmation') }}
       </x-input-error>
      </div>

      <!-- Update btn -->
      <x-primary-button class="w-full max-w-xs mt-4" id="update_account_information_btn">
       {{ __('Update') }}
      </x-primary-button>
     </form>
    </div>
    <!-- Update account information :end -->
   </div>
  </div>
 </div>

 @push('styles')
 @endpush

 @push('scripts')
 @endpush
</x-app-layout>