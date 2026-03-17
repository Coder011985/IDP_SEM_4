@props([
    'type' => 'default'
])

@php
    $base = 'px-3 py-1 text-xs font-medium rounded-full';

    $styles = [
        'pending'   => 'bg-amber-100 text-amber-700',
        'approved'  => 'bg-blue-100 text-blue-700',
        'assigned'  => 'bg-indigo-100 text-indigo-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'released'  => 'bg-green-100 text-green-700',
        'rejected'  => 'bg-red-100 text-red-700',
        'default'   => 'bg-slate-100 text-slate-700',
    ];

    $class = $styles[$type] ?? $styles['default'];
@endphp

<span {{ $attributes->merge(['class' => "$base $class"]) }}>
    {{ $slot }}
</span>
