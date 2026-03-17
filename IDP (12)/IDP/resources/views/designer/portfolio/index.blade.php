@extends('layouts.designer')

@section('title','My Portfolio')

@section('content')

<div class="max-w-7xl mx-auto">

<div class="flex justify-between items-center mb-6">

<h2 class="text-2xl font-bold">My Portfolio</h2>

<a href="{{ route('designer.portfolio.create') }}"
class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
Add Portfolio
</a>

</div>


@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
{{ session('success') }}
</div>
@endif



<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

@forelse($portfolios as $portfolio)

<div class="bg-white shadow rounded-lg overflow-hidden">

@if($portfolio->images->first() && $portfolio->images->first()->image_path)
<img src="{{ asset('storage/'.$portfolio->images->first()->image_path) }}"
class="w-full h-48 object-cover">
@endif


<div class="p-4">

<h3 class="font-semibold text-lg">
{{ $portfolio->title }}
</h3>

<p class="text-sm text-gray-500 mb-2">
{{ $portfolio->category }}
</p>

<p class="text-gray-600 text-sm mb-4">
{{ \Illuminate\Support\Str::limit($portfolio->description,100) }}
</p>


<div class="flex justify-between items-center">

<a href="{{ route('designer.portfolio.edit',$portfolio->id) }}"
class="text-blue-600 hover:underline text-sm">
Edit
</a>

<form method="POST"
action="{{ route('designer.portfolio.destroy',$portfolio->id) }}">

@csrf
@method('DELETE')

<button
onclick="return confirm('Delete this portfolio project?')"
class="text-red-600 hover:underline text-sm">
Delete
</button>

</form>

</div>

</div>

</div>

@empty

<div class="col-span-full text-center text-gray-500 py-16">
No portfolio projects yet.
</div>

@endforelse

</div>

</div>

@endsection
