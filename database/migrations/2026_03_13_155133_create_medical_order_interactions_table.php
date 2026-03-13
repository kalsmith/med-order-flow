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
        Schema::create('medical_order_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('medical_order_id')->constrained()->onDelete('cascade');

            // Quién escribe: 'patient' o 'doctor'
            $table->string('sender_type');

            // Tipo de mensaje: 'anamnesis' (inicio), 'question' (médico), 'answer' (paciente)
            $table->string('type')->default('message');

            $table->text('content');

            // Por si en el futuro permites subir fotos de exámenes previos
            $table->string('attachment_path')->nullable();

            $table->timestamps();

            // Índice para purga rápida y ordenamiento
            $table->index(['medical_order_id', 'created_at']);
        });
    }
};
