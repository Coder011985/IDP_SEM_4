@extends('layouts.app')

@section('title', $designer->name . ' | Designer Profile')

@section('content')

<div class="max-w-6xl mx-auto px-6 py-16 space-y-12">


{{-- ================= DESIGNER HEADER ================= --}}
<div class="bg-white border rounded-2xl p-10 shadow-sm">

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">

<div class="flex items-center gap-5">

<div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-xl font-semibold">
{{ strtoupper(substr($designer->name,0,1)) }}
</div>

<div>

<h1 class="text-3xl font-bold flex items-center gap-2">

{{ $designer->name }}

@if($designer->is_verified)
<span class="text-green-600 text-sm font-medium">
✔ Verified
</span>
@endif

@if($designer->reputationBadge() === 'top_rated')
<span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded font-medium">
⭐ Top Rated
</span>
@endif

</h1>

<p class="text-gray-500 text-sm">
{{ $designer->specialization ?? 'Interior Designer' }}
</p>

</div>

</div>


<div class="text-right">

<p class="text-sm text-gray-500">
Average Rating
</p>

@if($averageRating)
<p class="text-2xl font-semibold text-yellow-500">
⭐ {{ number_format($averageRating,1) }}
</p>
@else
<p class="text-sm text-gray-400">
No rating yet
</p>
@endif

<p class="text-xs text-gray-400 mt-1">
{{ $completedProjects->count() }} Completed Projects
</p>

</div>

</div>


{{-- BIO --}}
<div class="border-t mt-8 pt-8">

<h3 class="text-lg font-semibold mb-4">
About Designer
</h3>

<p class="text-gray-600 leading-relaxed max-w-3xl">
{{ $designer->bio ?? 'This designer has not added a bio yet.' }}
</p>

</div>

</div>



{{-- ================= PORTFOLIO ================= --}}
<div class="bg-white border rounded-2xl p-10 shadow-sm">

<h3 class="text-xl font-semibold mb-8">
Portfolio
</h3>

@if($portfolio->count())

<div class="grid grid-cols-2 md:grid-cols-3 gap-6">

@foreach($portfolio as $item)

@php
$image = $item->images->first();
@endphp

@if($image)

<div class="group overflow-hidden rounded-xl border bg-gray-100">

<img
src="{{ asset('storage/'.$image->image_path) }}"
class="w-full h-48 object-cover transition duration-300 group-hover:scale-110 cursor-pointer"
loading="lazy"
onclick="openImage(this.src)"
/>

</div>

@endif

@endforeach

</div>

@else

<p class="text-gray-400 text-sm">
No portfolio added yet.
</p>

@endif

</div>

{{-- ================= RATING SUMMARY ================= --}}
<div class="bg-white border rounded-2xl p-10 shadow-sm">

<h3 class="text-xl font-semibold mb-8">
Rating Summary
</h3>

@if($averageRating)

<div class="flex items-center gap-8">

<div class="text-center">
<p class="text-4xl font-bold text-yellow-500">
{{ number_format($averageRating,1) }}
</p>
<p class="text-sm text-gray-500">
Average Rating
</p>
</div>

<div class="flex-1 space-y-3">

@php $totalRatings = array_sum($ratingStats); @endphp

@foreach($ratingStats as $star => $count)

<div class="flex items-center gap-3 text-sm">

<span class="w-6">{{ $star }}★</span>

<div class="flex-1 bg-gray-200 rounded h-2">

<div class="bg-yellow-400 h-2 rounded"
style="width: {{ $totalRatings ? ($count/$totalRatings)*100 : 0 }}%">
</div>

</div>

<span class="text-gray-500 w-6 text-right">
{{ $count }}
</span>

</div>

@endforeach

</div>

</div>

@else

<p class="text-gray-400 text-sm">
No ratings yet.
</p>

@endif

</div>

{{-- ================= CLIENT REVIEWS ================= --}}
<div class="bg-white border rounded-2xl p-10 shadow-sm">

<h3 class="text-xl font-semibold mb-8">
Client Reviews
</h3>

@if($reviews->count())

<div class="space-y-6">

@foreach($reviews as $review)

<div class="border rounded-xl p-6 bg-gray-50">

<div class="flex justify-between items-center mb-3">

<strong>
{{ $review->reviewer->name ?? 'Client' }}
</strong>

<span class="text-yellow-500 font-semibold">
⭐ {{ $review->rating }}
</span>

</div>

@if($review->review)

<p class="text-gray-600 text-sm leading-relaxed">
{{ $review->review }}
</p>

@endif

</div>

@endforeach

</div>

@else

<p class="text-gray-400 text-sm">
No reviews yet.
</p>

@endif

</div>



{{-- ================= COMPLETED PROJECTS ================= --}}
<div class="bg-white border rounded-2xl p-10 shadow-sm">

<h3 class="text-xl font-semibold mb-8">
Completed Projects
</h3>

@if($completedProjects->count())

<div class="space-y-4">

@foreach($completedProjects as $project)

<div class="border rounded-lg p-5 flex justify-between items-center hover:bg-gray-50 transition">

<div>

<h4 class="font-semibold">
{{ $project->title }}
</h4>

<p class="text-xs text-gray-500">
Completed Project
</p>

</div>

<span class="text-green-600 text-sm font-medium">
✔ Completed
</span>

</div>

@endforeach

</div>

@else

<p class="text-gray-400 text-sm">
This designer has no completed projects yet.
</p>

@endif

</div>



{{-- ================= ACTION BUTTONS ================= --}}
<div class="flex justify-between items-center">

<a href="{{ route('public.designers.index') }}"
class="text-sm text-gray-500 hover:underline">

← Back to Designers

</a>


@auth

@if(auth()->user()->role === 'client')

<div class="flex gap-3">

<form method="POST"
action="{{ route('public.designers.favorite',$designer) }}">
@csrf

<button
class="px-6 py-3 border rounded-lg text-sm hover:bg-gray-100 transition">
❤️ Save Designer
</button>

</form>

<a href="{{ route('public.designers.hire',$designer) }}"
class="bg-black text-white px-6 py-3 rounded-lg text-sm font-medium hover:opacity-90 transition">

Hire This Designer
</a>

</div>

@else

<a href="{{ route('dashboard') }}"
class="bg-black text-white px-6 py-3 rounded-lg text-sm font-medium hover:opacity-90 transition">

Go to Dashboard

</a>

@endif

@else

<a href="{{ route('login') }}"
class="bg-black text-white px-6 py-3 rounded-lg text-sm font-medium hover:opacity-90 transition">

Login to Hire

</a>

@endauth

</div>


</div>

<div id="imageLightbox"
class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50"
onclick="closeImage()">

<img id="lightboxImage"
class="max-w-5xl max-h-[90vh] rounded-lg shadow-lg"
onclick="event.stopPropagation()">

<button onclick="closeImage()"
class="absolute top-6 right-6 text-white text-3xl font-bold">
✕
</button>

</div>

<script>

function openImage(src){
    const lightbox = document.getElementById('imageLightbox');
    const img = document.getElementById('lightboxImage');

    img.src = src;
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
}

function closeImage(){
    const lightbox = document.getElementById('imageLightbox');
    lightbox.classList.remove('flex');
    lightbox.classList.add('hidden');
}

</script>

@endsection
