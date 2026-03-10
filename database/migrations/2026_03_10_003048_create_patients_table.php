<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Usamos TEXT para todos los datos que van cifrados (Casts en el modelo)
            $table->text('full_name');
            $table->text('rut'); // La unicidad la validaremos por lógica, no por DB (por el cifrado)
            $table->text('birth_date');
            $table->text('gender_biologic'); // 'M' o 'F'

            $table->text('phone')->nullable();
            $table->text('prevision')->nullable(); // Fonasa, Isapre, Particular...

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
