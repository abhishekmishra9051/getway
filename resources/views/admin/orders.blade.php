@extends('layouts.app')
@section('title', 'All orders')
@section('subtitle', 'Orders across every merchant on the platform.')

@section('content')
@php $current = request('status', ''); @endphp
<div class="card overflow-hidden">
    <nav class="flex gap-1 overflow-x-auto px-5 py-3 border-b border-slate-100">
        @foreach (['' => 'All', 'review' => 'Under review', 'paid' => 'Paid', 'pending' => 'Pending', 'expired' => 'Expired'] as $value => $label)
            <a href="{{ route('admin.orders', array_filter(['status' => $value])) }}" @class([
                'whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition',
                'bg-slate-900 text-white' => $current === $value,
                'text-slate-600 hover:bg-slate-100' => $current !== $value,
            ])>{{ $label }}</a>
        @endforeach
    </nav>
    @include('orders._table', ['showMerchant' => true])
    @if ($orders->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $orders->links() }}</div>@endif
</div>
@endsection
