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
        Schema::table('exam_types', function (Blueprint $table) {
            // Relación opcional: Un examen PUEDE tener un artículo de blog principal
            $table->foreignId('post_id')->nullable()->constrained('blog_posts')->nullOnDelete()->after('description');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_types', function (Blueprint $table) {
            //
        });
    }
};
