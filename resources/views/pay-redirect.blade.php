@extends('layouts.base')
@section('title', 'Redirecting to secure checkout')
@section('body')
<div class="min-h-full flex flex-col items-center justify-center p-6 text-center bg-slate-100">
    <svg class="w-10 h-10 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="3"/>
        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
    </svg>
    <h1 class="mt-5 text-lg font-semibold text-slate-900">Taking you to secure checkout…</h1>
    <p class="mt-1 text-sm text-slate-500">Paying ₹{{ number_format($order->amount, 2) }} to {{ $order->user->displayName() }}</p>

    {{-- Paytm requires a form POST to its hosted payment page. --}}
    <form id="pg-form" method="POST" action="{{ $checkout['url'] }}" class="mt-6">
        @foreach ($checkout['fields'] ?? [] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <noscript><button class="btn btn-primary">Continue to payment</button></noscript>
    </form>
</div>
{{--
    The nonce is generated per-request by SecurityHeaders middleware and stored
    in the 'csp_nonce' view variable. It is included in script-src so this is
    the ONLY inline script permitted by the enforced CSP.
--}}
<script nonce="{{ $csp_nonce ?? '' }}">document.getElementById('pg-form').submit();</script>
@endsection
