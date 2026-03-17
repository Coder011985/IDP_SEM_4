<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title','Admin Panel') - IDP</title>


<script>
window.userId = {{ auth()->id() ?? 'null' }};
</script>

@vite(['resources/css/app.css','resources/js/app.js'])

{{-- ChartJS (global analytics charts) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>


<body class="bg-slate-100">

<div class="flex h-screen overflow-hidden">

{{-- SIDEBAR --}}
<aside class="w-64 bg-white border-r flex-shrink-0">

@include('layouts.sidebar-admin')

</aside>



{{-- MAIN AREA --}}
<div class="flex-1 flex flex-col overflow-hidden">

{{-- HEADER --}}
<header class="bg-white border-b px-8 py-4 flex items-center justify-between">

<h1 class="text-xl font-semibold text-slate-800">
@yield('title','Admin Dashboard')
</h1>

<div class="flex items-center gap-6">

{{-- NOTIFICATIONS --}}
@php
$unread = auth()->check()
    ? auth()->user()->unreadNotificationsCount()
    : 0;
@endphp

<a href="{{ route('notifications.index') }}" class="relative text-xl">

🔔

@if($unread > 0)
<span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
{{ $unread }}
</span>
@endif

</a>


<span class="text-sm text-gray-600">
{{ auth()->user()->name ?? '' }}
</span>

<form method="POST" action="{{ route('logout') }}">
@csrf

<button class="text-red-500 hover:text-red-600 text-sm">
Logout
</button>

</form>

</div>

</header>



{{-- CONTENT --}}
<main class="flex-1 overflow-y-auto p-8">

<div class="max-w-7xl mx-auto">

@yield('content')

</div>

</main>

</div>

</div>



{{-- GLOBAL TOAST / ALERT AREA --}}
<div id="toast-container"
class="fixed bottom-5 right-5 space-y-2 z-50"></div>



{{-- GLOBAL ADMIN SCRIPTS --}}
<script>

/*
|--------------------------------------------------------------------------
| Simple Toast Notification System
|--------------------------------------------------------------------------
*/

function showToast(message,type='info'){

const toast=document.createElement('div')

toast.className=
'px-4 py-3 rounded-lg shadow text-white text-sm '+
(type==='error'
?'bg-red-500'
:type==='success'
?'bg-emerald-500'
:'bg-slate-700')

toast.innerText=message

document.getElementById('toast-container').appendChild(toast)

setTimeout(()=>toast.remove(),4000)

}


/*
|--------------------------------------------------------------------------
| Flash Messages From Laravel
|--------------------------------------------------------------------------
*/

@if(session('success'))
showToast("{{ session('success') }}",'success')
@endif

@if(session('error'))
showToast("{{ session('error') }}",'error')
@endif



/*
|--------------------------------------------------------------------------
| Live Analytics Refresh (Admin Dashboard)
|--------------------------------------------------------------------------
*/

function refreshAdminAnalytics(){

const url="{{ route('admin.analytics.live') }}"

fetch(url)
.then(res=>res.json())
.then(data=>{

const revenue=document.querySelector('.stat-revenue')
const transactions=document.querySelector('.stat-transactions')
const projects=document.querySelector('.stat-projects')
const designers=document.querySelector('.stat-designers')
const clients=document.querySelector('.stat-clients')
const online=document.querySelector('.stat-online')

if(revenue) revenue.innerText='₹'+data.revenue
if(transactions) transactions.innerText=data.transactions
if(projects) projects.innerText=data.projects
if(designers) designers.innerText=data.designers
if(clients) clients.innerText=data.clients
if(online) online.innerText=data.online

})
.catch(()=>{})

}


/*
|--------------------------------------------------------------------------
| Auto Refresh Every 10 Seconds
|--------------------------------------------------------------------------
*/

setInterval(refreshAdminAnalytics,10000)

</script>



{{-- PAGE SPECIFIC SCRIPTS --}}
@stack('scripts')

</body>
</html>
