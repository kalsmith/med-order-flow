<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function columnExists($table, $column)
    {
        return Schema::hasColumn($table, $column);
    }

    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference_code')->unique(); // TRX-XXXXXX

            // Relaciones de participantes
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->nullable()->constrained('users');

            // Relación polimórfica con la Orden Médica u otros
            $table->uuid('reference_id')->nullable();
            $table->string('type')->default('medical_order');

            // Montos y contabilidad
            $table->decimal('amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);

            // Estado y Tracking
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['reference_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
