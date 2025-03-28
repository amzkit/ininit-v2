<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_io', function (Blueprint $table) {
            //
            $table->uuid('partner_id')->nullable();
            $table->enum('type', ['in', 'out'])->default('in'); // ✅ 'in' or 'out'
            $table->enum('transaction_type', ['purchase', 'sell', 'borrow', 'adjustment', 'opening'])->default('purchase'); // ✅ 'purchase', 'return', 'transfer', 'adjustment', 'opening'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_io', function (Blueprint $table) {
            //
            $table->dropColumn(['partner_id', 'type']);

        });
    }
};