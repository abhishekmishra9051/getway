@props(['name', 'size' => 'w-9 h-9 text-sm'])
@php
    $initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
    $palette = ['bg-brand-100 text-brand-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700'];
    $color = $palette[crc32($name) % count($palette)];
@endphp
<span {{ $attributes->merge(['class' => "inline-grid place-items-center rounded-full font-semibold shrink-0 $size $color"]) }}>{{ $initials ?: '?' }}</span>
