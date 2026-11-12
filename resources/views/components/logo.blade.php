@props(['dark' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="grid place-items-center w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 shadow-sm shadow-brand-600/30">
        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="white"><path d="M13.5 2 4 14h6.5L9.5 22 19 10h-6.5z"/></svg>
    </span>
    <span class="font-bold tracking-tight text-[17px] {{ $dark ? 'text-white' : 'text-slate-900' }}">{{ config('app.name') }}</span>
</span>
