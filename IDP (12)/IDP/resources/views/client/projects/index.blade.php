@extends('layouts.client')

@section('title','My Projects')

@section('content')

@php
use Illuminate\Support\Str;
@endphp

<div class="max-w-7xl mx-auto space-y-8">

{{-- HEADER --}}
<div class="flex items-center justify-between">

<h2 class="text-2xl font-bold text-slate-800">
My Projects
</h2>

<a href="{{ route('client.projects.create') }}"
class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
Create Project
</a>

</div>



{{-- PROJECT GRID --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">

@forelse($projects as $project)

@php

$status = $project->status ?? 'pending';

$statusClasses = match($status) {
'pending' => 'bg-amber-100 text-amber-700',
'assigned' => 'bg-blue-100 text-blue-700',
'completed' => 'bg-green-100 text-green-700',
default => 'bg-slate-100 text-slate-700',
};

$progress = $project->milestoneCompletionPercentage();
$escrow = $project->escrowAmount();
$released = $project->releasedAmount();

$lastMessage = $project->lastMessage();

$unreadCount = $project->designer_id
    ? $project->unreadMessagesCount(auth()->id())
    : 0;

@endphp


<div class="bg-white rounded-xl shadow hover:shadow-lg transition p-6 flex flex-col">

{{-- HEADER --}}
<div class="flex justify-between items-start mb-3">

<div>

<h3 class="text-lg font-semibold text-slate-800">
{{ $project->title }}
</h3>

@if($project->designer)

<div class="flex items-center gap-2 mt-1 text-sm text-slate-500">

<img
src="https://ui-avatars.com/api/?name={{ urlencode($project->designer->name) }}"
class="w-6 h-6 rounded-full">

<span>
{{ $project->designer->name }}
</span>

</div>

@endif

</div>

<span class="px-3 py-1 text-xs rounded-full {{ $statusClasses }}">
{{ ucfirst($status) }}
</span>

</div>



{{-- DESCRIPTION --}}
<p class="text-sm text-slate-600 mb-3">
{{ Str::limit($project->description,120) }}
</p>



{{-- CHAT PREVIEW --}}
@if($lastMessage)

<div class="text-xs text-gray-500 mb-3 bg-gray-50 p-2 rounded">

💬 {{ $project->chatPreview() }}

<span class="text-gray-400">
• {{ $lastMessage->created_at->diffForHumans() }}
</span>

</div>

@endif



{{-- BUDGET --}}
<p class="font-semibold text-slate-800 mb-4">
₹{{ number_format($project->budget_min) }}
-
₹{{ number_format($project->budget_max) }}
</p>



{{-- ESCROW INFO --}}
<div class="grid grid-cols-2 gap-4 mb-4 text-xs">

<div class="bg-slate-50 p-2 rounded">

<p class="text-slate-500">Escrow</p>

<p class="font-semibold text-blue-600">
₹{{ number_format($escrow,2) }}
</p>

</div>

<div class="bg-slate-50 p-2 rounded">

<p class="text-slate-500">Released</p>

<p class="font-semibold text-emerald-600">
₹{{ number_format($released,2) }}
</p>

</div>

</div>



{{-- PROGRESS --}}
<div class="mb-4">

<div class="flex justify-between text-sm mb-1">

<span class="text-slate-500">
Progress
</span>

<span class="font-semibold">
{{ $progress }}%
</span>

</div>

<div class="w-full bg-gray-200 rounded-full h-2">

<div class="bg-emerald-500 h-2 rounded-full"
style="width: {{ $progress }}%">
</div>

</div>

</div>



{{-- META --}}
<div class="space-y-1 text-sm text-slate-600 mb-5">

@if($project->location)
<p>📍 {{ $project->location }}</p>
@endif

@if($project->area_sqft)
<p>📐 {{ $project->area_sqft }} sq.ft</p>
@endif

@if($project->deadline)
<p>⏳ {{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}</p>
@endif

@if($project->style)
<p>🎨 {{ $project->style }}</p>
@endif

@if($project->property_type)
<p>🏠 {{ ucfirst($project->property_type) }}</p>
@endif

</div>



{{-- BUTTONS --}}
<div class="flex gap-3 mt-auto">

<a href="{{ route('client.projects.show',$project->id) }}"
class="flex-1 flex items-center justify-center h-10 bg-slate-800 hover:bg-black text-white rounded-lg text-sm font-medium transition">
View Project
</a>

<a href="{{ route('client.projects.bids',$project->id) }}"
class="flex-1 flex items-center justify-center h-10 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-medium transition">
View Bids
</a>

@if($project->designer_id)

<a href="{{ route('client.projects.chat',$project->id) }}"
class="relative flex-1 flex items-center justify-center h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition">

Chat

@if($unreadCount)

<span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $unreadCount }}
</span>

@endif

</a>

@endif

</div>

</div>


@empty


<div class="col-span-2 bg-white p-12 rounded-xl shadow text-center">

<p class="text-slate-500 mb-4">
No projects yet.
</p>

<a href="{{ route('client.projects.create') }}"
class="px-5 py-3 bg-blue-600 text-white rounded-lg">
Create Project
</a>

</div>


@endforelse

</div>


<div>
{{ $projects->links() }}
</div>


</div>

@endsection
