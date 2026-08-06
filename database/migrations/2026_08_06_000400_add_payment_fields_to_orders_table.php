<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 40)->default('cod')->after('status');
            $table->string('payment_status', 20)->default('unpaid')->after('payment_method');
            $table->string('payment_other_method')->nullable()->after('payment_status');
            $table->string('payment_reference', 120)->nullable()->after('payment_other_method');
            $table->decimal('paid_amount', 12, 2)->nullable()->after('payment_reference');
            $table->timestamp('paid_at')->nullable()->after('paid_amount');
            $table->text('payment_notes')->nullable()->after('paid_at');
            $table->index(['payment_method', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_method', 'payment_status']);
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'payment_other_method',
                'payment_reference',
                'paid_amount',
                'paid_at',
                'payment_notes',
            ]);
        });
    }
};
