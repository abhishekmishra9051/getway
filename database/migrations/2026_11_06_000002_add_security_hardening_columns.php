<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security hardening additions (non-destructive):
 *  - users.redirect_domains   — JSON allowlist for redirect_url host validation
 *  - users.api_last_used_at   — track last API usage time
 *  - users.api_last_used_ip   — track last API usage IP
 *  - admin_audit_logs         — log admin actions on merchant resources
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('redirect_domains')->nullable()->after('webhook_url');
            $table->timestamp('api_last_used_at')->nullable()->after('api_key');
            $table->string('api_last_used_ip', 45)->nullable()->after('api_last_used_at');
        });

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 64);
            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — audit rows are append-only.
            $table->index(['admin_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['redirect_domains', 'api_last_used_at', 'api_last_used_ip']);
        });
    }
};
