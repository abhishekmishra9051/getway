@extends('layouts.app')
@section('title', 'Orders')
@section('subtitle', $environment === 'sandbox' ? 'Simulated TEST payments, isolated from Live transactions.' : 'Every Live payment request created from the API or dashboard.')
@section('actions')
    <a href="{{ route('orders.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Payment link</a>
@endsection

@section('content')
<div class="mb-5 inline-flex rounded-lg bg-slate-100 p-1 text-sm font-medium">
    <a href="{{ route('orders.index', ['environment' => 'live']) }}" class="rounded-md px-4 py-2 {{ $environment === 'live' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500' }}">Live</a>
    <a href="{{ route('orders.index', ['environment' => 'sandbox']) }}" class="rounded-md px-4 py-2 {{ $environment === 'sandbox' ? 'bg-amber-400 text-amber-950 shadow-sm' : 'text-slate-500' }}">Sandbox · TEST</a>
</div>
@if ($environment === 'sandbox')
    <div class="mb-5 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200">Sandbox orders never affect Live balances, limits, or provider accounts.</div>
@endif
@php
    $current = request('status', '');
    $tabs = ['' => ['All', $counts->sum()], 'review' => ['Needs review', $counts['review'] ?? 0], 'paid' => ['Paid', $counts['paid'] ?? 0], 'pending' => ['Pending', $counts['pending'] ?? 0], 'failed' => ['Failed', $counts['failed'] ?? 0], 'expired' => ['Expired', $counts['expired'] ?? 0]];
@endphp
<div class="card overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-5 py-3 border-b border-slate-100">
        <nav class="flex gap-1 overflow-x-auto -mx-1">
            @foreach ($tabs as $value => [$label, $count])
                <a href="{{ route('orders.index', array_filter(['environment' => $environment, 'status' => $value, 'q' => request('q')])) }}" @class([
                    'inline-flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition',
                    'bg-slate-900 text-white' => $current === $value,
                    'text-slate-600 hover:bg-slate-100' => $current !== $value,
                ])>
                    {{ $label }}
                    <span @class(['rounded-full px-1.5 text-xs tabular-nums', 'bg-white/20' => $current === $value, 'bg-slate-100 text-slate-500' => $current !== $value])>{{ $count }}</span>
                </a>
            @endforeach
        </nav>
        <form class="relative md:w-80">
            <input type="hidden" name="environment" value="{{ $environment }}">
            @if ($current)<input type="hidden" name="status" value="{{ $current }}">@endif
            <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input name="q" value="{{ request('q') }}" placeholder="Search order ID, reference or UTR" class="input pl-9">
        </form>
    </div>

    @include('orders._table')

    @if ($orders->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
