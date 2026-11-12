<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            $nullCount = (int) DB::table('orders')
                ->whereNull('expires_at')
                ->count();

            if ($nullCount > 0) {
                throw new RuntimeException(
                    "Cannot restore orders.expires_at to NOT NULL: {$nullCount} order(s) have NULL expiry values. "
                    .'Repair those rows using an approved expiry policy, verify the data, then rerun the rollback.'
                );
            }

            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable(false)->change();
            });
        }
    }
};
