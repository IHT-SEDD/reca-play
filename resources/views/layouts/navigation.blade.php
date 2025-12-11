<nav x-data="{ open: false }" class="main-nav-default py-4 mb-1 w-full" id="mainNav">
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
                        <a href="{{ url('/my-profile') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl dark:hover:bg-orochimaru hover:text-hot-shot dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="user-cog" class="w-4 h-auto me-2"></i>
                            My Profile
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
                <x-responsive-nav-link :href="url('/my-profile')">
                    {{ __('My Profile') }}
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

<!-- Super admin menu -->
@if (Auth::user() && Auth::user()->isSuperAdmin())
<nav x-data="{ open: false }" class="superadmin-nav-default py-2">
    <!-- Primary Navigation Menu -->
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-end h-fit items-center">
            <!-- Navigation Links -->
            <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex">
                <!-- Masters -->
                <x-dropdown.dropdown-flowbite trigger="Masters">
                    <li class="w-full">
                        <a href="{{ url('master/venue') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            Venues
                        </a>
                        <a href="{{ url('master/venue-type') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            Venue Types
                        </a>
                        <a href="{{ url('master/field') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            Fields
                        </a>
                        <a href="{{ url('master/role') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            Roles
                        </a>
                        <a href="{{ url('master/category') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            Categories
                        </a>
                        <a href="{{ url('master/camera') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            Cameras
                        </a>
                        <a href="{{ url('master/nvr') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            NVR's
                        </a>
                        <a href="{{ url('master/qr_code') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            QR Codes
                        </a>
                        <a href="{{ url('master/port') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            Port
                        </a>
                        <a href="{{ url('master/api') }}"
                            class="rounded-lg px-4 py-2 hover:bg-white-owl hover:text-hot-shot dark:hover:bg-orochimaru dark:hover:text-eerie-black inline-flex justify-start items-center w-full">
                            <i data-lucide="dot" class="w-4 h-auto me-2"></i>
                            API
                        </a>
                    </li>
                </x-dropdown.dropdown-flowbite>

                <!-- User Management -->
                <x-nav-link class="text-sm" :href="url('/user-management')"
                    :active="request()->routeIs('user-management.*')">
                    {{ __('User Management') }}
                </x-nav-link>

                <!-- Get Video -->
                <x-nav-link class="text-sm" :href="url('/get-video')" :active="request()->routeIs('get-video.*')">
                    {{ __('Get Video') }}
                </x-nav-link>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
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
            <!-- Masters -->
            <div x-data="{ open: false }" class="sm:hidden">
                <!-- Toggle button -->
                <button @click="open = !open"
                    class="flex items-center justify-between w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start tracking-wide capitalize text-after-midnight dark:text-white font-medium hover:text-hot-shot dark:hover:text-hot-shot hover:border-hot-shot transition duration-150 ease-in-out text-sm">
                    Masters
                    <!-- Chevron -->
                    <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
                        class="w-[15px] h-[15px] transition-transform duration-300"></i>
                </button>

                <!-- Dropdown menu -->
                <div x-show="open" x-transition x-cloak class="mt-2 space-y-1">
                    <x-responsive-nav-link class="text-xs" :href="url('/master/venue')"
                        :active="request()->routeIs('master.venue.*')">
                        {{ __('Venues') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/venue-type')"
                        :active="request()->routeIs('master.venue-type.*')">
                        {{ __('Venue Types') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/field')"
                        :active="request()->routeIs('master.field.*')">
                        {{ __('Fields') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/role')"
                        :active="request()->routeIs('master.role.*')">
                        {{ __('Roles') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/category')"
                        :active="request()->routeIs('master.category.*')">
                        {{ __('Categories') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/camera')"
                        :active="request()->routeIs('master.camera.*')">
                        {{ __('Cameras') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/nvr')"
                        :active="request()->routeIs('master.nvr.*')">
                        {{ __("NVR's") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/qr_code')"
                        :active="request()->routeIs('master.qr_code.*')">
                        {{ __("QR Codes") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/port')"
                        :active="request()->routeIs('master.port.*')">
                        {{ __("Ports") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link class="text-xs" :href="url('/master/api')"
                        :active="request()->routeIs('master.api.*')">
                        {{ __("API's") }}
                    </x-responsive-nav-link>
                </div>
            </div>
            <!-- User Management -->
            <x-responsive-nav-link class="text-sm" :href="url('/user-management')"
                :active="request()->routeIs('user-management.*')">
                {{ __('User Management') }}
            </x-responsive-nav-link>
        </div>
    </div>
</nav>
@endif

<!-- Owner menu -->
@if (Auth::user() && Auth::user()->isVenueManagement())
<nav x-data="{ open: false }" class="venue-nav-default py-2">
    <!-- Primary Navigation Menu -->
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-end h-fit items-center">
            <!-- Navigation Links -->
            <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex">
                <!-- User Management -->
                <x-nav-link class="text-sm" :href="url('/venue-management')"
                    :active="request()->routeIs('venue-management.*')">
                    {{ __('Venue Management') }}
                </x-nav-link>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
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
            <!-- User Management -->
            <x-responsive-nav-link class="text-sm" :href="url('/venue-management')"
                :active="request()->routeIs('venue-management.*')">
                {{ __('Venue Management') }}
            </x-responsive-nav-link>
        </div>
    </div>
</nav>
@endif