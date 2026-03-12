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
        Schema::table('medical_orders', function (Blueprint $table) {
            // Almacena el momento en que un médico hace clic en "Firmar"
            $table->timestamp('claimed_at')->nullable()->after('doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_orders', function (Blueprint $table) {
            //
        });
    }
};
