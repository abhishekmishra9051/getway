@extends('layouts.base')
@section('title', 'Sandbox Checkout')

@section('body')
<main class="min-h-full bg-amber-50 px-4 py-10">
    <div class="mx-auto max-w-2xl">
        <div class="mb-5 rounded-xl bg-amber-400 px-5 py-4 text-center text-sm font-bold tracking-widest text-amber-950">
            SANDBOX · TEST TRANSACTION · NO REAL MONEY
        </div>

        <section class="card p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Payment simulator</h1>
                    <p class="mt-1 text-sm text-slate-500">Choose one controlled outcome for this test order.</p>
                </div>
                <x-status :status="$order->status" />
            </div>

            <dl class="mt-6 grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">TEST order</dt><dd class="font-mono font-semibold">{{ $order->order_no }}</dd></div>
                <div><dt class="text-slate-500">Client reference</dt><dd class="font-mono font-semibold">{{ $order->client_order_id }}</dd></div>
                <div><dt class="text-slate-500">Amount</dt><dd class="font-semibold">₹{{ number_format($order->amount, 2) }}</dd></div>
                <div><dt class="text-slate-500">Customer</dt><dd>{{ $order->customer_name ?: 'Test customer' }}</dd></div>
            </dl>

            @if (session('status'))
                <div class="mt-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mt-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            @isset($timeout)
                <div class="mt-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">Simulated provider timeout. No external provider was contacted.</div>
            @endisset

            @if ($order->isPending() && ! $order->sandbox_simulated_at)
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        'success' => ['Simulate success', 'btn-success'],
                        'failed' => ['Simulate failure', 'btn-danger'],
                        'pending' => ['Keep pending', 'btn-secondary'],
                        'expired' => ['Simulate expiry', 'btn-secondary'],
                        'timeout' => ['Simulate timeout', 'btn-secondary'],
                        'invalid_reference' => ['Invalid reference', 'btn-danger'],
                    ] as $outcome => [$label, $class])
                        <form method="POST" action="{{ route('sandbox.pay.simulate', $order) }}">
                            @csrf
                            <input type="hidden" name="outcome" value="{{ $outcome }}">
                            <button class="btn {{ $class }} w-full">{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
            @else
                <div class="mt-6 rounded-lg bg-slate-100 px-4 py-3 text-center text-sm text-slate-600">
                    This TEST order is complete. Create another Sandbox order to simulate a different outcome.
                </div>
            @endif
        </section>
    </div>
</main>
@endsection
