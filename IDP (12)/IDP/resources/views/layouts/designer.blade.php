<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>@yield('title','Designer Panel') - IDP</title>

<script>
window.userId = {{ auth()->id() ?? 'null' }};
</script>

@vite(['resources/css/app.css','resources/js/app.js'])

</head>


<body class="bg-slate-100">

<div class="flex h-screen overflow-hidden">


{{-- SIDEBAR --}}
<aside class="w-64 bg-white border-r flex-shrink-0">

@include('layouts.sidebar-designer')

</aside>



{{-- MAIN AREA --}}
<div class="flex-1 flex flex-col overflow-hidden">


{{-- HEADER --}}
<header class="bg-white border-b px-8 py-4 flex items-center justify-between">

<h1 class="text-xl font-semibold text-slate-800">
@yield('title','Designer Dashboard')
</h1>


<div class="flex items-center gap-6">

<span class="text-sm text-gray-600">
{{ auth()->user()->name ?? '' }}
</span>

<a href="{{ route('profile.edit') }}"
class="text-sm text-blue-600 hover:text-blue-700">
Profile
</a>

<form method="POST" action="{{ route('logout') }}">
@csrf

<button class="text-red-500 hover:text-red-600 text-sm">
Logout
</button>

</form>

</div>

</header>



{{-- PAGE CONTENT --}}
<main class="flex-1 overflow-y-auto p-8">

<div class="max-w-7xl mx-auto">

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
{{ session('error') }}
</div>
@endif

@yield('content')

</div>

</main>


</div>

</div>

</body>
</html>
