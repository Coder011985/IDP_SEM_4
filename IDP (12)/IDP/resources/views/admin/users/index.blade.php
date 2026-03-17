@extends('layouts.admin')

@section('title','Users')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

<h2 class="text-2xl font-bold">
Users Management
</h2>


{{-- STATISTICS --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-6">

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500 text-sm">Total Users</p>
<p class="text-2xl font-bold">
{{ $totalUsers }}
</p>
</div>

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500 text-sm">Designers</p>
<p class="text-2xl font-bold text-indigo-600">
{{ $totalDesigners }}
</p>
</div>

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500 text-sm">Clients</p>
<p class="text-2xl font-bold text-blue-600">
{{ $totalClients }}
</p>
</div>

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500 text-sm">Online</p>
<p class="text-2xl font-bold text-green-600">
{{ $onlineUsers }}
</p>
</div>

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500 text-sm">Offline</p>
<p class="text-2xl font-bold text-gray-600">
{{ $offlineUsers }}
</p>
</div>

</div>



{{-- SEARCH + FILTER --}}
<div class="bg-white p-4 rounded-xl shadow">

<form method="GET" class="flex flex-wrap gap-3">

<input
type="text"
name="search"
value="{{ request('search') }}"
placeholder="Search user name or email..."
class="border rounded-lg px-4 py-2 text-sm flex-1">

<select name="role" class="border rounded-lg px-3 py-2 text-sm">

<option value="">All Roles</option>
<option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
<option value="client" {{ request('role')=='client'?'selected':'' }}>Client</option>
<option value="designer" {{ request('role')=='designer'?'selected':'' }}>Designer</option>

</select>

<button class="bg-emerald-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-emerald-700">
Filter
</button>

<a href="{{ route('admin.users.index') }}"
class="bg-gray-200 px-4 py-2 rounded-lg text-sm hover:bg-gray-300">
Reset
</a>

</form>

</div>



{{-- USERS TABLE --}}
<div class="bg-white shadow rounded-xl overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-xs uppercase">

<tr>

<th class="px-6 py-3 text-left">User</th>
<th class="px-6 py-3 text-left">Email</th>
<th class="px-6 py-3 text-left">Role</th>
<th class="px-6 py-3 text-left">Status</th>
<th class="px-6 py-3 text-left">Last Seen</th>
<th class="px-6 py-3 text-left">Action</th>

</tr>

</thead>


<tbody class="divide-y">

@forelse($users as $user)

<tr class="hover:bg-gray-50">

<td class="px-6 py-4 flex items-center gap-3">

<img
src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}"
class="w-8 h-8 rounded-full">

<span class="font-medium">
{{ $user->name }}
</span>

</td>

<td class="px-6 py-4">
{{ $user->email }}
</td>

<td class="px-6 py-4 capitalize">
{{ $user->role }}
</td>


<td class="px-6 py-4">

@if($user->isOnline())

<span class="text-green-600 font-semibold">
● Online
</span>

@else

<span class="text-gray-500">
● Offline
</span>

@endif

</td>


<td class="px-6 py-4 text-gray-500 text-sm">

@if($user->last_seen_at)

{{ $user->last_seen_at->diffForHumans() }}

@else

Never

@endif

</td>


<td class="px-6 py-4">

<a href="{{ route('admin.users.edit',$user) }}"
class="text-blue-600 hover:underline text-sm">

Edit

</a>

</td>

</tr>

@empty

<tr>
<td colspan="6" class="py-8 text-center text-gray-500">
No users found.
</td>
</tr>

@endforelse

</tbody>

</table>

</div>



<div>
{{ $users->links() }}
</div>

</div>

@endsection
