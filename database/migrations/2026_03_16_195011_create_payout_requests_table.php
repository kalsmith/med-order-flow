<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_requests', function (Blueprint $blueprint) {
            $blueprint->id();
            // Relación con el doctor (ID de la tabla doctors)
            $blueprint->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');

            $blueprint->integer('amount'); // Monto solicitado
            $blueprint->enum('status', ['pending', 'paid', 'rejected'])->default('pending');

            // Gestión del pago
            $blueprint->string('evidence_path')->nullable(); // Ruta del comprobante (PDF/Imagen)
            $blueprint->timestamp('paid_at')->nullable();
            $blueprint->text('admin_notes')->nullable(); // Para notas del porqué se rechazó o info del banco

            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
