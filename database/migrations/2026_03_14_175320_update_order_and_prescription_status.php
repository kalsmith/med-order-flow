<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar estados en Orders
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'refund_pending', 'refunded', 'failed', 'manual_review', 'rejected') DEFAULT 'pending'");

        // 2. Ampliar estados en Prescriptions
        DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active', 'signed', 'expired', 'cancelled') DEFAULT 'active'");
    }

    public function down(): void
    {
        // Si necesitas volver atrás, aunque usualmente en ENUMs no es necesario
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending'");
        DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active', 'expired', 'cancelled') DEFAULT 'active'");
    }
};
