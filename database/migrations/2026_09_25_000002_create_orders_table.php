<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 32)->unique(); // our ID, sent to UPI as `tr`
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->constrained()->cascadeOnDelete();
            $table->string('client_order_id', 64); // merchant's own order ID
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending | paid | expired
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_mobile', 20)->nullable();
            $table->string('note')->nullable();
            $table->string('redirect_url', 500)->nullable();
            $table->string('utr')->nullable();
            $table->string('provider_txn_id')->nullable();
            $table->json('provider_response')->nullable();
            $table->string('verified_by')->nullable(); // provider | manual
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_order_id']);
            $table->index(['status', 'expires_at']);
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->string('url', 500);
            $table->json('payload');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->boolean('success')->default(false);
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('orders');
    }
};
