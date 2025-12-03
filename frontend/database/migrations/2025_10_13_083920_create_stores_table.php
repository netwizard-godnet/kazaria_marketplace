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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nom de la boutique
            $table->string('slug')->unique(); // URL de la boutique
            $table->text('description')->nullable(); // Description
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null'); // Catégorie principale
            $table->string('phone'); // Numéro de téléphone
            $table->string('email')->unique(); // Email de la boutique
            $table->string('address')->nullable(); // Adresse physique
            $table->string('city')->nullable(); // Ville
            $table->string('logo')->nullable(); // Logo de la boutique
            $table->string('banner')->nullable(); // Bannière de la boutique
            $table->string('dfe_document')->nullable(); // Document DFE
            $table->string('commerce_register')->nullable(); // Registre de commerce
            $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('pending'); // Statut
            $table->boolean('is_verified')->default(false); // Vérification
            $table->boolean('is_official')->default(false); // Boutique officielle
            $table->decimal('commission_rate', 5, 2)->default(10.00); // Taux de commission (%)
            $table->json('business_hours')->nullable(); // Heures d'ouverture
            $table->json('social_links')->nullable(); // Réseaux sociaux
            $table->integer('total_products')->default(0); // Nombre de produits
            $table->integer('total_orders')->default(0); // Nombre de commandes
            $table->decimal('total_sales', 15, 2)->default(0); // Total des ventes
            $table->decimal('rating', 3, 2)->default(0); // Note moyenne
            $table->integer('reviews_count')->default(0); // Nombre d'avis
            $table->timestamps();
            $table->softDeletes(); // Suppression douce
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
