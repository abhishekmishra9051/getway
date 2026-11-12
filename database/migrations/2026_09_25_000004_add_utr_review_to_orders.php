<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_utr', 40)->nullable()->after('utr'); // UTR typed by the customer, awaiting review
            $table->timestamp('utr_submitted_at')->nullable()->after('customer_utr');
            $table->unsignedTinyInteger('utr_attempts')->default(0)->after('utr_submitted_at');
            $table->string('review_note')->nullable()->after('utr_attempts'); // why the merchant rejected the last UTR

            // A confirmed UTR can belong to only one order per merchant.
            $table->unique(['user_id', 'utr']);
            $table->index(['user_id', 'customer_utr']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'utr']);
            $table->dropIndex(['user_id', 'customer_utr']);
            $table->dropColumn(['customer_utr', 'utr_submitted_at', 'utr_attempts', 'review_note']);
        });
    }
};
