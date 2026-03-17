@extends('layouts.designer')

@section('content')

<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Designer Dashboard</h1>

    <div class="bg-white p-6 rounded-xl shadow">
        <p>Welcome {{ auth()->user()->name }}</p>
    </div>
</div>

@endsection
