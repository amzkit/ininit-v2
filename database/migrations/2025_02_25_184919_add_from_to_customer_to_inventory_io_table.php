<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('inventory_io', function (Blueprint $table) {
            $table->unsignedBigInteger('from_customer_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('from_address_id')->nullable()->after('from_customer_id');
            $table->unsignedBigInteger('to_customer_id')->nullable()->after('from_address_id');
            $table->unsignedBigInteger('to_address_id')->nullable()->after('to_customer_id');

            // Foreign keys
            $table->foreign('from_customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('from_address_id')->references('id')->on('customer_addresses')->onDelete('set null');
            $table->foreign('to_customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('to_address_id')->references('id')->on('customer_addresses')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('inventory_io', function (Blueprint $table) {
            $table->dropForeign(['from_customer_id']);
            $table->dropForeign(['from_address_id']);
            $table->dropForeign(['to_customer_id']);
            $table->dropForeign(['to_address_id']);
            $table->dropColumn(['from_customer_id', 'from_address_id', 'to_customer_id', 'to_address_id']);
        });
    }
};