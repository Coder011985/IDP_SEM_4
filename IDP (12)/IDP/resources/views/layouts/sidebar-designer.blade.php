<nav class="flex flex-col h-full bg-white border-r p-6">

{{-- LOGO --}}
<div class="mb-10">

<h1 class="text-xl font-bold text-slate-800">
IDP Designer
</h1>

<p class="text-sm text-gray-500 mt-1">
Interior Design Portal
</p>

</div>



{{-- MENU --}}
<div class="space-y-2 flex-1">


{{-- DASHBOARD --}}
<a href="{{ route('designer.projects') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('designer.projects') && !request()->routeIs('designer.projects.assigned') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">
Dashboard
</a>


{{-- ASSIGNED PROJECTS --}}
<a href="{{ route('designer.projects.assigned') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('designer.projects.assigned') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">
Assigned Projects
</a>


{{-- PORTFOLIO --}}
<a href="{{ route('designer.portfolio.index') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('designer.portfolio.*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">
Portfolio
</a>


{{-- EARNINGS --}}
<a href="{{ route('designer.earnings') }}"
class="flex items-center px-4 py-2 rounded-lg transition
{{ request()->routeIs('designer.earnings') ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-blue-50 text-slate-700' }}">
My Earnings
</a>


{{-- NOTIFICATIONS --}}
@php
$unread = auth()->check()
? auth()->user()->unreadNotificationsCount()
: 0;
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
{{ auth()->user()->name ?? '' }}
</p>

</div>

</nav>
