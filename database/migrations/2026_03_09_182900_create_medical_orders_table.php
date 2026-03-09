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
        Schema::create('medical_orders', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('patient_id')->constrained()->onDelete('restrict');
            $table->foreignId('doctor_id')->constrained()->onDelete('restrict');
            $table->foreignId('exam_type_id')->constrained()->onDelete('restrict');

            $table->enum('status', ['pending', 'paid', 'signed', 'cancelled'])->default('pending');
            $table->integer('amount')->default(0);

            $table->string('pdf_path')->nullable();
            $table->string('verification_code')->unique()->nullable(); // Para validar en la web

            $table->timestamp('signed_at')->nullable();
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
