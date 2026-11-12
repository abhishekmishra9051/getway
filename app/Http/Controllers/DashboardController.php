<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $environment = $request->query('environment') === Order::SANDBOX ? Order::SANDBOX : Order::LIVE;
        $orders = fn () => $user->orders()->where('environment', $environment);

        // ── Single query for all status-based stats ───────────────────────────
        // Replaces 7 separate COUNT/SUM calls with one grouped aggregation.
        $agg = $orders()
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'review' THEN 1 ELSE 0 END) AS review_count,
                SUM(CASE WHEN status IN ('paid', 'expired', 'failed') THEN 1 ELSE 0 END) AS closed_count,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) AS total_amount,
                COALESCE(SUM(CASE WHEN status = 'paid' AND paid_at >= ? THEN amount ELSE 0 END), 0) AS today_amount,
                SUM(CASE WHEN status = 'paid' AND paid_at >= ? THEN 1 ELSE 0 END) AS today_count",
                [today()->startOfDay(), today()->startOfDay()])
            ->first();

        $paidCount = (int) $agg->paid_count;
        $closedCount = (int) $agg->closed_count;

        $stats = [
            'today_amount' => (float) $agg->today_amount,
            'today_count' => (int) $agg->today_count,
            'total_amount' => (float) $agg->total_amount,
            'paid_count' => $paidCount,
            'pending_count' => (int) $agg->pending_count,
            'review_count' => (int) $agg->review_count,
            'success_rate' => $closedCount ? (int) round($paidCount / $closedCount * 100) : null,
        ];

        // ── Single query for the 7-day chart, grouped in the DB ───────────────
        // Replaces loading all paid rows into PHP memory and grouping there.
        $since = today()->subDays(6);

        $byDay = $orders()
            ->where('status', Order::PAID)
            ->where('paid_at', '>=', $since)
            ->selectRaw('DATE(paid_at) AS day, COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS total')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $chart = collect(range(0, 6))->map(function ($i) use ($since, $byDay) {
            $day = $since->copy()->addDays($i)->toDateString();

            return [
                'label' => $since->copy()->addDays($i)->format('D'),
                'date' => $since->copy()->addDays($i)->format('d M'),
                'amount' => (float) ($byDay->get($day)?->total ?? 0),
                'count' => (int) ($byDay->get($day)?->cnt ?? 0),
            ];
        });

        $checklist = $environment === Order::SANDBOX ? [
            ['Generate Sandbox credentials', (bool) $user->sandbox_api_key, route('settings')],
            ['Set up Sandbox webhook URL', (bool) $user->sandbox_webhook_url, route('settings')],
            ['Create your first TEST payment', $user->orders()->where('environment', Order::SANDBOX)->exists(), route('docs')],
        ] : [
            ['Create your merchant account', true, null],
            ['Add a UPI account', $user->paymentAccounts()->exists(), route('accounts.create')],
            ['Set up your webhook URL', (bool) $user->webhook_url, route('settings')],
            ['Receive your first payment', $paidCount > 0, route('orders.create')],
        ];

        return view('dashboard', [
            'stats' => $stats,
            'chart' => $chart,
            'weekTotal' => $chart->sum('amount'),
            'checklist' => $checklist,
            'recentOrders' => $orders()->with('paymentAccount')->latest()->limit(6)->get(),
            'environment' => $environment,
        ]);
    }
}
