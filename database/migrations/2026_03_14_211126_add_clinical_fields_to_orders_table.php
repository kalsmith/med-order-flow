<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        // El texto de la receta/indicación médica
        $table->text('clinical_context')->nullable()->after('custom_description');

        // La fecha exacta en que el médico firmó
        $table->timestamp('signed_at')->nullable()->after('claimed_at');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['clinical_context', 'signed_at']);
    });
}
};
