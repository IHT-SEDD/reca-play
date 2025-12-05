<x-guest-layout :pageTitle="'Forgot Password'" :title="'RESET YOUR PASSWORD'">
    <div class="mb-4 text-sm text-carbon">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password
        reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-inputs.text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')"
                required autofocus />
            <x-input-error-email :messages="$errors->first('email')" />
        </div>

        <x-primary-button class="w-full mt-5">
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </form>
</x-guest-layout>