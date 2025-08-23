<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('projects.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                @php
                    $codesActive = request()->routeIs('account-code-types.*')
                        || request()->routeIs('account-codes.*')
                        || request()->routeIs('expense-codes.*');

                    $reportsActive = request()->routeIs('reports.summary')
                        || request()->routeIs('reports.cashbook')
                        || request()->routeIs('reports.notes');
                @endphp

               <!-- Desktop Nav Links -->
                <div class="hidden space-x-4 sm:-my-px sm:ml-6 sm:flex items-center">
                    <x-nav-link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">
                        Projects
                    </x-nav-link>

                    <x-nav-link href="{{ route('project-types.index') }}" :active="request()->routeIs('project-types.*')">
                        Project Types
                    </x-nav-link>

                    <x-nav-link href="{{ route('expenses.index') }}" :active="request()->routeIs('expenses.index')">
                        Expenses
                    </x-nav-link>

                     <x-nav-link href="{{ route('workers.index') }}" :active="request()->routeIs('workers.*')">
                        Workers
                    </x-nav-link>

                    <!-- Codes dropdown (desktop) -->
                    <div x-data="{ openCodes:false }" class="relative">
                        <button @click="openCodes = !openCodes"
                            class="inline-flex items-center h-16 px-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none
                                transition duration-150 ease-in-out"
                            :class="{'border-indigo-400 text-gray-900': {{ request()->routeIs('account-codes.*') || request()->routeIs('expense-codes.*') ? 'true' : 'false' }} }">
                            Codes
                            <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="openCodes" @click.away="openCodes=false"
                            x-cloak
                            class="absolute z-40 mt-2 w-44 rounded-xl border bg-white shadow">
                            <a href="{{ route('account-codes.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Account Codes</a>
                    
                        </div>
                    </div>

                    <!-- Reports dropdown (desktop) -->
                    <div x-data="{ openReports:false }" class="relative">
                        <button @click="openReports = !openReports"
                            class="inline-flex items-center h-16 px-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none
                                transition duration-150 ease-in-out"
                            :class="{'border-indigo-400 text-gray-900': {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                            Reports
                            <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="openReports" @click.away="openReports=false"
                            x-cloak
                            class="absolute z-40 mt-2 w-48 rounded-xl border bg-white shadow">
                            <a href="{{ route('reports.summary') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Summary</a>
                            <a href="{{ route('reports.cashbook') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cash Book</a>
                            <a href="{{ route('reports.notes') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Notes (Monthly)</a>
                            <a href="{{ route('reports.pnl') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profit and Loss (Monthly)</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profile -->
                        <x-dropdown-link href="{{ route('profile.edit') }}">
                            Profile
                        </x-dropdown-link>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Mobile Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">
                Projects
            </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ route('project-types.index') }}" :active="request()->routeIs('project-types.*')">
                Project Types
            </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ route('expenses.index') }}" :active="request()->routeIs('expenses.index')">
                Expenses
            </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ route('workers.index') }}" :active="request()->routeIs('workers.index')">
                Workers
            </x-responsive-nav-link>

            <div class="px-4 pt-2 text-xs font-semibold text-gray-500">Codes</div>
            <x-responsive-nav-link href="{{ route('account-code-types.index') }}" :active="request()->routeIs('account-code-types.*')">
                Account Code Types
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('account-codes.index') }}" :active="request()->routeIs('account-codes.*')" class="pl-8">
                Account Codes
            </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ route('reports.summary') }}" :active="request()->routeIs('reports.summary')">
                Reports
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('reports.cashbook') }}" :active="request()->routeIs('reports.cashbook')">
                Cash Book
            </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ route('reports.notes') }}" :active="request()->routeIs('reports.notes')">
                Notes (Monthly)
            </x-responsive-nav-link>
            
            <x-responsive-nav-link href="{{ route('reports.pnl') }}" :active="request()->routeIs('reports.pnl')">
                Profit and Loss (Monthly)
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.edit') }}">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>