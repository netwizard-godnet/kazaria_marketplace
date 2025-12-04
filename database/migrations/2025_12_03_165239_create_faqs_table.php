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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question'); // Question principale
            $table->text('answer'); // Réponse
            $table->json('keywords')->nullable(); // Mots-clés pour la correspondance (ex: ["livraison", "délai", "expédition"])
            $table->string('category')->nullable(); // Catégorie (ex: "livraison", "produit", "commande", "paiement")
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->boolean('is_active')->default(true);
            $table->integer('views_count')->default(0); // Nombre de fois que cette FAQ a été consultée
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
