<nav class="flex flex-col h-full bg-white border-r p-6">

{{-- LOGO --}}
<div class="mb-10">

<h1 class="text-xl font-bold text-slate-800">
IDP Client
</h1>

<p class="text-sm text-gray-500 mt-1">
Interior Design Portal
</p>

</div>



{{-- MENU --}}
<div class="space-y-2 flex-1">

{{-- DASHBOARD --}}
<a href="{{ route('client.dashboard') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('client.dashboard') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">
Dashboard
</a>


{{-- MY PROJECTS --}}
<a href="{{ route('client.projects.index') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('client.projects.index') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">
My Projects
</a>


{{-- CREATE PROJECT --}}
<a href="{{ route('client.projects.create') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('client.projects.create') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">
Create Project
</a>


{{-- NOTIFICATIONS --}}
@php
$unread = 0;

if(auth()->check()) {
    $user = auth()->user();

    $unread = cache()->remember(
        'user_notifications_'.$user->id,
        10,
        fn() => $user->notifications()
            ->whereNull('read_at')
            ->count()
    );
}
@endphp

<a href="{{ route('notifications.index') }}"
class="flex items-center justify-between px-4 py-2 rounded-lg transition
{{ request()->routeIs('notifications.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">

<span>Notifications</span>

@if($unread > 0)
<span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $unread }}
</span>
@endif

</a>

</div>



{{-- USER --}}
<div class="border-t pt-6">

<p class="text-sm text-gray-600 mb-3">
{{ Auth::user()->name ?? '' }}
</p>

<form method="POST" action="{{ route('logout') }}">
@csrf

<button type="submit"
class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
Logout
</button>

</form>

</div>

</nav>
