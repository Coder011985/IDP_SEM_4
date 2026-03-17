@props([
    'type' => 'primary',
    'size' => 'md'
])

@php
    $base = 'inline-flex items-center justify-center font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    $types = [
        'primary'   => 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500',
        'secondary' => 'bg-slate-200 hover:bg-slate-300 text-slate-800 focus:ring-slate-400',
        'danger'    => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'outline'   => 'border border-slate-300 text-slate-700 hover:bg-slate-100 focus:ring-slate-400',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-5 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $typeClass = $types[$type] ?? $types['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<button {{ $attributes->merge(['class' => "$base $typeClass $sizeClass"]) }}>
    {{ $slot }}
</button>
