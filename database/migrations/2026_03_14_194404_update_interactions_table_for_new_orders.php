<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // php artisan make:migration update_interactions_table_for_new_orders
    public function up()
    {
        Schema::table('medical_order_interactions', function (Blueprint $table) {
            // 1. Eliminar la FK vieja
            $table->dropForeign(['medical_order_id']);

            // 2. Renombrar la columna para que sea estándar
            $table->renameColumn('medical_order_id', 'order_id');
        });

        Schema::table('medical_order_interactions', function (Blueprint $table) {
            // 3. Crear la nueva FK hacia la tabla 'orders'
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_order_interactions', function (Blueprint $table) {
            //
        });
    }
};
