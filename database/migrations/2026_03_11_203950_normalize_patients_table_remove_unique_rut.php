<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // 1. Eliminamos la llave foránea primero (es la que bloquea el índice)
            $table->dropForeign(['user_id']);

            // 2. Ahora sí podemos borrar el índice único
            $table->dropUnique('user_patient_rut_unique');
        });

        Schema::table('patients', function (Blueprint $table) {
            // 3. Normalizamos los campos a TEXT
            $table->text('rut')->change();
            $table->text('full_name')->change();
            $table->text('birth_date')->change();
            $table->text('gender_biologic')->change();

            // 4. Volvemos a crear la relación con users (pero ahora sin el índice único)
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('rut', 255)->change();
            $table->unique(['user_id', 'rut'], 'user_patient_rut_unique');
        });
    }
};
