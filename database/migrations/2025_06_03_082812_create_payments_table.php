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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_provider_id')->constrained('payment_providers')->onDelete('restrict');
            $table->string('external_payment_id'); // ID del pago en el proveedor
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->json('metadata')->nullable(); // para guardar respuesta cruda del proveedor
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
