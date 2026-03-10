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
        Schema::create('medical_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Ahora sí, estas tablas ya existen arriba:
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('doctor_id')->constrained();
            $table->foreignId('exam_type_id')->constrained();

            $table->enum('status', ['pending', 'paid', 'signed', 'cancelled'])->default('pending');
            $table->integer('amount')->default(0);
            $table->string('verification_code')->unique()->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_orders');
    }
};
