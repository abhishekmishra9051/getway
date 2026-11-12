<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rotation_mode')->default('random')->after('webhook_url'); // random | default
        });

        Schema::table('payment_accounts', function (Blueprint $table) {
            // Max amount per day (paid today + open pending). Null = no limit.
            $table->decimal('daily_limit', 12, 2)->nullable()->default(100000)->after('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('rotation_mode'));
        Schema::table('payment_accounts', fn (Blueprint $table) => $table->dropColumn('daily_limit'));
    }
};
