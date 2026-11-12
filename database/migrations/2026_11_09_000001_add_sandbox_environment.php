<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sandbox_api_key')->nullable()->unique()->after('api_secret');
            $table->text('sandbox_api_secret')->nullable()->after('sandbox_api_key');
            $table->string('sandbox_webhook_url', 500)->nullable()->after('webhook_url');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'client_order_id']);
            $table->foreignId('payment_account_id')->nullable()->change();
            $table->string('environment', 16)->default('live')->after('status');
            $table->timestamp('sandbox_simulated_at')->nullable()->after('last_checked_at');
            $table->unique(['user_id', 'environment', 'client_order_id'], 'orders_user_environment_client_unique');
            $table->index(['user_id', 'environment', 'status', 'created_at'], 'orders_user_environment_status_created');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            DB::table('orders')->whereNull('payment_account_id')->delete();
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_user_environment_client_unique');
            $table->dropIndex('orders_user_environment_status_created');
            $table->dropColumn(['environment', 'sandbox_simulated_at']);
            $table->unique(['user_id', 'client_order_id']);
            $table->foreignId('payment_account_id')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['sandbox_api_key']);
            $table->dropColumn(['sandbox_api_key', 'sandbox_api_secret', 'sandbox_webhook_url']);
        });
    }
};
