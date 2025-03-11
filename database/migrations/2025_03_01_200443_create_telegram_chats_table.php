<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('telegram_chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('store_id')->nullable(); // Optional, if linked to stores
            $table->unsignedBigInteger('user_id')->nullable(); // Optional, if linked to users
            $table->bigInteger('chat_id')->unique(); // Store Telegram chat ID
            $table->string('chat_type')->nullable(); // e.g., 'private', 'group'
            $table->string('channel')->nullable(); // Optional, Group Name, if linked to channels (e.g., 'inventory')
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('telegram_chats');
    }
};

