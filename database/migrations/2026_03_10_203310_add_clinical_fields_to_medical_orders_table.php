<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_orders', function (Blueprint $table) {
            // El campo donde el QF leerá lo que el usuario escribió (síntomas)
            $table->text('clinical_context')->nullable()->after('custom_description');

            // Nivel de urgencia para el panel administrativo del QF/Médico
            // $table->enum('urgency_level', ['normal', 'priority', 'urgent'])
            //       ->default('normal')
            //       ->after('type');

            // Edad del paciente al momento de la orden (importante para el registro histórico)
            $table->integer('patient_age_at_order')->nullable()->after('patient_id');
        });
    }

    public function down(): void
    {
        Schema::table('medical_orders', function (Blueprint $table) {
            $table->dropColumn(['clinical_context', 'urgency_level', 'patient_age_at_order']);
        });
    }
};
