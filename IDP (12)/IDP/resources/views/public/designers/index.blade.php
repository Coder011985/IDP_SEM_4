@extends('layouts.app')

@section('title','Browse Designers')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-16">

{{-- PAGE HEADER --}}
<div class="text-center mb-12">

<h1 class="text-3xl font-bold mb-2">
Explore Professional Designers
</h1>

<p class="text-gray-500">
Browse verified interior designers on our platform.
</p>

</div>



{{-- SEARCH + FILTER --}}
<form method="GET" class="bg-white border rounded-xl p-6 mb-10 shadow-sm">

<div class="grid md:grid-cols-5 gap-4">

{{-- SEARCH --}}
<input
type="text"
name="search"
value="{{ request('search') }}"
placeholder="Search designer name..."
class="border rounded-lg px-4 py-2 w-full"
/>


{{-- SPECIALIZATION --}}
<select name="specialization" class="border rounded-lg px-4 py-2">

<option value="">
All Specializations
</option>

@foreach($specializations as $spec)

<option
value="{{ $spec }}"
{{ request('specialization')==$spec ? 'selected' : '' }}>

{{ ucfirst($spec) }}

</option>

@endforeach

</select>



{{-- SORT --}}
<select name="sort" class="border rounded-lg px-4 py-2">

<option value="">
Default Ranking
</option>

<option value="rating"
{{ request('sort')=='rating' ? 'selected' : '' }}>
Highest Rating
</option>

<option value="projects"
{{ request('sort')=='projects' ? 'selected' : '' }}>
Most Completed Projects
</option>

<option value="reviews"
{{ request('sort')=='reviews' ? 'selected' : '' }}>
Most Reviews
</option>

</select>



{{-- RESET --}}
<a
href="{{ route('public.designers.index') }}"
class="border rounded-lg px-4 py-2 text-center hover:bg-gray-100">

Reset

</a>



{{-- BUTTON --}}
<button
class="bg-black text-white rounded-lg px-6 py-2 hover:bg-gray-800 transition">

Search

</button>

</div>

</form>



{{-- DESIGNERS GRID --}}
<div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">

@forelse($designers as $designer)

@php
$badge = $designer->reputationBadge();
@endphp

<div class="bg-white border rounded-xl p-6 shadow hover:shadow-xl transition">


{{-- PROFILE IMAGE --}}
<div class="flex items-center gap-4 mb-4">

<img
src="{{ $designer->profile_photo ? asset('storage/'.$designer->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($designer->name) }}"
class="w-14 h-14 rounded-full object-cover"
/>

<div>

<h3 class="text-lg font-semibold">
{{ $designer->name }}
</h3>

<p class="text-sm text-gray-500">
{{ $designer->specialization ?? 'Interior Design' }}
</p>

</div>

</div>



{{-- RATING --}}
<p class="text-sm mb-2">

⭐ Rating:

@if($designer->received_reviews_avg_rating)

<strong>
{{ number_format($designer->received_reviews_avg_rating,1) }}
</strong>

<span class="text-gray-400">
({{ $designer->reviews_count ?? 0 }} reviews)
</span>

@else

<span class="text-gray-400">
No ratings yet
</span>

@endif

</p>



{{-- PROJECT COUNT --}}
<p class="text-sm text-gray-600 mb-2">

Completed Projects:

<strong>
{{ $designer->completed_projects_count }}
</strong>

</p>



{{-- BADGE --}}
@if($badge)

@php
$badgeColor = match($badge) {
'top_rated' => 'bg-yellow-100 text-yellow-700',
'verified_designer' => 'bg-green-100 text-green-700',
'rising_talent' => 'bg-blue-100 text-blue-700',
default => 'bg-gray-100 text-gray-700'
};
@endphp

<div class="mb-3">

<span class="text-xs px-2 py-1 rounded-full {{ $badgeColor }}">

{{ str_replace('_',' ',ucfirst($badge)) }}

</span>

</div>

@endif



{{-- PROFILE BUTTON --}}
<a
href="{{ route('public.designers.show',$designer) }}"
class="inline-block bg-black text-white px-4 py-2 rounded-md text-sm hover:bg-gray-800 transition">

View Profile

</a>

</div>

@empty

<div class="col-span-3 text-center text-gray-500">

No designers found.

</div>

@endforelse

</div>



{{-- PAGINATION --}}
<div class="mt-12">

{{ $designers->links() }}

</div>

</div>

@endsection
