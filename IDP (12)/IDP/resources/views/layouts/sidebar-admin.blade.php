<nav class="flex flex-col h-full bg-white border-r p-6">

{{-- TITLE --}}
<div class="mb-8">

<h1 class="text-xl font-bold text-slate-800">
Admin Panel
</h1>

</div>



{{-- MENU --}}
<div class="space-y-2 flex-1">

{{-- PROJECTS --}}
<a href="{{ route('admin.projects') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('admin.projects*') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 text-slate-700' }}">
Projects
</a>


{{-- ANALYTICS --}}
<a href="{{ route('admin.analytics') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('admin.analytics*') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 text-slate-700' }}">
Analytics
</a>


{{-- REVENUE --}}
<a href="{{ route('admin.revenue') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('admin.revenue*') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 text-slate-700' }}">
Revenue
</a>


{{-- PAYMENTS --}}
<a href="{{ route('admin.payments.index') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('admin.payments*') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 text-slate-700' }}">
Payments
</a>


{{-- ACTIVITY MONITOR --}}
<a href="{{ route('admin.activity') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('admin.activity*') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 text-slate-700' }}">
Activity Monitor
</a>


{{-- USERS --}}
<a href="{{ route('admin.users.index') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('admin.users.*') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 text-slate-700' }}">
Users
</a>


{{-- NOTIFICATIONS --}}
@php
$unread = auth()->check()
    ? auth()->user()->unreadNotificationsCount()
    : 0;
@endphp

<a href="{{ route('notifications.index') }}"
class="flex items-center justify-between px-4 py-2 rounded-lg transition
{{ request()->routeIs('notifications.*') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 text-slate-700' }}">

<span>Notifications</span>

@if($unread > 0)
<span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $unread }}
</span>
@endif

</a>

</div>



{{-- LOGOUT --}}
<form method="POST" action="{{ route('logout') }}" class="pt-6 border-t">

@csrf

<button
class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
Logout
</button>

</form>

</nav>
