<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Actualizamos la tabla orders
        // Agregamos manual_review (para fallos de firma) y refund_pending
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'refund_pending', 'refunded', 'failed', 'manual_review', 'rejected') NOT NULL DEFAULT 'pending'");

        // Actualizamos la tabla prescriptions
        // Agregamos 'signed' que es el que pide tu FlowService
        DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active', 'signed', 'voided', 'used', 'expired') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Revertir a los estados originales que me mostraste en el CREATE TABLE
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'refunded', 'failed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active', 'voided', 'used') NOT NULL DEFAULT 'active'");
    }
};
