<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Interior Design Portal (IDP) – Hire Professional Interior Designers</title>

@vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="bg-gray-50 text-gray-900 flex flex-col min-h-screen">


<!-- ================= NAVBAR ================= -->

<header class="bg-white border-b sticky top-0 z-50">

<div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

<div class="text-xl font-bold tracking-tight">
IDP
</div>

<nav class="flex items-center gap-6 text-sm font-medium text-gray-600">

<a href="/" class="hover:text-black">Home</a>

<a href="{{ route('public.designers.index') }}" class="hover:text-black">
Browse Designers
</a>

<a href="#features" class="hover:text-black">
Features
</a>

<a href="#workflow" class="hover:text-black">
How It Works
</a>

@auth

<span class="text-gray-500 hidden md:block">
{{ Auth::user()->name }}
</span>

<a href="{{ route('dashboard') }}"
class="bg-black text-white px-4 py-2 rounded-md hover:opacity-90">
Dashboard
</a>

@else

<a href="{{ route('login') }}" class="hover:text-black">
Login
</a>

<a href="{{ route('register') }}"
class="bg-black text-white px-4 py-2 rounded-md hover:opacity-90">
Register
</a>

@endauth

</nav>

</div>

</header>



<main class="flex-grow">

<!-- ================= HERO ================= -->

<section class="max-w-7xl mx-auto px-6 py-24">

<div class="grid lg:grid-cols-2 gap-16 items-center">

<div>

<h1 class="text-4xl lg:text-5xl font-bold leading-tight mb-6">
Secure Milestone-Based Interior Project Management
</h1>

<p class="text-gray-600 text-lg mb-8 max-w-xl leading-relaxed">

Connect with professional interior designers and manage projects
through secure milestone payments, transparent bidding,
and structured approvals.

</p>

<div class="flex gap-4 flex-wrap">

<a href="{{ route('register') }}"
class="bg-black text-white px-6 py-3 rounded-md font-medium hover:opacity-90">
Start as Client
</a>

<a href="{{ route('register') }}?role=designer"
class="border border-black px-6 py-3 rounded-md font-medium hover:bg-black hover:text-white">
Start as Designer
</a>

<a href="{{ route('public.designers.index') }}"
class="border border-gray-300 px-6 py-3 rounded-md font-medium hover:bg-gray-100">
Browse Designers
</a>

</div>

</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">

<img
src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6"
class="w-full h-[420px] object-cover"
alt="Interior Design">

</div>

</div>

</section>



<!-- ================= PLATFORM STATS ================= -->

@if(isset($stats))

<section class="bg-gray-100 py-16 border-t">

<div class="max-w-7xl mx-auto px-6">

<div class="grid md:grid-cols-4 gap-8 text-center">

<div class="bg-white p-6 rounded-lg shadow">
<p class="text-3xl font-bold text-blue-600">
{{ $stats['designers'] }}
</p>
<p class="text-sm text-gray-500 mt-2">Designers</p>
</div>

<div class="bg-white p-6 rounded-lg shadow">
<p class="text-3xl font-bold text-indigo-600">
{{ $stats['projects'] }}
</p>
<p class="text-sm text-gray-500 mt-2">Projects Posted</p>
</div>

<div class="bg-white p-6 rounded-lg shadow">
<p class="text-3xl font-bold text-emerald-600">
{{ $stats['completed'] }}
</p>
<p class="text-sm text-gray-500 mt-2">Completed Projects</p>
</div>

<div class="bg-white p-6 rounded-lg shadow">
<p class="text-3xl font-bold text-purple-600">
₹{{ number_format($stats['revenue']) }}
</p>
<p class="text-sm text-gray-500 mt-2">Platform Revenue</p>
</div>

</div>

</div>

</section>

@endif



<!-- ================= TOP RATED DESIGNERS ================= -->

@if(isset($topRated) && $topRated->count())

<section class="bg-white py-20 border-t">

<div class="max-w-7xl mx-auto px-6">

<div class="text-center mb-14">

<h2 class="text-3xl font-semibold mb-3">
🥇 Top Rated Designers
</h2>

<p class="text-gray-500">
Highest rated designers on our platform
</p>

</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

@foreach($topRated as $designer)

<div class="bg-gray-50 border rounded-xl p-6">

<h3 class="font-semibold text-lg flex items-center gap-2">
{{ $designer->name }}

@if($designer->availabilityStatus() === 'online')
<span class="text-green-600 text-xs">🟢 Online</span>

@elseif($designer->availabilityStatus() === 'recent')
<span class="text-yellow-500 text-xs">🟡 Active</span>

@else
<span class="text-gray-400 text-xs">⚫ Offline</span>
@endif

</h3>

<p class="text-sm text-gray-500">
{{ ucfirst($designer->specialization ?? 'Interior Designer') }}
</p>

<p class="text-yellow-500 font-semibold mt-2">
⭐ {{ number_format($designer->received_reviews_avg_rating ?? 0, 1) }}/5
</p>

<a href="{{ route('public.designers.show',$designer->id) }}"
class="inline-block mt-3 text-blue-600 text-sm hover:underline">
View Profile →
</a>

</div>

@endforeach

</div>

</div>

</section>

@endif

<!-- ================= ONLINE DESIGNERS ================= -->

@if(isset($onlineDesigners) && $onlineDesigners->count())

<section class="bg-green-50 py-20 border-t">

<div class="max-w-7xl mx-auto px-6">

<div class="text-center mb-14">

<h2 class="text-3xl font-semibold mb-3">
🟢 Designers Online Right Now
</h2>

<p class="text-gray-500">
Currently active interior designers on the platform
</p>

</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

@foreach($onlineDesigners as $designer)

<div class="bg-white border rounded-xl p-6">

<h3 class="font-semibold text-lg flex items-center gap-2">
{{ $designer->name }}

<span class="text-green-600 text-xs">🟢 Online</span>

</h3>

<p class="text-sm text-gray-500">
{{ ucfirst($designer->specialization ?? 'Interior Designer') }}
</p>

<a href="{{ route('public.designers.show',$designer->id) }}"
class="inline-block mt-3 text-blue-600 text-sm hover:underline">
View Profile →
</a>

</div>

@endforeach

</div>

</div>

</section>

@endif

<!-- ================= TRENDING DESIGNERS ================= -->

@if(isset($trending) && $trending->count())

<section class="bg-gray-50 py-20 border-t">

<div class="max-w-7xl mx-auto px-6">

<div class="text-center mb-14">

<h2 class="text-3xl font-semibold mb-3">
🔥 Trending Designers
</h2>

<p class="text-gray-500">
Most active designers based on projects & reviews
</p>

</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

@foreach($trending as $designer)

<div class="bg-white border rounded-xl p-6">

<h3 class="font-semibold text-lg flex items-center gap-2">
{{ $designer->name }}

@if($designer->availabilityStatus() === 'online')
<span class="text-green-600 text-xs">🟢 Online</span>

@elseif($designer->availabilityStatus() === 'recent')
<span class="text-yellow-500 text-xs">🟡 Active</span>

@else
<span class="text-gray-400 text-xs">⚫ Offline</span>
@endif

</h3>

<p class="text-sm text-gray-500">
{{ ucfirst($designer->specialization ?? 'Interior Designer') }}
</p>

<a href="{{ route('public.designers.show',$designer->id) }}"
class="inline-block mt-3 text-blue-600 text-sm hover:underline">
View Profile →
</a>

</div>

@endforeach

</div>

</div>

</section>

@endif



<!-- ================= RISING TALENT DESIGNERS ================= -->

@if(isset($rising) && $rising->count())

<section class="bg-white py-20 border-t">

<div class="max-w-7xl mx-auto px-6">

<div class="text-center mb-14">

<h2 class="text-3xl font-semibold mb-3">
🚀 Rising Talent Designers
</h2>

<p class="text-gray-500">
New designers gaining popularity
</p>

</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

@foreach($rising as $designer)

<div class="bg-gray-50 border rounded-xl p-6">

<h3 class="font-semibold text-lg flex items-center gap-2">
{{ $designer->name }}

@if($designer->availabilityStatus() === 'online')
<span class="text-green-600 text-xs">🟢 Online</span>

@elseif($designer->availabilityStatus() === 'recent')
<span class="text-yellow-500 text-xs">🟡 Active</span>

@else
<span class="text-gray-400 text-xs">⚫ Offline</span>
@endif

</h3>

<p class="text-sm text-gray-500">
{{ ucfirst($designer->specialization ?? 'Interior Designer') }}
</p>

<a href="{{ route('public.designers.show',$designer->id) }}"
class="inline-block mt-3 text-blue-600 text-sm hover:underline">
View Profile →
</a>

</div>

@endforeach

</div>

</div>

</section>

@endif

<!-- ================= RECOMMENDED DESIGNERS ================= -->

@if(isset($recommended) && $recommended->count())

<section class="bg-gray-50 py-20 border-t">

<div class="max-w-7xl mx-auto px-6">

<div class="text-center mb-14">

<h2 class="text-3xl font-semibold mb-3">
⭐ Recommended Designers
</h2>

<p class="text-gray-500">
Best performing designers based on rating and project success
</p>

</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

@foreach($recommended as $designer)

<div class="bg-white border rounded-xl p-6">

<h3 class="font-semibold text-lg flex items-center gap-2">
{{ $designer->name }}

@if($designer->availabilityStatus() === 'online')
<span class="text-green-600 text-xs">🟢 Online</span>
@endif

</h3>

<p class="text-sm text-gray-500">
{{ ucfirst($designer->specialization ?? 'Interior Designer') }}
</p>

<p class="text-yellow-500 font-semibold mt-2">
⭐ {{ number_format($designer->received_reviews_avg_rating ?? 0,1) }}/5
<span class="text-xs text-gray-400">
({{ $designer->reviews_count ?? 0 }} reviews)
</span>
</p>

<a href="{{ route('public.designers.show',$designer->id) }}"
class="inline-block mt-3 text-blue-600 text-sm hover:underline">
View Profile →
</a>

</div>

@endforeach

</div>

</div>

</section>

@endif

<!-- ================= LIVE ACTIVITY FEED ================= -->

@if(isset($activities) && $activities->count())

<section class="bg-gray-100 py-20 border-t">

<div class="max-w-5xl mx-auto px-6">

<div class="text-center mb-12">

<h2 class="text-3xl font-semibold mb-3">
🔥 Live Platform Activity
</h2>

<p class="text-gray-500">
Recent activity happening across the platform
</p>

</div>

<div id="activityFeed" class="space-y-4">

@foreach($activities as $activity)

<div class="bg-white p-4 rounded-lg shadow flex justify-between items-center">

<div class="text-sm text-gray-700">

<strong>{{ $activity->user->name ?? 'User' }}</strong>

{{ $activity->description }}

@if(optional($activity->project)->title)
<span class="text-blue-600">
({{ optional($activity->project)->title }})
</span>
@endif

</div>

<div class="text-xs text-gray-400">
{{ $activity->created_at->diffForHumans() }}
</div>

</div>

@endforeach

</div>

</div>

</section>

@endif

<!-- ================= FEATURES ================= -->

<section id="features" class="bg-white py-20 border-t">

<div class="max-w-7xl mx-auto px-6">

<div class="text-center mb-14">

<h2 class="text-3xl font-semibold mb-3">
Built for Structured Project Execution
</h2>

<p class="text-gray-500">
Professional lifecycle control with secure financial workflows.
</p>

</div>

<div class="grid md:grid-cols-3 gap-10">

<div class="p-6 bg-gray-50 rounded-lg border">
<h3 class="font-semibold mb-3">Milestone Escrow</h3>
<p class="text-sm text-gray-600">
Funds are secured before work begins and released after approval.
</p>
</div>

<div class="p-6 bg-gray-50 rounded-lg border">
<h3 class="font-semibold mb-3">Role Dashboards</h3>
<p class="text-sm text-gray-600">
Dedicated dashboards for Clients, Designers and Admin.
</p>
</div>

<div class="p-6 bg-gray-50 rounded-lg border">
<h3 class="font-semibold mb-3">Revenue Tracking</h3>
<p class="text-sm text-gray-600">
Automated commission and financial reporting.
</p>
</div>

</div>

</div>

</section>



<!-- ================= WORKFLOW ================= -->

<section id="workflow" class="py-20 bg-gray-50 border-t">

<div class="max-w-6xl mx-auto px-6 text-center">

<h2 class="text-3xl font-semibold mb-12">
How the Milestone Workflow Works
</h2>

<div class="grid md:grid-cols-3 gap-10">

<div>
<div class="text-3xl font-bold mb-4">1</div>
<h4 class="font-semibold mb-2">Bid & Assign</h4>
<p class="text-sm text-gray-600">
Designers submit proposals and clients choose the best fit.
</p>
</div>

<div>
<div class="text-3xl font-bold mb-4">2</div>
<h4 class="font-semibold mb-2">Fund Milestone</h4>
<p class="text-sm text-gray-600">
Clients fund each milestone securely before work begins.
</p>
</div>

<div>
<div class="text-3xl font-bold mb-4">3</div>
<h4 class="font-semibold mb-2">Release Payment</h4>
<p class="text-sm text-gray-600">
Admin verifies and releases funds to designers.
</p>
</div>

</div>

</div>

</section>



<!-- ================= CTA ================= -->

<section class="bg-black text-white py-20">

<div class="max-w-5xl mx-auto px-6 text-center">

<h2 class="text-3xl font-semibold mb-6">
Start Your Interior Design Project Today
</h2>

<p class="text-gray-300 mb-8">
Post a project or hire professional designers through our
secure milestone escrow platform.
</p>

<div class="flex justify-center gap-4 flex-wrap">

<a href="{{ route('register') }}"
class="bg-white text-black px-6 py-3 rounded-md font-medium hover:opacity-90">
Create Free Account
</a>

<a href="{{ route('public.designers.index') }}"
class="border border-white px-6 py-3 rounded-md hover:bg-white hover:text-black">
Browse Designers
</a>

</div>

</div>

</section>


</main>



<!-- ================= FOOTER ================= -->

<footer class="bg-gray-900 text-white text-center py-6 text-sm">

© {{ date('Y') }} Interior Design Portal (IDP)
Secure Milestone Escrow Platform.

</footer>

<script>

if(window.Echo){

Echo.channel('activity')
.listen('.activity.created', (e) => {

    const feed = document.getElementById('activityFeed');
    if(!feed) return;

    const activity = e.activity;

    const html = `
    <div class="bg-white p-4 rounded-lg shadow flex justify-between items-center">

        <div class="text-sm text-gray-700">

            <strong>${activity.user?.name ?? 'User'}</strong>

            ${activity.description ?? ''}

            ${activity.project?.title
                ? `<span class="text-blue-600">(${activity.project.title})</span>`
                : ''}

        </div>

        <div class="text-xs text-gray-400">
            just now
        </div>

    </div>
    `;

    feed.insertAdjacentHTML('afterbegin', html);

    // keep only latest 10 items
    if(feed.children.length > 10){
        feed.removeChild(feed.lastElementChild);
    }

});

}

</script>

</body>
</html>
