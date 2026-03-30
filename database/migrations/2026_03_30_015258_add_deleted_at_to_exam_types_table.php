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
        Schema::table('exam_types', function (Blueprint $table) {
            // Agregamos la columna deleted_at para habilitar SoftDeletes
            // La ponemos después de is_active para mantener el orden
            $table->softDeletes()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_types', function (Blueprint $table) {
            // Eliminamos la columna si hacemos rollback de la migración
            $table->dropSoftDeletes();
        });
    }
};
