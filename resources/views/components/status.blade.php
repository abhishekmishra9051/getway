@props(['status'])
@php
    [$classes, $dot] = [
        'paid' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
        'pending' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500'],
        'expired' => ['bg-slate-100 text-slate-600 ring-slate-500/20', 'bg-slate-400'],
        'review' => ['bg-sky-50 text-sky-700 ring-sky-600/20', 'bg-sky-500'],
        'failed' => ['bg-red-50 text-red-700 ring-red-600/20', 'bg-red-500'],
    ][$status] ?? ['bg-slate-100 text-slate-700 ring-slate-500/20', 'bg-slate-400'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset $classes"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>{{ $status === 'review' ? 'Under review' : ucfirst($status) }}
</span>
