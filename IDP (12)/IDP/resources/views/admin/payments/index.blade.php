@extends('layouts.admin')

@section('title','Payment Releases')

@section('content')

<div class="bg-white rounded-lg shadow overflow-hidden">

<div class="px-6 py-4 border-b">
<h2 class="text-lg font-semibold text-slate-700">
Milestones Ready For Payment
</h2>
</div>


<div class="overflow-x-auto">

<table class="min-w-full text-sm">

<thead class="bg-slate-100 text-slate-600">

<tr>
<th class="px-6 py-3 text-left">Project</th>
<th class="px-6 py-3 text-left">Client</th>
<th class="px-6 py-3 text-left">Designer</th>
<th class="px-6 py-3 text-left">Milestone</th>
<th class="px-6 py-3 text-left">Amount</th>
<th class="px-6 py-3 text-right">Action</th>
</tr>

</thead>


<tbody class="divide-y">

@forelse($milestones as $milestone)

<tr class="hover:bg-slate-50">

<td class="px-6 py-4">
{{ $milestone->project->title }}
</td>

<td class="px-6 py-4">
{{ $milestone->project->client->name }}
</td>

<td class="px-6 py-4">
{{ $milestone->project->designer->name }}
</td>

<td class="px-6 py-4">
{{ $milestone->title }}
</td>

<td class="px-6 py-4 font-semibold text-emerald-600">
₹{{ number_format($milestone->amount,2) }}
</td>

<td class="px-6 py-4 text-right">

<form method="POST"
action="{{ route('admin.payments.release',$milestone) }}">

@csrf

<button
class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-3 py-1.5 rounded">

Release Payment

</button>

</form>

</td>

</tr>

@empty

<tr>
<td colspan="6" class="text-center py-6 text-gray-500">
No approved milestones found.
</td>
</tr>

@endforelse

</tbody>

</table>

</div>


@if($milestones->hasPages())

<div class="px-6 py-4 border-t">
{{ $milestones->links() }}
</div>

@endif

</div>

@endsection
