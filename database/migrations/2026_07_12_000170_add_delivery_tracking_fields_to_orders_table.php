<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('driver_name')->nullable()->after('delivery_longitude');
            $table->string('driver_phone', 30)->nullable()->after('driver_name');
            $table->string('proof_of_delivery_path')->nullable()->after('driver_phone');
            $table->timestamp('delivered_at')->nullable()->after('proof_of_delivery_path');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'driver_name',
                'driver_phone',
                'proof_of_delivery_path',
                'delivered_at',
            ]);
        });
    }
};