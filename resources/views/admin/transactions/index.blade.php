@extends('layouts.app')
@section('title', 'Transaction Debugger')
@section('subtitle', 'Read-only investigation of persisted payment and webhook records.')

@section('content')
<form method="GET" action="{{ route('admin.transactions.index') }}" class="card p-5 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="sm:col-span-2">
            <label for="q" class="label">Order, reference, UTR, provider transaction, or merchant</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" class="input" placeholder="Search stored transaction records">
            @error('q')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">All statuses</option>
                @foreach (['pending', 'review', 'paid', 'failed', 'expired'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="environment" class="label">Environment</label>
            <select id="environment" name="environment" class="input">
                <option value="">Live and Sandbox</option>
                <option value="live" @selected(($filters['environment'] ?? '') === 'live')>Live</option>
                <option value="sandbox" @selected(($filters['environment'] ?? '') === 'sandbox')>Sandbox</option>
            </select>
        </div>
        <div>
            <label for="provider" class="label">Provider</label>
            <select id="provider" name="provider" class="input">
                <option value="">All providers</option>
                <option value="sandbox" @selected(($filters['provider'] ?? '') === 'sandbox')>Internal TEST simulator</option>
                @foreach ($providers as $value => $provider)
                    <option value="{{ $value }}" @selected(($filters['provider'] ?? '') === $value)>{{ $provider['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="date_from" class="label">Created from</label>
            <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input">
        </div>
        <div>
            <label for="date_to" class="label">Created to</label>
            <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input">
        </div>
        <div class="flex items-end gap-2">
            <button class="btn btn-primary"><x-icon name="search" class="w-4 h-4" /> Search</button>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50/70"><tr><th class="th">Transaction</th><th class="th">Merchant</th><th class="th">Provider</th><th class="th">Amount</th><th class="th">Status</th><th class="th">Updated</th><th class="th"><span class="sr-only">View</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                    <tr class="hover:bg-slate-50/60">
                        <td class="td"><div class="font-mono text-xs font-semibold text-slate-900">{{ $order->order_no }}</div><div class="mt-1 text-xs text-slate-500">#{{ $order->id }} · {{ $order->client_order_id }}</div></td>
                        <td class="td"><div class="text-sm text-slate-900">{{ $order->user->displayName() }}</div><div class="text-xs text-slate-500">Merchant #{{ $order->user_id }}</div></td>
                        <td class="td text-xs text-slate-600">{{ $order->environment === 'sandbox' ? 'Internal TEST simulator' : ($order->paymentAccount?->providerLabel() ?? 'Unavailable') }}<div class="mt-1 font-semibold {{ $order->environment === 'sandbox' ? 'text-amber-700' : 'text-emerald-700' }}">{{ strtoupper($order->environment) }}</div></td>
                        <td class="td font-semibold tabular-nums">₹{{ number_format($order->amount, 2) }}</td>
                        <td class="td"><x-status :status="$order->status" /><div class="mt-1 text-xs text-slate-400">{{ $order->webhook_logs_count }} webhook {{ Str::plural('attempt', $order->webhook_logs_count) }}</div></td>
                        <td class="td text-xs text-slate-500 whitespace-nowrap">{{ $order->updated_at->format('d M Y, h:i A') }}</td>
                        <td class="td text-right"><a href="{{ route('admin.transactions.show', $order) }}" class="btn btn-secondary btn-sm"><x-icon name="eye" class="w-4 h-4" /> Inspect</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty icon="search" title="No transactions found" text="Adjust the filters and try again." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $orders->links() }}</div>@endif
</div>
@endsection
