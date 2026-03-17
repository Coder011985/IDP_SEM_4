<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

<div class="flex justify-between h-16">

<!-- LEFT -->
<div class="flex">

<!-- Logo -->
<div class="shrink-0 flex items-center">
<a href="{{ route('client.dashboard') }}">
<x-application-logo class="block h-9 w-auto fill-current text-gray-800"/>
</a>
</div>

<!-- Main Navigation -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

<x-nav-link :href="route('client.dashboard')" :active="request()->routeIs('client.dashboard')">
Dashboard
</x-nav-link>

@auth

{{-- CLIENT MENU --}}
@if(auth()->user()->role === 'client')

<x-nav-link :href="route('client.projects.index')" :active="request()->routeIs('client.projects.*')">
My Projects
</x-nav-link>

<x-nav-link :href="route('client.projects.create')" :active="request()->routeIs('client.projects.create')">
Post Project
</x-nav-link>

@endif


{{-- DESIGNER MENU --}}
@if(auth()->user()->role === 'designer')

<x-nav-link :href="route('designer.projects')" :active="request()->routeIs('designer.projects')">
Browse Projects
</x-nav-link>

<x-nav-link :href="route('designer.projects.assigned')" :active="request()->routeIs('designer.projects.assigned')">
Assigned Projects
</x-nav-link>

<x-nav-link :href="route('designer.portfolio.index')" :active="request()->routeIs('designer.portfolio.*')">
Portfolio
</x-nav-link>

<x-nav-link :href="route('designer.earnings')" :active="request()->routeIs('designer.earnings')">
Earnings
</x-nav-link>

@endif


{{-- ADMIN MENU --}}
@if(auth()->user()->role === 'admin')

<x-nav-link :href="route('admin.projects')" :active="request()->routeIs('admin.projects')">
Projects
</x-nav-link>

<x-nav-link :href="route('admin.analytics')" :active="request()->routeIs('admin.analytics')">
Analytics
</x-nav-link>

<x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
Users
</x-nav-link>

@endif

@endauth

</div>

</div>


<!-- RIGHT -->
<div class="hidden sm:flex sm:items-center sm:ms-6 gap-6">

@auth

@php
$count = auth()->user()->unreadNotificationsCount();
@endphp

<!-- Notification Bell -->
<a href="{{ route('notifications.index') }}" class="relative text-xl">

🔔

@if($count > 0)

<span id="notificationCounter"
class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $count }}
</span>

@endif

</a>

<!-- User Dropdown -->
<x-dropdown align="right" width="48">

<x-slot name="trigger">

<button class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white hover:text-gray-800">

<div>{{ Auth::user()->name }}</div>

<div class="ms-1">
<svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
<path fill-rule="evenodd"
d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
clip-rule="evenodd"/>
</svg>
</div>

</button>

</x-slot>

<x-slot name="content">

<x-dropdown-link :href="route('profile.edit')">
Profile
</x-dropdown-link>

<form method="POST" action="{{ route('logout') }}">
@csrf

<x-dropdown-link :href="route('logout')"
onclick="event.preventDefault(); this.closest('form').submit();">
Log Out
</x-dropdown-link>

</form>

</x-slot>

</x-dropdown>

@endauth

</div>


<!-- Mobile Button -->
<div class="-me-2 flex items-center sm:hidden">

<button @click="open = ! open"
class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">

<svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">

<path :class="{'hidden': open, 'inline-flex': ! open }"
class="inline-flex"
stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M4 6h16M4 12h16M4 18h16"/>

<path :class="{'hidden': ! open, 'inline-flex': open }"
class="hidden"
stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M6 18L18 6M6 6l12 12"/>

</svg>

</button>

</div>

</div>

</div>

</nav>
