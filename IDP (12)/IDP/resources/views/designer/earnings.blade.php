@extends('layouts.designer')

@section('title','My Earnings')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

<h1 class="text-2xl font-bold">
My Earnings
</h1>



{{-- SUMMARY --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div class="bg-green-100 p-6 rounded-xl shadow">
<p class="text-sm text-green-700">
Total Released Earnings
</p>

<p class="text-2xl font-bold text-green-900 mt-2">
₹{{ number_format($releasedTotal,2) }}
</p>
</div>


<div class="bg-yellow-100 p-6 rounded-xl shadow">
<p class="text-sm text-yellow-700">
Escrow (Funded)
</p>

<p class="text-2xl font-bold text-yellow-900 mt-2">
₹{{ number_format($fundedTotal,2) }}
</p>
</div>

</div>



{{-- PAYMENTS TABLE --}}
<div class="bg-white rounded-xl shadow overflow-hidden">

<table class="w-full text-left">

<thead class="bg-gray-50 text-xs uppercase text-gray-500">

<tr>
<th class="px-6 py-3">Project</th>
<th class="px-6 py-3">Client</th>
<th class="px-6 py-3">Amount</th>
<th class="px-6 py-3">Status</th>
<th class="px-6 py-3">Released</th>
</tr>

</thead>


<tbody class="divide-y">

@forelse($payments as $payment)

<tr class="hover:bg-gray-50">

<td class="px-6 py-4 text-sm font-medium">
{{ $payment->project->title ?? '-' }}
</td>

<td class="px-6 py-4 text-sm">
{{ $payment->project->client->name ?? '-' }}
</td>

<td class="px-6 py-4 text-sm font-semibold">
₹{{ number_format($payment->amount,2) }}
</td>

<td class="px-6 py-4 text-sm">

@php
$color = match($payment->status){
'released'=>'bg-green-100 text-green-700',
'funded'=>'bg-yellow-100 text-yellow-700',
default=>'bg-gray-100 text-gray-700'
};
@endphp

<span class="px-2 py-1 text-xs rounded {{ $color }}">
{{ ucfirst($payment->status) }}
</span>

</td>

<td class="px-6 py-4 text-sm text-gray-600">

{{ $payment->released_at
? \Carbon\Carbon::parse($payment->released_at)->format('d M Y')
: '-' }}

</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center py-6 text-gray-500">
No earnings yet.
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>

@endsection
