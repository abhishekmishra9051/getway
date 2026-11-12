<?php

namespace App\Services\Verifiers;

use App\Models\Order;

/**
 * For plain UPI IDs with no status API: the merchant confirms the payment
 * from the dashboard after matching the UTR in their bank/UPI app.
 */
class ManualVerifier implements Verifier
{
    public function check(Order $order): VerificationResult
    {
        return VerificationResult::pending();
    }
}
