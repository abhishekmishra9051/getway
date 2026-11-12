<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds error_label to webhook_logs so we can record short machine-readable
 * failure reasons (blocked_url, timeout, http_500, network_error) without
 * storing the raw response body (SSRF data-exfiltration risk).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->string('error_label', 32)->nullable()->after('response_body');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropColumn('error_label');
        });
    }
};
