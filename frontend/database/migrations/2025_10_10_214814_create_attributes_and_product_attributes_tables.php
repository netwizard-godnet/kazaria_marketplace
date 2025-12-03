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
        // Table des attributs (Couleur, RAM, Stockage, etc.)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Couleur, RAM, Stockage
            $table->string('slug')->unique();
            $table->string('type')->default('select'); // select, checkbox, radio
            $table->boolean('is_filterable')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Table des valeurs d'attributs (Rouge, 8GB, 256GB, etc.)
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->string('value'); // Ex: Rouge, 8GB, 256GB
            $table->string('slug');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Table pivot produit-attributs
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_value_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['product_id', 'attribute_value_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
