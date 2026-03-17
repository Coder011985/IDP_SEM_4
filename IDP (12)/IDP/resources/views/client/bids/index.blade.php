@extends('layouts.client')

@section('title','Project Bids')

@section('content')

<div class="max-w-6xl mx-auto space-y-8">

<div>

<h2 class="text-2xl font-bold text-slate-800">
Bids for: {{ $project->title }}
</h2>

<p class="text-sm text-slate-500">
Compare designers and choose the best proposal.
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



<div class="space-y-6">

@forelse($bids as $bid)

@php
$designer = $bid->designer;
$rating = $designer?->averageRating() ?? 0;
$completed = $designer?->completedProjects()->count() ?? 0;
@endphp


<div class="bg-white p-6 rounded-xl shadow border
{{ $bid->status === 'accepted' ? 'ring-2 ring-emerald-500' : '' }}">

<div class="flex justify-between items-start mb-5">

<div class="flex items-center gap-4">

<img
src="https://ui-avatars.com/api/?name={{ urlencode($designer->name ?? 'Designer') }}"
class="w-12 h-12 rounded-full">

<div>

<h3 class="text-lg font-semibold text-slate-800">
{{ $designer->name }}
</h3>

<p class="text-xs text-slate-500">
{{ ucfirst($designer->specialization ?? 'Designer') }}
</p>

</div>

</div>

<span class="px-3 py-1 text-xs rounded-full bg-slate-100">
{{ ucfirst($bid->status) }}
</span>

</div>



{{-- DESIGNER STATS --}}
<div class="grid grid-cols-3 gap-4 mb-5 text-sm">

<div class="bg-slate-50 p-3 rounded">

<p class="text-slate-500 text-xs">Rating</p>

<p class="font-semibold">
⭐ {{ number_format($rating,1) }}
</p>

</div>

<div class="bg-slate-50 p-3 rounded">

<p class="text-slate-500 text-xs">Completed</p>

<p class="font-semibold">
{{ $completed }} Projects
</p>

</div>

<div class="bg-slate-50 p-3 rounded">

<p class="text-slate-500 text-xs">Bid Amount</p>

<p class="font-bold text-emerald-600">
₹{{ number_format($bid->amount,2) }}
</p>

</div>

</div>



{{-- PROPOSAL --}}
<div class="mb-6">

<p class="text-sm text-slate-600 leading-relaxed">
{{ $bid->proposal ?? $bid->message }}
</p>

</div>



{{-- ACTION --}}
@if(
$bid->status === 'pending' &&
$project->status === 'pending' &&
$project->approval_status === 'approved'
)

<form method="POST"
action="{{ route('client.projects.acceptBid',[$project->id,$bid->id]) }}"
onsubmit="return confirm('Accept this bid and assign the designer?')">

@csrf

<button class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm">
Accept Bid
</button>

</form>

@endif

</div>

@empty

<div class="bg-white p-10 rounded-xl shadow text-center">

<p class="text-slate-500">
No bids submitted yet.
</p>

</div>

@endforelse

</div>

</div>

@endsection
