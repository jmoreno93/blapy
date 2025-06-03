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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_session_id')->nullable();
            $table->enum('sender', ['user', 'bot']);
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign('chat_session_id')->references('id')->on('chat_sessions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
