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
        Schema::table('medical_orders', function (Blueprint $table) {
            // Campos para el médico
            $table->text('rejection_reason')->nullable()->after('custom_description');
            $table->text('internal_notes')->nullable()->after('rejection_reason'); // Solo para uso interno médico

            // Campo para seguimiento de Flow
            $table->string('flow_refund_id')->nullable()->after('amount');

            // Aseguramos que clinical_context esté disponible (si no existe en todas las migraciones previas)
            if (!Schema::hasColumn('medical_orders', 'clinical_context')) {
                $table->text('clinical_context')->nullable()->after('custom_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_orders', function (Blueprint $table) {
            //
        });
    }
};
