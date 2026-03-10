<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregamos el nuevo valor 'refund_pending' al ENUM
        DB::statement("ALTER TABLE medical_orders MODIFY COLUMN status ENUM('pending', 'paid', 'signed', 'cancelled', 'refund_pending')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Regresamos al estado anterior
        DB::statement("ALTER TABLE medical_orders MODIFY COLUMN status ENUM('pending', 'paid', 'signed', 'cancelled')");
    }
};
