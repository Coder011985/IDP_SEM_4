@extends('layouts.designer')

@section('title','Designer Dashboard')

@section('content')

<div class="max-w-7xl mx-auto space-y-10">

<h2 class="text-2xl font-bold">
Designer Dashboard
</h2>



{{-- STATS --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Available Projects</p>
<p class="text-3xl font-bold text-indigo-600 mt-2">
{{ $availableCount }}
</p>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Assigned Projects</p>
<p class="text-3xl font-bold text-emerald-600 mt-2">
{{ $assignedCount }}
</p>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Completed Projects</p>
<p class="text-3xl font-bold text-green-600 mt-2">
{{ $completedCount }}
</p>
</div>

</div>



{{-- EARNINGS --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div class="bg-green-100 p-6 rounded-xl shadow">
<p class="text-sm font-medium text-green-700">
Total Released Earnings
</p>

<p class="text-2xl font-bold text-green-900 mt-2">
₹{{ number_format($releasedTotal,2) }}
</p>
</div>


<div class="bg-yellow-100 p-6 rounded-xl shadow">
<p class="text-sm font-medium text-yellow-700">
Escrow (Funded)
</p>

<p class="text-2xl font-bold text-yellow-900 mt-2">
₹{{ number_format($fundedTotal,2) }}
</p>
</div>

</div>



{{-- CHART --}}
<div class="bg-white p-6 rounded-xl shadow">

<h3 class="text-lg font-semibold mb-4">
Monthly Earnings
</h3>

<canvas id="earningsChart"></canvas>

</div>



{{-- AVAILABLE PROJECTS --}}
<div>

<h2 class="text-2xl font-bold mb-6">
Available Projects
</h2>

@if(auth()->user()->specialization === null)

<div class="mb-4 p-4 rounded-lg bg-yellow-100 text-yellow-700">
Your specialization is not set.
</div>

@endif


@if(session('success'))

<div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
{{ session('success') }}
</div>

@endif


@if(session('error'))

<div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
{{ session('error') }}
</div>

@endif



<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

@forelse($projects as $project)

<div class="bg-white rounded-xl shadow p-6">

<div class="flex justify-between items-start mb-4">

<h3 class="text-lg font-semibold">
{{ $project->title }}
</h3>

<span class="px-3 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700">
{{ ucfirst($project->property_type) }}
</span>

</div>


<p class="text-sm text-slate-600 mb-4">
{{ \Illuminate\Support\Str::limit($project->description,120) }}
</p>


<div class="grid grid-cols-2 gap-4 mb-4 text-sm">

<div>
<p class="text-slate-500">Budget</p>
<p class="font-semibold text-emerald-600">
₹{{ number_format($project->budget_min ?? 0) }}
-
₹{{ number_format($project->budget_max ?? 0) }}
</p>
</div>

<div>
<p class="text-slate-500">Location</p>
<p class="font-medium">
{{ $project->location ?? 'N/A' }}
</p>
</div>

</div>



<form action="{{ route('designer.projects.submitBid',$project->id) }}"
method="POST"
class="space-y-3">

@csrf

<input
type="number"
name="amount"
placeholder="Bid Amount"
required
class="w-full border rounded-lg px-4 py-2">


<textarea
name="proposal"
placeholder="Your proposal"
rows="3"
required
class="w-full border rounded-lg px-4 py-2"></textarea>


<button
class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg">
Submit Bid
</button>

</form>

</div>

@empty

<div class="col-span-2 text-center p-10 border border-dashed rounded-xl text-slate-500">

No projects available.

</div>

@endforelse

</div>


<div class="mt-8">
{{ $projects->links() }}
</div>

</div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const earnings = @json($monthlyEarnings)

const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']

const data = Array(12).fill(0)

for(const m in earnings){
data[m-1] = earnings[m]
}

new Chart(document.getElementById('earningsChart'),{
type:'bar',
data:{
labels:labels,
datasets:[{
data:data
}]
},
options:{
plugins:{legend:{display:false}}
}
})

</script>

@endsection
