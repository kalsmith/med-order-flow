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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users'); // Aquí va el Matrón o QF

            // Contenido Principal
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary'); // Para la meta-description y el listado
            $table->longText('content');
            $table->string('featured_image')->nullable();

            // SEO Fields (Fundamentales)
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            // El "Gancho" de Conversión (Aquí ocurre la magia)
            $table->string('cta_type')->nullable(); // 'pack', 'exam', 'custom_flow'
            $table->unsignedBigInteger('cta_id')->nullable(); // ID del pack o examen asociado

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
