@extends('layouts.admin')

@section('title','Disputes')

@section('content')

<div class="max-w-7xl mx-auto p-6">

<h1 class="text-2xl font-bold mb-6">
Dispute Management
</h1>

<div class="bg-white shadow rounded-lg overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-left">

<tr>
<th class="p-3">Project</th>
<th class="p-3">Client</th>
<th class="p-3">Designer</th>
<th class="p-3">Milestone</th>
<th class="p-3">Reason</th>
<th class="p-3">Status</th>
<th class="p-3">Action</th>
</tr>

</thead>

<tbody>

@forelse($disputes as $dispute)

<tr class="border-t">

<td class="p-3">
{{ $dispute->project->title }}
</td>

<td class="p-3">
{{ $dispute->project->client->name }}
</td>

<td class="p-3">
{{ $dispute->project->designer->name ?? 'N/A' }}
</td>

<td class="p-3">
{{ $dispute->milestone->title ?? 'N/A' }}
</td>

<td class="p-3 max-w-xs">
{{ $dispute->reason }}
</td>

<td class="p-3">

@if($dispute->status == 'open')
<span class="text-red-600 font-semibold">
Open
</span>
@else
<span class="text-green-600 font-semibold">
Resolved
</span>
@endif

</td>

<td class="p-3">

@if($dispute->status == 'open')

<form method="POST"
action="{{ route('admin.disputes.resolve',$dispute) }}"
class="flex gap-2">

@csrf

<select name="resolution"
class="border rounded px-2 py-1 text-sm">

<option value="refund_client">
Refund Client
</option>

<option value="pay_designer">
Pay Designer
</option>

</select>

<button
class="bg-emerald-600 text-white px-3 py-1 rounded text-sm">
Resolve
</button>

</form>

@else

<span class="text-gray-400">
Completed
</span>

@endif

</td>

</tr>

@empty

<tr>
<td colspan="7" class="p-6 text-center text-gray-500">
No disputes found.
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

<div class="mt-6">
{{ $disputes->links() }}
</div>

</div>

@endsection
