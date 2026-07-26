<nav x-data="{ open: false }" class="bg-surface border-b border-line">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-ink" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')">
                        {{ __('Leadek') }}
                    </x-nav-link>
                    <x-nav-link :href="route('deals.index')" :active="request()->routeIs('deals.*')">
                        {{ __('Pipeline') }}
                    </x-nav-link>
                    <x-nav-link :href="route('contacts.index')" :active="request()->routeIs('contacts.*')">
                        {{ __('Kontaktok') }}
                    </x-nav-link>
                    <x-nav-link :href="route('organizations.index')" :active="request()->routeIs('organizations.*')">
                        {{ __('Szervezetek') }}
                    </x-nav-link>
                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                        {{ __('Projektek') }}
                    </x-nav-link>
                    <x-nav-link :href="route('retainers.index')" :active="request()->routeIs('retainers.*')">
                        {{ __('Retainerek') }}
                    </x-nav-link>
                    <x-nav-link :href="route('campaigns.index')" :active="request()->routeIs('campaigns.*')">
                        {{ __('Kampányok') }}
                    </x-nav-link>
                    <x-nav-link :href="route('personal-notes.index')" :active="request()->routeIs('personal-notes.*')">
                        {{ __('Jegyzeteim') }}
                    </x-nav-link>
                    <x-nav-link :href="route('custom-field-definitions.index')" :active="request()->routeIs('custom-field-definitions.*')">
                        {{ __('Egyedi mezők') }}
                    </x-nav-link>
                    <x-nav-link :href="route('activity-log.index')" :active="request()->routeIs('activity-log.*')">
                        {{ __('Napló') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Globális gyorskeresés -->
            <div class="hidden sm:flex sm:items-center flex-1 justify-center px-4">
                <form method="GET" action="{{ route('search') }}" class="w-full max-w-xs">
                    <label class="sr-only" for="nav-search">{{ __('Keresés') }}</label>
                    <input type="text" id="nav-search" name="q" value="{{ request('q') }}"
                           placeholder="{{ __('Keresés…') }}"
                           class="w-full text-fluid-xs rounded-md border-line-strong bg-sunken text-ink-soft focus:border-line-strong focus:ring-line-strong">
                </form>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-fluid-xs leading-4 font-medium rounded-md text-ink-muted bg-surface hover:text-ink-soft focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-ink-muted hover:text-ink-soft hover:bg-surface-hover focus:outline-none focus:bg-surface-hover focus:text-ink-soft transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="px-4 pt-2">
            <form method="GET" action="{{ route('search') }}">
                <label class="sr-only" for="nav-search-mobile">{{ __('Keresés') }}</label>
                <input type="text" id="nav-search-mobile" name="q" value="{{ request('q') }}"
                       placeholder="{{ __('Keresés…') }}"
                       class="w-full text-fluid-xs rounded-md border-line-strong bg-sunken text-ink-soft focus:border-line-strong focus:ring-line-strong">
            </form>
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')">
                {{ __('Leadek') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('deals.index')" :active="request()->routeIs('deals.*')">
                {{ __('Pipeline') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('contacts.index')" :active="request()->routeIs('contacts.*')">
                {{ __('Kontaktok') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('organizations.index')" :active="request()->routeIs('organizations.*')">
                {{ __('Szervezetek') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                {{ __('Projektek') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('retainers.index')" :active="request()->routeIs('retainers.*')">
                {{ __('Retainerek') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('campaigns.index')" :active="request()->routeIs('campaigns.*')">
                {{ __('Kampányok') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('personal-notes.index')" :active="request()->routeIs('personal-notes.*')">
                {{ __('Jegyzeteim') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('custom-field-definitions.index')" :active="request()->routeIs('custom-field-definitions.*')">
                {{ __('Egyedi mezők') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('activity-log.index')" :active="request()->routeIs('activity-log.*')">
                {{ __('Napló') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-line">
            <div class="px-4">
                <div class="font-medium text-fluid-base text-ink">{{ Auth::user()->name }}</div>
                <div class="font-medium text-fluid-xs text-ink-muted">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
