<?php

namespace App\Services\Verifiers;

use App\Models\Order;

/**
 * Providers whose customers pay on the provider's own checkout page
 * (official payment gateways) instead of our UPI QR.
 */
interface HostedCheckout
{
    /**
     * Create the payment session at the provider.
     *
     * @return array{type: 'redirect'|'form', url: string, fields?: array<string, string>, expires_at: int}
     *
     * @throws \RuntimeException when the provider refuses the request
     */
    public function startCheckout(Order $order, string $returnUrl): array;
}
