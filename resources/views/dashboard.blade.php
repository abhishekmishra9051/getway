@extends('layouts.app')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $inr = fn ($v) => '₹'.number_format((float) $v, 2);
    $done = collect($checklist)->where(1, true)->count();
@endphp
@section('title', $greeting.', '.Str::before(auth()->user()->name, ' '))
@section('subtitle', 'Here is what is happening with your payments today.')
@section('actions')
    <a href="{{ route('orders.index', ['environment' => $environment]) }}" class="btn btn-secondary">View orders</a>
    @if ($environment === 'live')<a href="{{ route('orders.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Payment link</a>@endif
@endsection

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <div class="inline-flex rounded-lg bg-slate-100 p-1 text-sm font-medium">
        <a href="{{ route('dashboard', ['environment' => 'live']) }}" class="rounded-md px-4 py-2 {{ $environment === 'live' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500' }}">Live</a>
        <a href="{{ route('dashboard', ['environment' => 'sandbox']) }}" class="rounded-md px-4 py-2 {{ $environment === 'sandbox' ? 'bg-amber-400 text-amber-950 shadow-sm' : 'text-slate-500' }}">Sandbox · TEST</a>
    </div>
    @if ($environment === 'sandbox')
        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold tracking-wide text-amber-800">SIMULATED DATA</span>
    @endif
</div>
@if ($stats['review_count'])
    <a href="{{ route('orders.index', ['environment' => $environment, 'status' => 'review']) }}" class="mb-6 flex items-center gap-4 rounded-xl bg-sky-50 ring-1 ring-sky-200 px-5 py-4 hover:bg-sky-100/70 transition">
        <span class="grid place-items-center w-10 h-10 rounded-lg bg-sky-500 text-white shrink-0"><x-icon name="shield" class="w-5 h-5" /></span>
        <div class="flex-1">
            <div class="font-semibold text-sky-900">{{ $stats['review_count'] }} {{ Str::plural('payment', $stats['review_count']) }} waiting for your review</div>
            <div class="text-sm text-sky-800/80">Customers submitted UTRs. Match them with your bank/UPI app to approve.</div>
        </div>
        <x-icon name="arrow-right" class="w-5 h-5 text-sky-700" />
    </a>
@endif

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @foreach ([
        [$environment === 'sandbox' ? 'Simulated today' : 'Collected today', $inr($stats['today_amount']), $stats['today_count'].' '.Str::plural('payment', $stats['today_count']), 'banknotes', 'bg-emerald-50 text-emerald-600'],
        [$environment === 'sandbox' ? 'Total simulated' : 'Total collected', $inr($stats['total_amount']), $stats['paid_count'].' successful '.Str::plural('payment', $stats['paid_count']), 'chart', 'bg-brand-50 text-brand-600'],
        ['Pending orders', $stats['pending_count'], 'Waiting for customer payment', 'clock', 'bg-amber-50 text-amber-600'],
        ['Success rate', $stats['success_rate'] === null ? '—' : $stats['success_rate'].'%', 'Paid vs expired orders', 'check-circle', 'bg-sky-50 text-sky-600'],
    ] as [$label, $value, $hint, $icon, $tone])
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-slate-500">{{ $label }}</div>
                <div class="grid place-items-center w-9 h-9 rounded-lg {{ $tone }}"><x-icon :name="$icon" class="w-5 h-5" /></div>
            </div>
            <div class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</div>
            <div class="mt-1 text-xs text-slate-500">{{ $hint }}</div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- 7-day chart --}}
    <div class="card xl:col-span-2">
        <div class="card-header">
            <div>
                <div class="card-title">{{ $environment === 'sandbox' ? 'Simulated payments' : 'Collections' }} · last 7 days</div>
                <div class="text-2xl font-bold tracking-tight text-slate-900 mt-1">{{ $inr($weekTotal) }}</div>
            </div>
        </div>
        @php $max = max($chart->max('amount'), 1); @endphp
        <div class="px-5 pt-6 pb-5">
            <div class="flex items-end gap-3 h-48">
                @foreach ($chart as $day)
                    @php $h = $day['amount'] > 0 ? max(4, $day['amount'] / $max * 100) : 0; @endphp
                    <div class="group relative flex-1 h-full flex flex-col justify-end items-center">
                        <div class="absolute -top-1 left-1/2 -translate-x-1/2 -translate-y-full opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap rounded-md bg-slate-900 text-white text-xs px-2 py-1 z-10">
                            {{ $day['date'] }} · {{ $inr($day['amount']) }} · {{ $day['count'] }} paid
                        </div>
                        <div class="w-full max-w-12 rounded-t-md {{ $loop->last ? 'bg-brand-600' : 'bg-brand-200 group-hover:bg-brand-400' }} transition-all" style="height: {{ $h }}%"></div>
                        <div class="w-full max-w-12 h-px bg-slate-200"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-3 mt-2">
                @foreach ($chart as $day)
                    <div class="flex-1 text-center text-xs {{ $loop->last ? 'font-semibold text-slate-900' : 'text-slate-500' }}">{{ $loop->last ? 'Today' : $day['label'] }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Setup checklist --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Getting started</div>
            <span class="text-xs font-medium text-slate-500">{{ $done }}/{{ count($checklist) }} done</span>
        </div>
        <div class="px-5 pt-4">
            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $done / count($checklist) * 100 }}%"></div>
            </div>
        </div>
        <ul class="p-3">
            @foreach ($checklist as [$label, $isDone, $url])
                <li>
                    <a @if (! $isDone && $url) href="{{ $url }}" @endif class="flex items-center gap-3 rounded-lg px-2 py-2.5 {{ ! $isDone && $url ? 'hover:bg-slate-50' : '' }}">
                        @if ($isDone)
                            <span class="grid place-items-center w-6 h-6 rounded-full bg-emerald-500 text-white"><x-icon name="check" class="w-3.5 h-3.5" /></span>
                            <span class="text-sm text-slate-400 line-through">{{ $label }}</span>
                        @else
                            <span class="w-6 h-6 rounded-full ring-2 ring-inset ring-slate-300"></span>
                            <span class="text-sm font-medium text-slate-800 flex-1">{{ $label }}</span>
                            <x-icon name="arrow-right" class="w-4 h-4 text-slate-400" />
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- Recent orders --}}
<div class="card overflow-hidden">
    <div class="card-header">
        <div class="card-title">Recent orders</div>
        <a href="{{ route('orders.index', ['environment' => $environment]) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 inline-flex items-center gap-1">View all <x-icon name="arrow-right" class="w-4 h-4" /></a>
    </div>
    @include('orders._table', ['orders' => $recentOrders])
</div>
@endsection
