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
        Schema::table('transactions', function (Blueprint $table) {
            // Cambiamos el ID a char(36) para soportar UUIDs
            $table->char('id', 36)->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Por si necesitas volver atrás (esto es arriesgado si ya tienes datos)
            $table->bigIncrements('id')->change();
        });
    }
};
