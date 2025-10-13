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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Informations du produit au moment de la commande
            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->string('product_sku')->nullable();
            
            // Prix et quantité
            $table->decimal('price', 10, 2); // Prix unitaire
            $table->integer('quantity');
            $table->decimal('total', 10, 2); // price * quantity
            
            $table->timestamps();
            
            // Index
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
