<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. REORGANIZAR TABLA ORDERS (La Plata)
        Schema::table('orders', function (Blueprint $blueprint) {
            // Eliminamos lo que ya no debería ser responsabilidad de la orden
            // El verification_code ahora es propio de la receta (el QR del PDF)
            if (Schema::hasColumn('orders', 'verification_code')) {
                $blueprint->dropUnique('orders_verification_code_unique');
                $blueprint->dropColumn('verification_code');
            }

            // La razón de rechazo es un evento clínico/administrativo,
            // mejor manejarlo en la prescripción o log de interacciones
            if (Schema::hasColumn('orders', 'rejection_reason')) {
                $blueprint->dropColumn('rejection_reason');
            }
        });

        // 2. REORGANIZAR TABLA PRESCRIPTIONS (La Firma)
        Schema::table('prescriptions', function (Blueprint $blueprint) {
            // Añadimos el tipo para saber si el médico debe escribir (custom) o es fija (standard)
            if (!Schema::hasColumn('prescriptions', 'type')) {
                $blueprint->enum('type', ['standard', 'custom'])->default('standard')->after('exam_type_id');
            }

            // Añadimos un campo para el motivo de rechazo médico específico
            if (!Schema::hasColumn('prescriptions', 'rejection_reason')) {
                $blueprint->text('rejection_reason')->nullable()->after('void_reason');
            }
        });

        // 3. ACTUALIZACIÓN DE ESTADOS (ENUMS)
        // Como MySQL/MariaDB no permite modificar ENUMs fácilmente por Schema, usamos DB::statement

        // La Orden se queda con estados puramente financieros/de flujo
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'refund_pending', 'refunded', 'failed', 'manual_review', 'rejected') NOT NULL DEFAULT 'pending'");

        // La Prescripción se queda con estados clínicos
        DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active', 'signed', 'voided', 'used', 'expired', 'rejected') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            $blueprint->varchar('verification_code')->nullable()->unique();
            $blueprint->text('rejection_reason')->nullable();
        });

        Schema::table('prescriptions', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['type', 'rejection_reason']);
        });
    }
};
