<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_orders', function (Blueprint $blueprint) {
            // 'standard' para packs existentes, 'custom' para pedidos manuales
           // $blueprint->string('type')->default('standard')->after('status');

            // Aquí guardamos el texto del examen que no encontró
            $blueprint->text('custom_description')->nullable()->after('type');

            // Hacemos que exam_type_id sea nullable por si es una orden 100% manual
            $blueprint->unsignedBigInteger('exam_type_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('medical_orders', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['type', 'custom_description']);
            $blueprint->unsignedBigInteger('exam_type_id')->nullable(false)->change();
        });
    }
};
