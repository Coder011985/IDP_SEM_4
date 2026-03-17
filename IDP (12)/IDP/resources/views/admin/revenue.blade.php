@extends('layouts.admin')

@section('title','Revenue Dashboard')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

<div class="flex justify-between items-center">

<h1 class="text-2xl font-bold">
Platform Revenue Dashboard
</h1>

<div class="flex gap-3">

<form method="GET">

<select name="year" onchange="this.form.submit()"
class="border rounded-lg px-4 py-2 text-sm">

@foreach($years as $year)

<option value="{{ $year }}"
{{ $selectedYear == $year ? 'selected' : '' }}>
{{ $year }}
</option>

@endforeach

</select>

</form>


<a
href="{{ route('admin.revenue.export',$selectedYear) }}"
class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm">

Export {{ $selectedYear }}

</a>

</div>

</div>



{{-- STATS --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

<div class="bg-white p-6 rounded-xl shadow">

<p class="text-sm text-gray-500">
Platform Commission
</p>

<p class="text-2xl font-bold text-emerald-600 mt-2">
₹{{ number_format($totalCommission ?? 0,2) }}
</p>

</div>



<div class="bg-white p-6 rounded-xl shadow">

<p class="text-sm text-gray-500">
Paid To Designers
</p>

<p class="text-2xl font-bold text-indigo-600 mt-2">
₹{{ number_format($totalDesignerPayout ?? 0,2) }}
</p>

</div>



<div class="bg-white p-6 rounded-xl shadow">

<p class="text-sm text-gray-500">
Total Released
</p>

<p class="text-2xl font-bold text-slate-800 mt-2">
₹{{ number_format($totalReleased ?? 0,2) }}
</p>

</div>



<div class="bg-white p-6 rounded-xl shadow">

<p class="text-sm text-gray-500">
Escrow Holding
</p>

<p class="text-2xl font-bold text-blue-600 mt-2">
₹{{ number_format($totalEscrow ?? 0,2) }}
</p>

</div>

</div>



{{-- CHART --}}
<div class="bg-white p-6 rounded-xl shadow">

<h2 class="text-lg font-semibold mb-4">
Monthly Commission — {{ $selectedYear }}
</h2>

<canvas id="revenueChart"></canvas>

</div>



{{-- TABLE --}}
<div class="bg-white rounded-xl shadow overflow-hidden">

<div class="p-6 border-b">

<h2 class="font-semibold">
Released Payments — {{ $selectedYear }}
</h2>

</div>



<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-xs uppercase">

<tr>

<th class="px-6 py-3">Project</th>
<th class="px-6 py-3">Client</th>
<th class="px-6 py-3">Designer</th>
<th class="px-6 py-3">Total</th>
<th class="px-6 py-3">Commission</th>
<th class="px-6 py-3">Designer</th>
<th class="px-6 py-3">Released</th>

</tr>

</thead>



<tbody class="divide-y">

@forelse($payments as $payment)

<tr class="hover:bg-gray-50">

<td class="px-6 py-4 font-medium">
{{ $payment->project->title ?? '-' }}
</td>

<td class="px-6 py-4">
{{ $payment->project->client->name ?? '-' }}
</td>

<td class="px-6 py-4">
{{ $payment->project->designer->name ?? '-' }}
</td>

<td class="px-6 py-4 font-semibold">
₹{{ number_format($payment->amount,2) }}
</td>

<td class="px-6 py-4 text-emerald-600 font-semibold">
₹{{ number_format($payment->commission_amount,2) }}
</td>

<td class="px-6 py-4 text-indigo-600 font-semibold">
₹{{ number_format($payment->designer_amount,2) }}
</td>

<td class="px-6 py-4">
{{ $payment->released_at ? $payment->released_at->format('d M Y') : '-' }}
</td>

</tr>

@empty

<tr>
<td colspan="7" class="text-center py-6 text-gray-500">
No payments found.
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const monthlyData = @json($monthlyRevenue)

const labels = monthlyData.map(item =>
new Date(0,item.month-1).toLocaleString('default',{month:'short'})
)

const commission = monthlyData.map(item => item.total_commission)

new Chart(document.getElementById('revenueChart'),{

type:'bar',

data:{
labels:labels,
datasets:[{
label:'Commission ₹',
data:commission,
backgroundColor:'#10b981'
}]
},

options:{
responsive:true,
scales:{
y:{beginAtZero:true}
}
}

})

</script>

@endsection
