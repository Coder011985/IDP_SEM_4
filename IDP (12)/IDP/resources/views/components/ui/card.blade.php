@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-6'
])

<div {{ $attributes->merge([
        'class' => "bg-white rounded-2xl shadow-sm border border-slate-200 $padding"
    ]) }}>

    @if($title || $subtitle)
        <div class="mb-5">
            @if($title)
                <h3 class="text-lg font-semibold text-slate-800">
                    {{ $title }}
                </h3>
            @endif

            @if($subtitle)
                <p class="text-sm text-slate-500 mt-1">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    {{ $slot }}

</div>
