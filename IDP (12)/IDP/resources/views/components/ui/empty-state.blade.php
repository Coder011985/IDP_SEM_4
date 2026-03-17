@props([
    'title' => 'No data available',
    'description' => null
])

<div class="flex flex-col items-center justify-center text-center py-16 px-6 border border-dashed border-slate-300 rounded-2xl bg-white">

    <div class="mb-4">
        <div class="w-14 h-14 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 text-xl">
            📂
        </div>
    </div>

    <h3 class="text-lg font-semibold text-slate-800">
        {{ $title }}
    </h3>

    @if($description)
        <p class="text-sm text-slate-500 mt-2 max-w-md">
            {{ $description }}
        </p>
    @endif

    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif

</div>
