<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Cambiamos el tipo de dato a string para soportar el UUID
            $table->string('subject_id', 36)->change();
        });
    }

    public function down(): void
    {
        // Esto es por si necesitas revertir

    }
};
