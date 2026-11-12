<?php

namespace App\Policies;

use App\Models\PaymentAccount;
use App\Models\User;

class PaymentAccountPolicy
{
    public function update(User $user, PaymentAccount $account): bool
    {
        return $account->user_id === $user->id;
    }

    public function delete(User $user, PaymentAccount $account): bool
    {
        return $account->user_id === $user->id;
    }
}
