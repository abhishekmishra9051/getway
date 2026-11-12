<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PG accounts (PhonePe PG, Paytm PG) take payments on the PG's own
        // checkout, so they have no UPI ID of their own.
        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->string('upi_id')->nullable()->change();
        });

        // Cached PG checkout session (redirect URL or form fields) so reopening
        // the payment link doesn't create a second PG transaction.
        Schema::table('orders', function (Blueprint $table) {
            $table->json('provider_checkout')->nullable()->after('provider_response');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('provider_checkout'));
        Schema::table('payment_accounts', fn (Blueprint $table) => $table->string('upi_id')->nullable(false)->change());
    }
};
