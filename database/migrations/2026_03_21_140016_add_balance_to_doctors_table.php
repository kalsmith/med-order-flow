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
            Schema::table('doctors', function (Blueprint $table) {
                // Usamos integer para montos en CLP (sin decimales)
                $table->integer('balance')->default(0)->after('rnpi_number');
            });
        }

        public function down(): void
        {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('balance');
            });
        }
};
