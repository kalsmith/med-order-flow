<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Asegúrate que en la tabla patients el id sea bigIncrements
            $table->foreignId('patient_id')->constrained('patients');
            $table->integer('amount')->default(0);
            $table->enum('status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->string('flow_order_id')->nullable();
            $table->string('flow_refund_id')->nullable();
            $table->timestamps();
        }); // <-- Faltaba cerrar el Blueprint
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
