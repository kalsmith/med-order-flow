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
        Schema::create('gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('gateway')->default('flow');
            $table->string('buy_order')->unique(); // MED-XXXXXX
            $table->string('token')->nullable();    // Token devuelto por Flow
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending, authorized, failed
            $table->uuid('payable_id');
            $table->string('payable_type');
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_transactions');
    }
};
