<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Redefinimos el ENUM incluyendo 'rejected'
        // Es importante mantener el orden y los valores anteriores para no corromper datos
        DB::statement("ALTER TABLE medical_orders MODIFY COLUMN status ENUM('pending', 'paid', 'signed', 'rejected', 'cancelled', 'refund_pending') DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En caso de rollback, volvemos al estado anterior (teniendo cuidado si ya hay registros 'rejected')
        DB::statement("ALTER TABLE medical_orders MODIFY COLUMN status ENUM('pending', 'paid', 'signed', 'cancelled', 'refund_pending') DEFAULT NULL");
    }
};
