<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Este campo es el "reloj" de la rotación.
            // Lo ponemos después de is_active.
            $table->timestamp('last_assigned_at')->nullable()->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('last_assigned_at');
        });
    }
};
