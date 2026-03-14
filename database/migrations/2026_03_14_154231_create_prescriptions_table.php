<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relación con el mundo comercial (Tabla Orders)
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');

            // Relaciones médicas
            $table->foreignId('doctor_id')->nullable()->constrained('doctors');
            $table->foreignId('exam_type_id')->nullable()->constrained('exam_types');

            // Correlativo Humano (Ej: 1001)
            $table->bigInteger('correlative_number')->unsigned()->unique();

            // Estados puramente médicos
            $table->enum('status', ['active', 'voided', 'used'])->default('active');

            // Identificación y validación
            $table->string('verification_code', 20)->unique();
            $table->text('clinical_context')->nullable();
            $table->text('void_reason')->nullable(); // Razón de la anulación técnica

            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
