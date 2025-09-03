<x-guest-layout :pageTitle="'Register'" :title="'SIGN UP YOUR ACCOUNT'">

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-2 w-full" type="text" name="name" :value="old('name')" required
                autofocus placeholder="your full name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-2 w-full" type="text" name="username" :value="old('username')"
                required autofocus placeholder="your username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required
                placeholder="your.email@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-2 w-full" type="password" name="password" required
                autocomplete="new-password" placeholder="xxxxxxxx" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <x-indicator-password id="strengthMeter"></x-indicator-password>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-2 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" placeholder="xxxxxxxx" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Actions button -->
        <div class="flex flex-col items-center gap-4 mt-6">
            <!-- Register btn -->
            <x-primary-button class="w-full">
                {{ __('Register') }}
            </x-primary-button>

            <!-- Divider -->
            <p class="md:text-md text-xs font-medium text-center my-2 text-after-midnight">or sign in with</p>

            <!-- Login via google btn -->
            <x-google-button class="w-full">
                <img src="{{ asset('assets/icons/google.svg') }}" alt="Google Icon" class="inline-block me-2 w-5 h-5">
                {{ __('Google') }}
            </x-google-button>

            <!-- Login -->
            <a class="text-xs md:text-sm text-after-midnight/90 hover:text-miami tracking-wide"
                href="{{ route('login') }}">
                {{ __("Already have an account?") }}
            </a>
        </div>
    </form>
</x-guest-layout>