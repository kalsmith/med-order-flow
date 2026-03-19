<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            // Relación con la orden principal
            $table->foreignUuid('order_id')->constrained()->onDelete('cascade');

            // Relación con el catálogo (nullable para casos custom si fuera necesario)
            $table->foreignId('exam_type_id')->nullable()->constrained('exam_types');

            // Guardamos el nombre actual para registro rápido sin joins pesados
            $table->string('exam_name');
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
