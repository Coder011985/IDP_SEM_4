@extends('layouts.admin')

@section('title','Admin - Project Management')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

<div class="flex justify-between items-center">

<h1 class="text-2xl font-bold">
Project Management
</h1>

<p class="text-sm text-gray-500">
Total Projects: {{ $projects->total() }}
</p>

</div>


@if(session('success'))

<div class="bg-green-100 text-green-700 p-3 rounded">
{{ session('success') }}
</div>

@endif


<div class="space-y-8">

@forelse($projects as $project)

<div class="bg-white rounded-xl shadow p-6">

{{-- PROJECT HEADER --}}
<div class="flex justify-between items-center mb-4">

<div>

<h2 class="text-lg font-semibold">
#{{ $project->id }} — {{ $project->title }}
</h2>

<p class="text-sm text-gray-500">

Client:
{{ $project->client->name ?? 'N/A' }}

|

Designer:
{{ $project->designer->name ?? 'Not Assigned' }}

</p>

</div>


<div class="flex gap-2">

@if($project->approval_status === 'pending')

<a
href="{{ route('admin.projects.approve',$project->id) }}"
class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">

Approve

</a>

<a
href="{{ route('admin.projects.reject',$project->id) }}"
class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">

Reject

</a>

@elseif($project->approval_status === 'approved')

<span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded">
Approved
</span>

@elseif($project->approval_status === 'rejected')

<span class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded">
Rejected
</span>

@endif

</div>

</div>



{{-- PROJECT INFO --}}
<div class="grid grid-cols-3 gap-4 text-sm mb-4">

<div>
<span class="text-gray-500">Budget:</span><br>
₹{{ number_format($project->budget_min,2) }} - ₹{{ number_format($project->budget_max,2) }}
</div>

<div>
<span class="text-gray-500">Type:</span><br>
{{ ucfirst($project->property_type) }}
</div>

<div>
<span class="text-gray-500">Progress:</span><br>
{{ $project->progress }}%
</div>

</div>



{{-- MILESTONES --}}
<div class="space-y-4">

@forelse($project->milestones as $milestone)

@php
$badge = match($milestone->status){
'pending'=>'bg-amber-100 text-amber-700',
'funded'=>'bg-blue-100 text-blue-700',
'approved'=>'bg-indigo-100 text-indigo-700',
'released'=>'bg-emerald-100 text-emerald-700',
default=>'bg-slate-100 text-slate-700'
};
@endphp


<div class="border rounded-lg p-4">

<div class="flex justify-between items-center mb-2">

<h4 class="font-semibold">
{{ $milestone->title }}
</h4>

<span class="px-3 py-1 text-xs rounded-full {{ $badge }}">
{{ ucfirst($milestone->status) }}
</span>

</div>


<p class="text-sm text-gray-600 mb-2">
Amount: ₹{{ number_format($milestone->amount,2) }}
</p>


@if($milestone->payment)

<p class="text-xs text-gray-500 mb-3">

Payment Status:
{{ ucfirst($milestone->payment->status) }}

</p>

@endif



@if(
$milestone->status === 'approved'
&& $milestone->payment
&& $milestone->payment->status === 'funded'
)

<form
method="POST"
action="{{ route('admin.milestones.release',[$project->id,$milestone->id]) }}">

@csrf

<button
class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-xs">

Release Payment

</button>

</form>

@endif

</div>


@empty

<p class="text-sm text-gray-500">
No milestones yet.
</p>

@endforelse

</div>

</div>


@empty

<div class="text-center p-10 border border-dashed rounded-lg text-gray-500">
No projects found.
</div>

@endforelse

</div>



<div>
{{ $projects->links() }}
</div>

</div>

@endsection
