<x-guest-layout :pageTitle="'Login'" :title="'SIGN IN TO YOUR ACCOUNT'">

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-inputs.text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')"
                required autofocus placeholder="your.email@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-inputs.text-input id="password" class="block mt-2 w-full" type="password" name="password" required
                autocomplete="current-password" placeholder="xxxxxxxx" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-5">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-base-300 text-hot-shot shadow-sm focus:ring-none appearance-none"
                    name="remember">
                <span class="ms-2 text-xs md:text-sm text-after-midnight">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Actions button -->
        <div class="flex flex-col gap-4 mt-5">
            <!-- Login btn -->
            <x-primary-button class="w-full">
                {{ __('Log in') }}
            </x-primary-button>

            <!-- Divider -->
            <p class="md:text-md text-sm font-medium text-center my-2 text-after-midnight">or sign in with</p>

            <!-- Login via google btn -->
            <x-google-button class="w-full">
                <img src="{{ asset('assets/icons/google.svg') }}" alt="Google Icon" class="inline-block me-2 w-5 h-5">
                {{ __('Google') }}
            </x-google-button>
        </div>

        <!-- Actions links -->
        <div class="flex items-center justify-between w-full mt-6">
            <!-- Forgot password -->
            @if (Route::has('password.request'))
            <a class="text-xs md:text-sm text-after-midnight/90 hover:text-miami tracking-wide"
                href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
            @endif

            <!-- Register -->
            <a class="text-xs md:text-sm text-after-midnight/90 hover:text-miami tracking-wide"
                href="{{ route('register') }}">
                {{ __("Didn't have an account?") }}
            </a>
        </div>
    </form>
</x-guest-layout>