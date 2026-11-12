@extends('layouts.app')
@section('title', 'Merchants')
@section('subtitle', 'Everyone using the platform, with their collections.')

@section('content')
<div class="card overflow-hidden">
    @if ($merchants->isEmpty())
        <x-empty icon="users" title="No merchants yet" text="Merchants appear here once they sign up." />
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50/70">
                <tr>
                    <th class="th">Merchant</th>
                    <th class="th text-right">Orders</th>
                    <th class="th text-right">Paid</th>
                    <th class="th text-right">Collected</th>
                    <th class="th">Status</th>
                    <th class="th">Joined</th>
                    <th class="th"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($merchants as $merchant)
                    <tr class="hover:bg-slate-50/80">
                        <td class="td">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$merchant->displayName()" />
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900">{{ $merchant->displayName() }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $merchant->email }}{{ $merchant->phone ? ' · '.$merchant->phone : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="td text-right tabular-nums">{{ number_format($merchant->orders_count) }}</td>
                        <td class="td text-right tabular-nums">{{ number_format($merchant->paid_orders_count) }}</td>
                        <td class="td text-right font-semibold text-slate-900 tabular-nums">₹{{ number_format($merchant->paid_amount ?? 0, 2) }}</td>
                        <td class="td">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $merchant->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-red-50 text-red-700 ring-red-600/20' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $merchant->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>{{ $merchant->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="td text-slate-500 whitespace-nowrap">{{ $merchant->created_at->format('d M Y') }}</td>
                        <td class="td text-right">
                            <form method="POST" action="{{ route('admin.merchants.toggle', $merchant) }}">@csrf
                                <button data-confirm="{{ $merchant->is_active ? 'Disable' : 'Enable' }} {{ $merchant->displayName() }}?" class="btn btn-sm {{ $merchant->is_active ? 'btn-danger' : 'btn-secondary' }}">
                                    {{ $merchant->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @if ($merchants->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $merchants->links() }}</div>@endif
</div>
@endsection
