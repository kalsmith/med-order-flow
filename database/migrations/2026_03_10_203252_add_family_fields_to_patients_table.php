<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Parentesco: self (yo), child (hijo/a), spouse (pareja), parent (padre/madre), other
            $table->string('relationship', 50)->default('self')->after('user_id');

            // Para marcar quién es el titular de la cuenta
            $table->boolean('is_primary')->default(false)->after('relationship');

            // Cambiamos los tipos TEXT a VARCHAR para mejor performance e índices
            $table->string('rut')->change();

            // Un usuario no puede tener dos pacientes con el mismo RUT (evita duplicados en su familia)
            $table->unique(['user_id', 'rut'], 'user_patient_rut_unique');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique('user_patient_rut_unique');
            $table->dropColumn(['relationship', 'is_primary']);
        });
    }
};
