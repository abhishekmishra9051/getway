<?php

namespace App\Services\Verifiers;

use App\Models\Order;

interface Verifier
{
    /**
     * Ask the provider whether this order has been paid.
     * Must never throw for "not paid yet"; return VerificationResult::pending().
     */
    public function check(Order $order): VerificationResult;
}
