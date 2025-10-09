<nav x-data="{ open: false }" class="bg-white dark:bg-thamar-black border-b border-base-200 dark:border-white/20 py-3">
    <!-- Primary Navigation Menu -->
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-fit items-center">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="/">
                    <img x-show="!darkMode" src="{{ asset('assets/img/logos/reca-black.png') }}" alt="Logo RECA"
                        class="w-8 h-8 md:w-11 md:h-11">

                    <img x-show="darkMode" src="{{ asset('assets/img/logos/reca-white.png') }}" alt="Logo RECA"
                        class="w-8 h-8 md:w-11 md:h-11">
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <x-nav-link :href="url('/')" :active="request()->routeIs('home.*')">
                    {{ __('Home') }}
                </x-nav-link>
                <x-nav-link :href="url('/venue')" :active="request()->routeIs('venue.*')">
                    {{ __('Venues') }}
                </x-nav-link>
                <x-nav-link :href="url('/event')" :active="request()->routeIs('event.*')">
                    {{ __('Events') }}
                </x-nav-link>
                <!-- Menu in auth mode only :begin -->
                @auth
                <x-nav-link :href="url('/my-recording')" :active="request()->routeIs('recording.*')">
                    {{ __('Recordings') }}
                </x-nav-link>
                @endauth
                <!-- Menu in auth mode only :end -->
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Dark Mode toggle -->
                {{-- <button @click="darkMode = !darkMode"
                    class="p-1.5 rounded-full inline-flex gap-2 items-center justify-center bg-transparent border border-base-200 dark:border-base-200/40 me-4">
                    <!-- Sun (Light mode) -->
                    <div :class="darkMode ? 'bg-transparent text-white' : 'bg-base-200 text-hot-shot'"
                        class="p-2 rounded-full transition">
                        <i data-lucide="sun" class="w-4 h-auto"></i>
                    </div>
                    <!-- Moon (Dark mode) -->
                    <div :class="darkMode ? 'bg-white text-eerie-black' : 'bg-transparent text-eerie-black'"
                        class="p-2 rounded-full transition">
                        <i data-lucide="moon" class="w-4 h-auto"></i>
                    </div>
                </button> --}}

                <!-- User dropdown -->
                <x-dropdown.dropdown-flowbite :trigger="Auth::user()->name ?? 'Welcome, Guest'" iconTrigger="user">
                    <li class="w-full">
                        @auth
                        <a href="{{ url('/profile/edit') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl dark:hover:bg-orochimaru hover:text-hot-shot dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="user-round-pen" class="w-4 h-auto me-2"></i>
                            Profile
                        </a>
                        <form method="POST" action="{{ url('/logout') }}" class="w-full">
                            @csrf
                            <a href="{{ url('/logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="rounded-lg px-4 py-2 hover:bg-white-owl dark:hover:bg-orochimaru hover:text-hot-shot dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                                <i data-lucide="log-out" class="w-4 h-auto me-2"></i>
                                Logout
                            </a>
                        </form>
                        @endauth

                        @guest
                        <a href="{{ url('/register') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl dark:hover:bg-orochimaru hover:text-hot-shot dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="user-round-plus" class="w-4 h-auto me-2"></i>
                            Register
                        </a>
                        <a href="{{ url('/login') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl dark:hover:bg-orochimaru hover:text-hot-shot dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="log-in" class="w-4 h-auto me-2"></i>
                            Login
                        </a>
                        @endguest
                    </li>
                </x-dropdown.dropdown-flowbite>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                {{-- <button @click="darkMode = !darkMode"
                    class="p-1 rounded-full inline-flex gap-2 items-center justify-center bg-transparent border border-base-200 dark:border-base-200/40 me-4">
                    <!-- Sun (Light mode) -->
                    <div :class="darkMode ? 'bg-transparent text-white' : 'bg-base-200 text-hot-shot'"
                        class="p-1 rounded-full transition">
                        <i data-lucide="sun" class="w-3 h-auto"></i>
                    </div>
                    <!-- Moon (Dark mode) -->
                    <div :class="darkMode ? 'bg-white text-eerie-black' : 'bg-transparent text-eerie-black'"
                        class="p-1 rounded-full transition">
                        <i data-lucide="moon" class="w-3 h-auto"></i>
                    </div>
                </button> --}}

                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-after-midnight dark:text-white hover:text-hot-shot dark:hover:text-hot-shot transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <!-- Responsive Navigation Menu -->
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="url('/')" :active="request()->routeIs('home.*')">
                {{ __('Home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="url('/venue')" :active="request()->routeIs('venue.*')">
                {{ __('Venues') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="url('/event')" :active="request()->routeIs('event.*')">
                {{ __('Events') }}
            </x-responsive-nav-link>
            <!-- Menu in auth mode only :begin -->
            @auth
            <x-responsive-nav-link :href="url('/my-recording')" :active="request()->routeIs('recording.*')">
                {{ __('Recordings') }}
            </x-responsive-nav-link>
            @endauth
            <!-- Menu in auth mode only :end -->
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <!-- Text in auth mode only :begin -->
                @auth
                <div class="font-medium text-md text-after-midnight dark:text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-after-midnight/80 dark:text-white-owl">{{ Auth::user()->email }}
                </div>
                @endauth
                <!-- Text in auth mode only :end -->

                <!-- Text in guest mode only :begin -->
                @guest
                <div class="font-medium text-md text-after-midnight dark:text-white">Welcome, Guest!</div>
                <div class="font-medium text-sm text-after-midnight/80 dark:text-white-owl">Please sign in or sign up to
                    continue.</div>
                @endguest
                <!-- Text in guest mode only :end -->
            </div>

            <div class="mt-3 space-y-1">
                <!-- Options in auth mode only :begin -->
                @auth
                <x-responsive-nav-link :href="url('/profile/edit')" :active="request()->routeIs('profile.*')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="url('/logout')" onclick="event.preventDefault();
                                            this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
                @endauth
                <!-- Options in auth mode only :end -->

                <!-- Options in guest mode only :begin -->
                @guest
                <x-responsive-nav-link :href="url('/login')">
                    {{ __('Login') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="url('/register')">
                    {{ __('Register') }}
                </x-responsive-nav-link>
                @endguest
                <!-- Options in guest mode only :end -->
            </div>
        </div>
    </div>
</nav>