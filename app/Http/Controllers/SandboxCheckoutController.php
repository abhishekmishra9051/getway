<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SandboxCheckoutController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function show(Order $order)
    {
        $this->assertSandbox($order);

        return view('sandbox-pay', ['order' => $order]);
    }

    public function simulate(Request $request, Order $order)
    {
        $this->assertSandbox($order);

        $data = $request->validate([
            'outcome' => ['required', Rule::in([
                'success', 'failed', 'pending', 'expired', 'timeout', 'invalid_reference',
            ])],
        ]);

        $order = $this->orders->simulateSandbox($order, $data['outcome']);

        if ($data['outcome'] === 'timeout') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'environment' => Order::SANDBOX,
                    'test' => true,
                    'message' => 'Simulated provider timeout.',
                    'data' => $order->toApiArray(),
                ], 504);
            }

            return response()->view('sandbox-pay', [
                'order' => $order,
                'timeout' => true,
            ], 504);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'environment' => Order::SANDBOX,
                'test' => true,
                'data' => $order->toApiArray(),
            ]);
        }

        return redirect()->route('sandbox.pay.show', $order)
            ->with('status', 'TEST outcome simulated: '.strtoupper($data['outcome']).'.');
    }

    private function assertSandbox(Order $order): void
    {
        abort_unless($order->environment === Order::SANDBOX, 404);
    }
}
