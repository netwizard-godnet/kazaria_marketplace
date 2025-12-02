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
        // Supprimer les tables si elles existent déjà (pour éviter les erreurs)
        Schema::dropIfExists('product_variation_attribute_values');
        Schema::dropIfExists('product_variations');
        
        // Table des variations de produits (avec prix différents selon les attributs)
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->nullable()->unique(); // Référence unique pour cette variation
            $table->decimal('price', 10, 2); // Prix spécifique pour cette variation
            $table->decimal('old_price', 10, 2)->nullable(); // Ancien prix (pour promo)
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Pourcentage de réduction
            $table->integer('stock')->default(0); // Stock spécifique pour cette variation
            $table->string('image')->nullable(); // Image spécifique pour cette variation (optionnel)
            $table->boolean('is_default')->default(false); // Variation par défaut
            $table->boolean('is_active')->default(true); // Activer/désactiver cette variation
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('sku');
        });

        // Table pivot pour lier les variations aux valeurs d'attributs
        Schema::create('product_variation_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variation_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_value_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['product_variation_id', 'attribute_value_id'], 'var_attr_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_attribute_values');
        Schema::dropIfExists('product_variations');
    }
};
