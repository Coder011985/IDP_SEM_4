<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>@yield('title','Client Panel') - IDP</title>

<script>
window.userId = {{ auth()->id() ?? 'null' }};
</script>

@vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="bg-slate-100">

<div class="flex h-screen overflow-hidden">

{{-- SIDEBAR --}}
<aside class="w-64 bg-white border-r flex-shrink-0">

@include('layouts.sidebar-client')

</aside>



{{-- MAIN AREA --}}
<div class="flex-1 flex flex-col overflow-hidden">



{{-- TOP HEADER --}}
<header class="bg-white border-b px-8 py-4 flex items-center justify-between">

<h1 class="text-xl font-semibold text-slate-800">
@yield('title','Dashboard')
</h1>

<div class="flex items-center gap-6">

@php

$notifications = auth()->check()
? \App\Models\Notification::where('user_id', auth()->id())
->latest()
->take(5)
->get()
: collect();

$notificationCount = auth()->check()
? auth()->user()->unreadNotificationsCount()
: 0;

$chatUnread = auth()->check()
? \App\Models\Chat::unreadCountForUser(auth()->id())
: 0;

@endphp



{{-- CHAT ICON --}}
<div class="relative">

<a href="{{ route('client.projects.index') }}"
class="relative text-gray-600 hover:text-gray-800">

💬

@if($chatUnread > 0)
<span
class="absolute -top-2 -right-3 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $chatUnread }}
</span>
@endif

</a>

</div>



{{-- NOTIFICATION BELL --}}
<div class="relative">

<button onclick="toggleNotifications()"
class="relative text-gray-600 hover:text-gray-800">

🔔

@if($notificationCount > 0)
<span
class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $notificationCount }}
</span>
@endif

</button>


{{-- DROPDOWN --}}
<div id="notificationDropdown"
class="hidden absolute right-0 mt-3 w-80 bg-white border rounded-lg shadow-lg z-50">

<div class="p-3 border-b font-semibold text-sm">
Notifications
</div>

<div class="max-h-80 overflow-y-auto">

@forelse($notifications as $notification)

<a href="{{ $notification->link ?? route('notifications.index') }}"
class="block px-4 py-3 text-sm hover:bg-gray-100
{{ $notification->is_read ? '' : 'bg-blue-50' }}">

{{ $notification->message }}

<div class="text-xs text-gray-500 mt-1">
{{ $notification->created_at->diffForHumans() }}
</div>

</a>

@empty

<div class="p-4 text-sm text-gray-500 text-center">
No notifications
</div>

@endforelse

</div>

<div class="p-2 border-t text-center">
<a href="{{ route('notifications.index') }}"
class="text-sm text-blue-600 hover:underline">
View all notifications
</a>
</div>

</div>

</div>



{{-- USER NAME --}}
<span class="text-sm text-gray-600">
{{ Auth::user()->name ?? '' }}
</span>



{{-- PROFILE --}}
<a href="{{ route('profile.edit') }}"
class="text-blue-600 hover:text-blue-700 text-sm">
Profile
</a>



{{-- LOGOUT --}}
<form method="POST" action="{{ route('logout') }}">
@csrf
<button class="text-red-500 hover:text-red-600 text-sm">
Logout
</button>
</form>

</div>

</header>



{{-- PAGE CONTENT --}}
<main class="flex-1 overflow-y-auto p-8">

<div class="max-w-7xl mx-auto">

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
{{ session('error') }}
</div>
@endif

@yield('content')

</div>

</main>

</div>

</div>


<script>

function toggleNotifications() {
const dropdown = document.getElementById('notificationDropdown');
dropdown.classList.toggle('hidden');
}

</script>

</body>
</html>
