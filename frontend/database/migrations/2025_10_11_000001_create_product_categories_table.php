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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Catégorie principale
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
            
            // Éviter les doublons
            $table->unique(['product_id', 'category_id']);
        });
        
        Schema::create('product_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Sous-catégorie principale
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
            
            // Éviter les doublons
            $table->unique(['product_id', 'subcategory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_subcategories');
        Schema::dropIfExists('product_categories');
    }
};
