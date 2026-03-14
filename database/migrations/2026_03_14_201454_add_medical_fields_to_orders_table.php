<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->timestamp('claimed_at')->nullable();
            $table->text('custom_description')->nullable(); // Para que el doctor sepa qué le piden
            $table->text('rejection_reason')->nullable();
            // Si necesitas el código de verificación para el PDF final aquí:
            $table->string('verification_code')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
