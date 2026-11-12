@extends('layouts.app')
@section('title', 'Create payment link')
@section('subtitle', 'Generate a UPI payment link and share it with your customer on WhatsApp, SMS or email.')
@section('back', route('orders.index'))

@section('content')
@if ($accounts->isEmpty())
    <div class="card">
        <x-empty icon="qr" title="Add a UPI account first" text="Payments are received directly in your own UPI account. Connect one to start creating payment links.">
            <a href="{{ route('accounts.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add UPI account</a>
        </x-empty>
    </div>
@else
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <form method="POST" action="{{ route('orders.store') }}" class="card p-6 lg:col-span-3">
        @csrf
        <div class="mb-6">
            <label for="f-amount" class="label">Amount</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xl font-semibold text-slate-400">₹</span>
                <input id="f-amount" type="number" name="amount" step="0.01" min="1" max="100000" value="{{ old('amount') }}" required autofocus data-amount-preview
                    class="input pl-10 py-3 text-2xl font-semibold" placeholder="0.00">
            </div>
            <p class="help">Between ₹1 and ₹1,00,000.</p>
        </div>

        <div class="mb-5">
            <label for="f-account" class="label">Receive in</label>
            <select id="f-account" name="payment_account_id" class="input">
                <option value="">Automatic ({{ \App\Models\User::ROTATION_MODES[auth()->user()->rotation_mode] ?? 'rotation' }}, respects daily limits)</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected(old('payment_account_id') == $account->id)>{{ $account->label }} — {{ $account->upi_id }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid sm:grid-cols-2 gap-x-4">
            <x-input name="customer_name" label="Customer name" placeholder="Optional" />
            <x-input name="customer_mobile" label="Customer mobile" placeholder="Optional" />
        </div>
        <x-input name="note" label="Payment note" maxlength="50" placeholder="e.g. Invoice #1024" help="Shown to the customer in their UPI app." />

        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 mt-2 pt-5">
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary"><x-icon name="link" class="w-4 h-4" /> Create link</button>
        </div>
    </form>

    <div class="lg:col-span-2">
        <div class="card p-6 bg-gradient-to-br from-slate-900 to-brand-950 text-white ring-0">
            <div class="text-xs font-semibold uppercase tracking-wider text-brand-200">Customer will see</div>
            <div class="mt-4 flex items-center gap-3">
                <x-avatar :name="auth()->user()->displayName()" class="!bg-white/10 !text-white" />
                <div>
                    <div class="text-sm text-slate-300">Paying to</div>
                    <div class="font-semibold">{{ auth()->user()->displayName() }}</div>
                </div>
            </div>
            <div id="preview-amount" class="mt-6 text-4xl font-bold tracking-tight">₹0.00</div>
            <div class="mt-6 grid grid-cols-2 gap-3 text-xs text-slate-300">
                <div class="flex items-center gap-2"><x-icon name="qr" class="w-4 h-4 text-brand-300" /> Dynamic QR</div>
                <div class="flex items-center gap-2"><x-icon name="phone" class="w-4 h-4 text-brand-300" /> All UPI apps</div>
                <div class="flex items-center gap-2"><x-icon name="clock" class="w-4 h-4 text-brand-300" /> Valid {{ config('gateway.order_expiry_minutes') }} min</div>
                <div class="flex items-center gap-2"><x-icon name="shield" class="w-4 h-4 text-brand-300" /> Direct to your bank</div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
