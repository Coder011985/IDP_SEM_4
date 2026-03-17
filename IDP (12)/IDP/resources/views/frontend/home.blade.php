@extends('layouts.app')

@section('content')

<!-- Navbar -->
<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">IDP</h1>

        <div class="space-x-6">
            <a href="#" class="text-gray-600 hover:text-black">Home</a>
            <a href="#" class="text-gray-600 hover:text-black">Designers</a>
            <a href="#" class="text-gray-600 hover:text-black">Projects</a>
            <a href="#" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800">
                Login
            </a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="bg-gray-100 min-h-screen flex items-center">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">

        <div>
            <h2 class="text-5xl font-bold text-gray-900 leading-tight">
                Interior Design Portal
            </h2>

            <p class="mt-6 text-lg text-gray-600">
                Connect Clients with Professional Designers.
                Manage projects, track progress, and collaborate easily.
            </p>

            <div class="mt-8 space-x-4">
                <a href="#" class="bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800">
                    Get Started
                </a>

                <a href="#" class="border border-black px-6 py-3 rounded-lg hover:bg-black hover:text-white transition">
                    Explore Designers
                </a>
            </div>
        </div>

        <div>
            <img src="https://images.unsplash.com/photo-1618220179428-22790b461013"
                 class="rounded-2xl shadow-lg"
                 alt="Interior Design">
        </div>

    </div>
</section>

@endsection
