@extends('layouts.admin')

@section('title','Analytics Dashboard')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

<h2 class="text-2xl font-bold">
Platform Analytics Dashboard
</h2>


{{-- RANGE SELECTOR --}}
<div class="flex gap-3 flex-wrap">

<a href="{{ route('admin.analytics',['range'=>'daily']) }}"
class="px-4 py-2 rounded-lg text-sm font-medium transition
{{ $range=='daily' ? 'bg-black text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
Daily
</a>

<a href="{{ route('admin.analytics',['range'=>'weekly']) }}"
class="px-4 py-2 rounded-lg text-sm font-medium transition
{{ $range=='weekly' ? 'bg-black text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
Weekly
</a>

<a href="{{ route('admin.analytics',['range'=>'monthly']) }}"
class="px-4 py-2 rounded-lg text-sm font-medium transition
{{ $range=='monthly' ? 'bg-black text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
Monthly
</a>

<a href="{{ route('admin.analytics',['range'=>'yearly']) }}"
class="px-4 py-2 rounded-lg text-sm font-medium transition
{{ $range=='yearly' ? 'bg-black text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
Yearly
</a>

</div>



{{-- SYSTEM STATS --}}
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Revenue</p>
<p class="text-2xl font-bold text-emerald-600">
₹{{ number_format($totalRevenue,2) }}
</p>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Transactions</p>
<p class="text-2xl font-bold">
{{ $totalTransactions }}
</p>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Projects</p>
<p class="text-2xl font-bold">
{{ $totalProjects }}
</p>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Designers</p>
<p class="text-2xl font-bold text-indigo-600">
{{ $totalDesigners }}
</p>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Clients</p>
<p class="text-2xl font-bold text-blue-600">
{{ $totalClients }}
</p>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<p class="text-sm text-gray-500">Online Users</p>
<p class="text-2xl font-bold text-green-600">
{{ $onlineUsers }}
</p>
</div>

</div>



{{-- AI INSIGHTS --}}
<div class="bg-white p-6 rounded-xl shadow">

<h3 class="text-lg font-semibold mb-4">
AI Insights
</h3>

<div class="grid md:grid-cols-3 gap-6">

<div class="p-4 bg-emerald-50 rounded-lg">
<p class="text-sm text-gray-500">Predicted Next Month Revenue</p>
<p class="text-xl font-bold text-emerald-600">
₹{{ number_format($predictedRevenue ?? 0,2) }}
</p>
</div>

<div class="p-4 bg-orange-50 rounded-lg">
<p class="text-sm text-gray-500">Inactive Designers</p>
<p class="text-xl font-bold text-orange-600">
{{ $inactiveDesigners ?? 0 }}
</p>
</div>

<div class="p-4 bg-blue-50 rounded-lg">
<p class="text-sm text-gray-500">Top Cities</p>

@if(isset($topCities) && $topCities->count())

<ul class="text-sm mt-2 space-y-1">

@foreach($topCities as $city)

<li>{{ $city->location }} ({{ $city->total }})</li>

@endforeach

</ul>

@else

<p class="text-sm text-gray-400">
No data yet
</p>

@endif

</div>

</div>

</div>



{{-- SUCCESS RATE --}}
<div class="bg-white p-6 rounded-xl shadow">

<h3 class="text-lg font-semibold mb-2">
Project Success Rate
</h3>

<div class="flex items-center gap-4">

<div class="w-full bg-gray-200 rounded-full h-3">

<div
class="bg-emerald-500 h-3 rounded-full"
style="width: {{ $successRate }}%">
</div>

</div>

<span class="font-semibold text-emerald-600">
{{ $successRate }}%
</span>

</div>

</div>



{{-- ANALYTICS CHARTS --}}
<div class="grid md:grid-cols-3 gap-6">

<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold mb-4">Revenue Growth</h3>
<div class="h-64">
<canvas id="revenueChart"></canvas>
</div>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold mb-4">User Growth</h3>
<div class="h-64">
<canvas id="usersChart"></canvas>
</div>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold mb-4">Project Growth</h3>
<div class="h-64">
<canvas id="projectsChart"></canvas>
</div>
</div>

</div>



{{-- SECOND ROW CHARTS --}}
<div class="grid md:grid-cols-2 gap-6">

<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold mb-4">Project Status Distribution</h3>
<div class="h-64">
<canvas id="statusChart"></canvas>
</div>
</div>

<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold mb-4">Revenue Comparison</h3>
<div class="h-64">
<canvas id="comparisonChart"></canvas>
</div>
</div>

</div>



{{-- TOP DESIGNERS --}}
<div class="bg-white p-6 rounded-xl shadow">

<h3 class="font-semibold mb-4">
Top Designers
</h3>

<table class="w-full text-sm">

<thead class="text-left text-gray-500 border-b">
<tr>
<th class="py-2">Designer</th>
<th>Projects</th>
<th>Rating</th>
</tr>
</thead>

<tbody>

@foreach($topDesigners as $designer)

<tr class="border-b">

<td class="py-2">
{{ $designer->name }}
</td>

<td>
{{ $designer->completed_projects_count }}
</td>

<td class="text-yellow-500">
⭐ {{ number_format($designer->received_reviews_avg_rating ?? 0,1) }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>



{{-- RECENT USERS --}}
<div class="bg-white p-6 rounded-xl shadow">

<h3 class="font-semibold mb-4">
Recent Users
</h3>

<table class="w-full text-sm">

<thead class="text-left text-gray-500 border-b">
<tr>
<th>Name</th>
<th>Role</th>
<th>Status</th>
</tr>
</thead>

<tbody>

@foreach($recentUsers as $user)

<tr class="border-b">

<td>{{ $user->name }}</td>

<td>{{ ucfirst($user->role) }}</td>

<td>

@if($user->last_seen_at && $user->last_seen_at->gt(now()->subMinutes(2)))
<span class="text-green-600">Online</span>
@else
<span class="text-gray-400">Offline</span>
@endif

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

@endsection



@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

Chart.defaults.animation = false;

const revenueLabels = @json($revenueGrowth->keys()->values() ?? []);
const revenueData = @json($revenueGrowth->values()->values() ?? []);

new Chart(document.getElementById('revenueChart'),{
type:'line',
data:{
labels:revenueLabels,
datasets:[{
label:'Revenue',
data:revenueData,
borderColor:'#10b981',
backgroundColor:'rgba(16,185,129,0.2)',
tension:0.4,
fill:true
}]
},
options:{responsive:true,maintainAspectRatio:false}
});


const userLabels = @json($userGrowth->keys()->values() ?? []);
const userData = @json($userGrowth->values()->values() ?? []);

new Chart(document.getElementById('usersChart'),{
type:'bar',
data:{
labels:userLabels,
datasets:[{
label:'Users',
data:userData,
backgroundColor:'#3b82f6'
}]
},
options:{responsive:true,maintainAspectRatio:false}
});


const projectLabels = @json($projectGrowth->keys()->values() ?? []);
const projectData = @json($projectGrowth->values()->values() ?? []);

new Chart(document.getElementById('projectsChart'),{
type:'bar',
data:{
labels:projectLabels,
datasets:[{
label:'Projects',
data:projectData,
backgroundColor:'#6366f1'
}]
},
options:{responsive:true,maintainAspectRatio:false}
});


const statusLabels = @json($projectStatus->keys()->values() ?? []);
const statusData = @json($projectStatus->values()->values() ?? []);

new Chart(document.getElementById('statusChart'),{
type:'doughnut',
data:{
labels:statusLabels,
datasets:[{
data:statusData,
backgroundColor:['#3b82f6','#10b981','#f59e0b','#ef4444']
}]
},
options:{responsive:true,maintainAspectRatio:false}
});


new Chart(document.getElementById('comparisonChart'),{
type:'bar',
data:{
labels:['Previous Month','Current Month'],
datasets:[{
label:'Revenue',
data:[{{ $previousRevenue ?? 0 }},{{ $currentRevenue ?? 0 }}],
backgroundColor:['#94a3b8','#10b981']
}]
},
options:{responsive:true,maintainAspectRatio:false}
});

</script>

@endpush
