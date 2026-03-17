@extends('layouts.client')

@section('title','Project Details')

@section('content')

@php
$progress = $project->milestoneCompletionPercentage();
$escrow = $project->escrowAmount();
$released = $project->releasedAmount();
@endphp

<div class="max-w-7xl mx-auto space-y-8">

{{-- PROJECT --}}
<div class="bg-white p-8 rounded-xl shadow">

<div class="flex justify-between items-start mb-6">

<h2 class="text-2xl font-bold text-slate-800">
{{ $project->title }}
</h2>

<span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
{{ ucfirst(str_replace('_',' ',$project->status)) }}
</span>

</div>


<div class="mb-6">

<h4 class="font-semibold text-slate-700 mb-2">
Description
</h4>

<p class="text-slate-600">
{{ $project->description }}
</p>

</div>


<p class="text-sm text-slate-600 mb-6">

<strong>Budget:</strong>

₹{{ number_format($project->budget_min) }}
-
₹{{ number_format($project->budget_max) }}

</p>



{{-- PROJECT FINANCIAL SUMMARY --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

<div class="bg-slate-50 p-4 rounded-lg">
<p class="text-sm text-slate-500">Escrow Funded</p>
<p class="text-lg font-bold text-blue-600">
₹{{ number_format($escrow,2) }}
</p>
</div>

<div class="bg-slate-50 p-4 rounded-lg">
<p class="text-sm text-slate-500">Released</p>
<p class="text-lg font-bold text-emerald-600">
₹{{ number_format($released,2) }}
</p>
</div>

<div class="bg-slate-50 p-4 rounded-lg">
<p class="text-sm text-slate-500">Milestones</p>
<p class="text-lg font-bold">
{{ $project->milestones->count() }}
</p>
</div>

</div>



{{-- PROGRESS --}}
<div>

<div class="flex justify-between text-sm mb-2">

<span class="text-slate-500">
Milestone Completion
</span>

<span class="font-semibold">
{{ $progress }}%
</span>

</div>

<div class="w-full bg-gray-200 rounded-full h-3">

<div class="bg-emerald-500 h-3 rounded-full"
style="width: {{ $progress }}%">
</div>

</div>

</div>

</div>



{{-- CHAT --}}
@if($project->designer_id)

<div class="bg-white p-8 rounded-xl shadow">

<div class="flex justify-between items-center">

<h3 class="font-semibold text-lg">
Project Chat
</h3>

<a href="{{ route('client.projects.chat',$project) }}"
class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm">

Open Chat

@if($project->unreadMessagesCount(auth()->id()))

<span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $project->unreadMessagesCount(auth()->id()) }}
</span>

@endif

</a>

</div>

<p class="text-sm text-slate-500 mt-2">
Communicate with your designer.
</p>

</div>

@endif



{{-- MILESTONES --}}
@if($project->milestones->count())

<div class="bg-white p-8 rounded-xl shadow">

<h3 class="text-lg font-semibold mb-6">
Project Milestones
</h3>

<div class="space-y-4">

@foreach($project->milestones as $milestone)

@php
$badge = match($milestone->status){
'pending'=>'bg-amber-100 text-amber-700',
'funded'=>'bg-blue-100 text-blue-700',
'approved'=>'bg-indigo-100 text-indigo-700',
'released'=>'bg-emerald-100 text-emerald-700',
default=>'bg-slate-100 text-slate-700'
};
@endphp

<div class="border rounded-lg p-5">

<div class="flex justify-between items-center mb-2">

<h4 class="font-medium">
{{ $milestone->title }}
</h4>

<span class="text-xs px-3 py-1 rounded-full {{ $badge }}">
{{ ucfirst($milestone->status) }}
</span>

</div>


<p class="text-sm text-slate-600 mb-3">
Amount: ₹{{ number_format($milestone->amount,2) }}
</p>


@if($milestone->payment)

<p class="text-xs text-slate-500 mb-3">
Payment Status:
<strong>{{ ucfirst($milestone->payment->status) }}</strong>
</p>

@endif



@if(
$milestone->status === 'pending'
&& $milestone->payment
&& $milestone->payment->status === 'pending'
)

<form method="POST"
action="{{ route('client.milestones.fund',$milestone) }}"
class="space-y-3">

@csrf

<select name="payment_method"
class="w-full border rounded-lg px-3 py-2 text-sm">

<option value="demo">Demo Payment</option>
<option value="upi">UPI / QR Payment</option>
<option value="bank">Bank Transfer</option>
<option value="razorpay">Razorpay</option>

</select>

<button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
Fund Milestone
</button>

</form>

@endif

</div>

@endforeach

</div>

</div>

@endif



{{-- ACTIVITY TIMELINE --}}
@if($project->activities->count())

<div class="bg-white p-8 rounded-xl shadow">

<h3 class="text-lg font-semibold mb-6">
Project Activity
</h3>

<div class="space-y-4">

@foreach($project->activities as $activity)

<div class="flex items-start gap-4">

<div class="w-3 h-3 mt-2 bg-blue-500 rounded-full"></div>

<div>

<p class="text-sm text-slate-700">
{{ $activity->description ?? $activity->action }}
</p>

<p class="text-xs text-slate-400">
{{ $activity->created_at->diffForHumans() }}
</p>

</div>

</div>

@endforeach

</div>

</div>

@endif



<div>

<a href="{{ route('client.projects.index') }}"
class="px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded-lg text-sm">
Back
</a>

</div>

</div>

@endsection
