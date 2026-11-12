@extends('layouts.app')
@section('title', 'Transaction '.$order->order_no)
@section('subtitle', 'Read-only debugger · No provider request is made from this page.')
@section('back', route('admin.transactions.index'))

@section('content')
<div class="mb-6 rounded-xl px-5 py-4 ring-1 {{ $order->environment === 'sandbox' ? 'bg-amber-50 text-amber-900 ring-amber-200' : 'bg-emerald-50 text-emerald-900 ring-emerald-200' }}">
    <div class="flex items-center gap-2 text-sm font-semibold"><x-icon name="shield" class="w-5 h-5" /> {{ strtoupper($order->environment) }}{{ $order->environment === 'sandbox' ? ' · TEST TRANSACTION' : ' · LIVE TRANSACTION' }}</div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="card">
            <div class="card-header"><div class="card-title">Transaction details</div><x-status :status="$order->status" /></div>
            @php
                $details = [
                    'Database order ID' => '#'.$order->id,
                    'Order number' => $order->order_no,
                    'Merchant reference' => $order->client_order_id,
                    'Merchant' => $order->user->displayName().' (#'.$order->user_id.')',
                    'Amount' => '₹'.number_format($order->amount, 2).' INR',
                    'Payment method' => $order->environment === 'sandbox' ? 'Internal TEST simulator' : ($order->paymentAccount?->providerLabel() ?? 'Unavailable'),
                    'Payment account' => $order->paymentAccount?->label,
                    'Environment' => strtoupper($order->environment),
                    'UTR' => $order->utr,
                    'Submitted UTR' => $order->customer_utr,
                    'Provider transaction ID' => $order->provider_txn_id,
                    'Verified by' => $order->verified_by,
                    'Created' => $order->created_at->format('d M Y, h:i:s A'),
                    'Last updated' => $order->updated_at->format('d M Y, h:i:s A'),
                    'Last verification attempt' => $order->last_checked_at?->format('d M Y, h:i:s A'),
                ];
            @endphp
            <dl class="grid grid-cols-1 sm:grid-cols-2">
                @foreach ($details as $label => $value)
                    <div class="px-5 py-3.5 border-b border-slate-100 {{ $loop->odd ? 'sm:border-r' : '' }}"><dt class="text-xs font-medium text-slate-500">{{ $label }}</dt><dd class="mt-1 text-sm text-slate-900 break-all">{{ $value ?: 'Not recorded' }}</dd></div>
                @endforeach
            </dl>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Masked customer information</div><x-icon name="lock" class="w-5 h-5 text-slate-400" /></div>
            <dl class="grid grid-cols-1 sm:grid-cols-3">
                @foreach (['Name' => $maskedName, 'Email' => $maskedEmail, 'Mobile' => $maskedMobile] as $label => $value)
                    <div class="px-5 py-4 border-b border-slate-100"><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-1 text-sm text-slate-900">{{ $value ?: 'Not recorded' }}</dd></div>
                @endforeach
            </dl>
        </div>

        @if ($providerError)
            <div class="rounded-xl bg-red-50 p-5 text-red-900 ring-1 ring-red-200"><div class="flex items-center gap-2 font-semibold"><x-icon name="alert" class="w-5 h-5" /> Recorded error</div><p class="mt-2 text-sm break-words">{{ $providerError }}</p></div>
        @endif

        <div class="card overflow-hidden">
            <div class="card-header"><div class="card-title">Webhook delivery attempts</div></div>
            @if ($order->webhookLogs->isEmpty())
                <x-empty icon="link" title="No webhook attempts recorded" text="This transaction may not have emitted an event or no matching webhook URL was configured." />
            @else
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-100"><thead class="bg-slate-50"><tr><th class="th">Event</th><th class="th">Attempt</th><th class="th">Result</th><th class="th">Time</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @foreach ($order->webhookLogs as $log)<tr><td class="td font-mono text-xs">{{ $log->event }}</td><td class="td">#{{ $log->attempt }}</td><td class="td"><span class="text-sm {{ $log->success ? 'text-emerald-700' : 'text-red-700' }}">{{ $log->response_code ?? 'No response' }}</span>@if($log->error_label)<div class="text-xs text-slate-500">{{ $log->error_label }}</div>@endif</td><td class="td text-xs whitespace-nowrap">{{ $log->created_at->format('d M Y, h:i:s A') }}</td></tr>@endforeach
                </tbody></table></div>
            @endif
        </div>
    </div>

    <div class="card self-start">
        <div class="card-header"><div class="card-title">Debug timeline</div></div>
        <ol class="p-5 space-y-5">
            @foreach ($timeline as $event)
                <li class="relative flex gap-3">
                    @unless ($loop->last)<span class="absolute left-4 top-8 -bottom-5 w-px bg-slate-200"></span>@endunless
                    <span class="grid place-items-center w-8 h-8 rounded-full bg-slate-100 text-slate-600 shrink-0"><x-icon :name="$event['icon']" class="w-4 h-4" /></span>
                    <div class="pt-1 min-w-0"><div class="text-sm font-medium text-slate-900">{{ $event['label'] }}</div>@if($event['detail'])<div class="mt-1 text-xs text-slate-500 break-words">{{ $event['detail'] }}</div>@endif<div class="mt-1 text-xs text-slate-400">{{ $event['time']?->format('d M Y, h:i:s A') ?? 'Exact time not recorded' }}</div></div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
@endsection
