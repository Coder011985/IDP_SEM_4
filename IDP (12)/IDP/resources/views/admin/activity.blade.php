@extends('layouts.admin')

@section('title','System Activity')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

<h2 class="text-2xl font-bold">
System Activity Monitor
</h2>


{{-- SEARCH --}}
<div class="bg-white p-4 rounded-xl shadow">

<form method="GET" class="flex gap-3">

<input
type="text"
name="search"
placeholder="Search user, project, action..."
value="{{ request('search') }}"
class="flex-1 border rounded-lg px-4 py-2">

<button
class="bg-emerald-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-emerald-700">
Search
</button>

<a
href="{{ route('admin.activity') }}"
class="bg-gray-200 px-4 py-2 rounded-lg text-sm hover:bg-gray-300">
Reset
</a>

</form>

</div>



<div class="bg-white rounded-xl shadow overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-slate-100 text-xs uppercase text-slate-600">

<tr>
<th class="px-6 py-3 text-left">User</th>
<th class="px-6 py-3 text-left">Project</th>
<th class="px-6 py-3 text-left">Action</th>
<th class="px-6 py-3 text-left">Description</th>
<th class="px-6 py-3 text-left">Time</th>
</tr>

</thead>


<tbody class="divide-y">

@forelse($activities as $activity)

<tr class="hover:bg-slate-50">

{{-- USER --}}
<td class="px-6 py-4 flex items-center gap-3">

<img
src="https://ui-avatars.com/api/?name={{ urlencode($activity->user->name ?? 'System') }}"
class="w-8 h-8 rounded-full">

<span class="font-medium">
{{ $activity->user->name ?? 'System' }}
</span>

</td>


{{-- PROJECT --}}
<td class="px-6 py-4">

@if($activity->project)

<span class="text-slate-700 font-medium">
{{ $activity->project->title }}
</span>

@else

<span class="text-slate-400">
-
</span>

@endif

</td>


{{-- ACTION --}}
<td class="px-6 py-4">

@php

$colors = [
'project_created' => 'bg-blue-100 text-blue-700',
'bid_submitted' => 'bg-indigo-100 text-indigo-700',
'bid_accepted' => 'bg-green-100 text-green-700',
'milestone_funded' => 'bg-yellow-100 text-yellow-700',
'milestone_submitted' => 'bg-purple-100 text-purple-700',
'milestone_released' => 'bg-emerald-100 text-emerald-700',
'project_approved' => 'bg-green-100 text-green-700',
'project_rejected' => 'bg-red-100 text-red-700',
];

$badge = $colors[$activity->action] ?? 'bg-slate-100 text-slate-700';

@endphp


<span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
{{ ucwords(str_replace('_',' ',$activity->action)) }}
</span>

</td>


{{-- DESCRIPTION --}}
<td class="px-6 py-4 text-slate-600">
{{ $activity->description }}
</td>


{{-- TIME --}}
<td class="px-6 py-4 text-slate-500 text-xs">

<div>
{{ $activity->created_at->diffForHumans() }}
</div>

<div class="text-slate-400">
{{ $activity->created_at->format('d M Y H:i') }}
</div>

</td>


</tr>

@empty

<tr>

<td colspan="5" class="px-6 py-6 text-center text-slate-500">
No activity recorded.
</td>

</tr>

@endforelse

</tbody>

</table>

</div>


<div>
{{ $activities->links() }}
</div>

</div>

@endsection
