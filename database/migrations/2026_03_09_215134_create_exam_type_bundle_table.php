<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_type_bundle', function (Blueprint $table) {
            $table->id();

            // El "Padre" (El Pack/Batería)
            $table->foreignId('parent_id')
                  ->constrained('exam_types')
                  ->onDelete('cascade');

            // El "Hijo" (El examen atómico)
            $table->foreignId('child_id')
                  ->constrained('exam_types')
                  ->onDelete('cascade');

            $table->timestamps();

            // Evitar que se duplique la misma relación
            $table->unique(['parent_id', 'child_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_type_bundle');
    }
};
