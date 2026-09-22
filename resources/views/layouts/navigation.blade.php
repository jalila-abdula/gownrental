<nav
    x-data="{ open: false }"
    class="bg-white border-b border-gray-100"
>
    @php
        $dashboardRoute = match (auth()->user()->role) {
            'owner' => 'owner.dashboard',
            'employee' => 'employee.dashboard',
            'customer' => 'customer.dashboard',
            default => 'login',
        };
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            {{-- LEFT SIDE --}}
            <div class="flex">

                {{-- LOGO --}}
                <div class="shrink-0 flex items-center">

                    <a href="{{ route($dashboardRoute) }}">

                        <x-application-logo
                            class="block h-9 w-auto fill-current text-gray-800"
                        />

                    </a>

                </div>

                {{-- DESKTOP NAVIGATION --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- DASHBOARD --}}
                    <x-nav-link
                        :href="route($dashboardRoute)"
                        :active="request()->routeIs(
                            'owner.dashboard',
                            'employee.dashboard',
                            'customer.dashboard'
                        )"
                    >
                        {{ __('Dashboard') }}
                    </x-nav-link>


                    {{-- OWNER NAVIGATION --}}
                    @if(auth()->user()->role === 'owner')

                        {{-- CATEGORIES --}}
                        <x-nav-link
                            :href="route('owner.categories.index')"
                            :active="request()->routeIs('owner.categories.*')"
                        >
                            {{ __('Categories') }}
                        </x-nav-link>


                        {{-- GOWNS --}}
                        <x-nav-link
                            :href="route('owner.gowns.index')"
                            :active="request()->routeIs('owner.gowns.*')"
                        >
                            {{ __('Gowns') }}
                        </x-nav-link>


                        {{-- ACCESSORIES --}}
                        <x-nav-link
                            :href="route('owner.accessories.index')"
                            :active="request()->routeIs('owner.accessories.*')"
                        >
                            {{ __('Accessories') }}
                        </x-nav-link>

                    @endif


                    {{-- EMPLOYEE NAVIGATION --}}
                    @if(auth()->user()->role === 'employee')

                        <x-nav-link
                            :href="route('employee.dashboard')"
                            :active="request()->routeIs('employee.dashboard')"
                        >
                            {{ __('Employee Dashboard') }}
                        </x-nav-link>

                    @endif


                    {{-- CUSTOMER NAVIGATION --}}
                    @if(auth()->user()->role === 'customer')

                        <x-nav-link
                            :href="route('customer.dashboard')"
                            :active="request()->routeIs('customer.dashboard')"
                        >
                            {{ __('Customer Dashboard') }}
                        </x-nav-link>

                    @endif

                </div>

            </div>


            {{-- RIGHT SIDE --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <x-dropdown
                    align="right"
                    width="48"
                >

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                        >

                            <div>
                                {{ Auth::user()->name }}
                            </div>

                            <div class="ms-1">

                                <svg
                                    class="fill-current h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                >

                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />

                                </svg>

                            </div>

                        </button>

                    </x-slot>


                    <x-slot name="content">

                        {{-- LOGOUT --}}
                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >

                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                            >
                                {{ __('Log Out') }}
                            </x-dropdown-link>

                        </form>

                    </x-slot>

                </x-dropdown>

            </div>


            {{-- MOBILE HAMBURGER --}}
            <div class="-me-2 flex items-center sm:hidden">

                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                >

                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >

                        <path
                            :class="{
                                'hidden': open,
                                'inline-flex': !open
                            }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{
                                'hidden': !open,
                                'inline-flex': open
                            }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />

                    </svg>

                </button>

            </div>

        </div>

    </div>


    {{-- MOBILE NAVIGATION --}}
    <div
        :class="{ 'block': open, 'hidden': !open }"
        class="hidden sm:hidden"
    >

        <div class="pt-2 pb-3 space-y-1">

            {{-- DASHBOARD --}}
            <x-responsive-nav-link
                :href="route($dashboardRoute)"
                :active="request()->routeIs(
                    'owner.dashboard',
                    'employee.dashboard',
                    'customer.dashboard'
                )"
            >
                {{ __('Dashboard') }}
            </x-responsive-nav-link>


            {{-- OWNER MOBILE LINKS --}}
            @if(auth()->user()->role === 'owner')

                <x-responsive-nav-link
                    :href="route('owner.categories.index')"
                    :active="request()->routeIs('owner.categories.*')"
                >
                    {{ __('Categories') }}
                </x-responsive-nav-link>


                <x-responsive-nav-link
                    :href="route('owner.gowns.index')"
                    :active="request()->routeIs('owner.gowns.*')"
                >
                    {{ __('Gowns') }}
                </x-responsive-nav-link>


                <x-responsive-nav-link
                    :href="route('owner.accessories.index')"
                    :active="request()->routeIs('owner.accessories.*')"
                >
                    {{ __('Accessories') }}
                </x-responsive-nav-link>

            @endif


            {{-- EMPLOYEE MOBILE LINKS --}}
            @if(auth()->user()->role === 'employee')

                <x-responsive-nav-link
                    :href="route('employee.dashboard')"
                    :active="request()->routeIs('employee.dashboard')"
                >
                    {{ __('Employee Dashboard') }}
                </x-responsive-nav-link>

            @endif


            {{-- CUSTOMER MOBILE LINKS --}}
            @if(auth()->user()->role === 'customer')

                <x-responsive-nav-link
                    :href="route('customer.dashboard')"
                    :active="request()->routeIs('customer.dashboard')"
                >
                    {{ __('Customer Dashboard') }}
                </x-responsive-nav-link>

            @endif

        </div>


        {{-- MOBILE USER INFORMATION --}}
        <div class="pt-4 pb-1 border-t border-gray-200">

            <div class="px-4">

                <div class="font-medium text-base text-gray-800">
                    {{ Auth::user()->name }}
                </div>

                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->email }}
                </div>

            </div>


            <div class="mt-3 space-y-1">

                {{-- LOGOUT --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>

                </form>

            </div>

        </div>

    </div>

</nav>
