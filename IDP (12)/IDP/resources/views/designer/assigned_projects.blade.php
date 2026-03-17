@extends('layouts.designer')

@section('title','Assigned Projects')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

<div>

<h2 class="text-2xl font-bold">
Assigned Projects
</h2>

<p class="text-sm text-slate-500">
Manage milestones and submit work.
</p>

</div>



@if(session('success'))
<div class="p-4 bg-green-100 text-green-700 rounded-lg">
{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="p-4 bg-red-100 text-red-700 rounded-lg">
{{ session('error') }}
</div>
@endif



<div class="space-y-8">

@forelse($projects as $project)

@php
$completion = $project->calculateProgress();
$escrow = $project->escrowAmount();
$released = $project->releasedAmount();
@endphp


<div class="bg-white rounded-xl shadow p-6">

<div class="flex justify-between items-start mb-4">

<div>

<h3 class="text-lg font-semibold">
{{ $project->title }}
</h3>

<p class="text-sm text-slate-500">
{{ \Illuminate\Support\Str::limit($project->description,100) }}
</p>

</div>

<div class="flex items-center gap-3">

<a
href="{{ route('designer.projects.chat',$project) }}"
class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
Chat
</a>

<span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
{{ ucfirst($project->status) }}
</span>

</div>

</div>



{{-- PROJECT FINANCIAL SUMMARY --}}
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6 text-sm">

<div class="bg-slate-50 p-3 rounded-lg">
<p class="text-slate-500">Escrow</p>
<p class="font-semibold text-blue-600">
₹{{ number_format($escrow,2) }}
</p>
</div>

<div class="bg-slate-50 p-3 rounded-lg">
<p class="text-slate-500">Released</p>
<p class="font-semibold text-emerald-600">
₹{{ number_format($released,2) }}
</p>
</div>

<div class="bg-slate-50 p-3 rounded-lg">
<p class="text-slate-500">Milestones</p>
<p class="font-semibold">
{{ $project->milestones->count() }}
</p>
</div>

</div>



{{-- PROGRESS --}}
<div class="mb-6">

<div class="flex justify-between text-sm mb-1">

<span>Completion</span>

<span class="font-semibold">
{{ $completion }}%
</span>

</div>

<div class="w-full bg-slate-200 rounded-full h-3">

<div
class="bg-emerald-500 h-3 rounded-full"
style="width: {{ $completion }}%">
</div>

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

<p class="text-sm text-slate-600 mb-2">
Amount: ₹{{ number_format($milestone->amount,2) }}
</p>



@if($milestone->status === 'funded')

<form
method="POST"
action="{{ route('designer.projects.completeMilestone',[$project->id,$milestone->id]) }}">

@csrf

<button
class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
Submit Milestone
</button>

</form>

@endif

</div>

@empty

<p class="text-sm text-slate-500">
No milestones yet.
</p>

@endforelse

</div>

</div>


@empty

<div class="text-center p-10 border border-dashed rounded-xl text-slate-500">
No assigned projects.
</div>

@endforelse

</div>



<div class="mt-8">
{{ $projects->links() }}
</div>

</div>

@endsection
