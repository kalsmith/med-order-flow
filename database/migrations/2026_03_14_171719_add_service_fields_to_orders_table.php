<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            // El ID del examen (para flujo médico).
            // Es nullable porque si mañana vendes una "Suscripción", este campo no se usará.
            $blueprint->foreignId('exam_type_id')
                ->nullable()
                ->constrained('exam_types')
                ->nullOnDelete()
                ->after('patient_id');

            // El tipo de servicio para el switch del Webhook.
            // Por defecto 'standard' para no romper lo que ya tienes.
            $blueprint->string('type')
                ->default('standard')
                ->after('exam_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['exam_type_id']);
            $table->dropColumn(['exam_type_id', 'type']);
        });
    }
};
