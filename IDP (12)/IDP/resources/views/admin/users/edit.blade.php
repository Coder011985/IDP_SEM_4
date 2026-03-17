@extends('layouts.app')

@section('content')

<div class="max-w-xl mx-auto p-6">

<h1 class="text-2xl font-bold mb-6">
Edit User
</h1>


<div class="bg-white shadow rounded-lg p-6">

<form method="POST" action="{{ route('admin.users.update',$user->id) }}">
@csrf
@method('PATCH')


{{-- NAME --}}
<div class="mb-4">
<label class="block text-sm font-medium text-gray-700">
Name
</label>

<p class="mt-1 text-gray-900">
{{ $user->name }}
</p>
</div>


{{-- EMAIL --}}
<div class="mb-4">
<label class="block text-sm font-medium text-gray-700">
Email
</label>

<p class="mt-1 text-gray-900">
{{ $user->email }}
</p>
</div>


{{-- ROLE --}}
<div class="mb-4">
<label class="block text-sm font-medium text-gray-700">
Role
</label>

<p class="mt-1 text-gray-900 capitalize">
{{ $user->role }}
</p>
</div>


{{-- ONLINE STATUS --}}
<div class="mb-4">
<label class="block text-sm font-medium text-gray-700">
Online Status
</label>

@if($user->isOnline())

<span class="text-green-600 font-semibold">
● Online
</span>

@else

<span class="text-gray-500">
● Offline
</span>

@endif

</div>


{{-- LAST SEEN --}}
<div class="mb-4">
<label class="block text-sm font-medium text-gray-700">
Last Seen
</label>

<p class="text-gray-700">

@if($user->last_seen_at)
{{ $user->last_seen_at->diffForHumans() }}
@else
Never
@endif

</p>
</div>



{{-- DESIGNER SPECIALIZATION --}}
@if($user->role === 'designer')

<div class="mb-4">

<label class="block text-sm font-medium text-gray-700">
Specialization
</label>

<select name="specialization"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

<option value="">Select Specialization</option>

<option value="residential"
{{ $user->specialization == 'residential' ? 'selected' : '' }}>
Residential
</option>

<option value="commercial"
{{ $user->specialization == 'commercial' ? 'selected' : '' }}>
Commercial
</option>

<option value="office"
{{ $user->specialization == 'office' ? 'selected' : '' }}>
Office
</option>

<option value="luxury"
{{ $user->specialization == 'luxury' ? 'selected' : '' }}>
Luxury
</option>

</select>

</div>

@endif



{{-- USER STATUS --}}
<div class="mb-6">

<label class="block text-sm font-medium text-gray-700">
Account Status
</label>

<select name="status"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

<option value="active"
{{ $user->status == 'active' ? 'selected' : '' }}>
Active
</option>

<option value="inactive"
{{ $user->status == 'inactive' ? 'selected' : '' }}>
Inactive
</option>

</select>

</div>



{{-- UPDATE BUTTON --}}
<div class="mt-6">

<button type="submit"
class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">

Update User

</button>

</div>


</form>

</div>

</div>

@endsection
