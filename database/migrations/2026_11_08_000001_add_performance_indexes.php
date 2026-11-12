<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes:
 *
 *  orders(user_id, status, created_at)
 *    — OrderController::index filters by user_id + optional status, orders by created_at DESC.
 *
 *  orders(payment_account_id, status, paid_at)
 *    — PaymentAccount::scopeWithUsageToday subquery joins on payment_account_id
 *      and filters by status / paid_at.
 *
 *  orders(user_id, paid_at)
 *    — Dashboard 7-day chart filters paid orders by user_id + paid_at range.
 *
 *  webhook_logs(order_id, event, success)
 *    — SendWebhook idempotency check: ->where('event')->where('success', true)->exists()
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'orders_user_status_created');
            $table->index(['payment_account_id', 'status', 'paid_at'], 'orders_account_status_paid');
            $table->index(['user_id', 'paid_at'], 'orders_user_paid_at');
        });

        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->index(['order_id', 'event', 'success'], 'webhook_logs_order_event_success');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_status_created');
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->index('payment_account_id', 'orders_payment_account_id_foreign');
            }
            $table->dropIndex('orders_account_status_paid');
            $table->dropIndex('orders_user_paid_at');
        });

        Schema::table('webhook_logs', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->index('order_id', 'webhook_logs_order_id_foreign');
            }
            $table->dropIndex('webhook_logs_order_event_success');
        });
    }
};
