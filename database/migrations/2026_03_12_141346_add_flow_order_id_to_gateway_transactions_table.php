<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->string('flow_order_id')->nullable()->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->dropColumn('flow_order_id');
        });
    }
};
