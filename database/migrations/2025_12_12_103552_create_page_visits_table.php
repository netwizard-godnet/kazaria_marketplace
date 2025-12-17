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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('page_path'); // Chemin de la page (ex: /products, /categories/phones)
            $table->string('page_name')->nullable(); // Nom lisible de la page
            $table->string('session_id')->nullable(); // Pour les utilisateurs non connectés
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Pour les utilisateurs connectés
            $table->string('ip_address', 45)->nullable(); // Adresse IP
            $table->text('user_agent')->nullable(); // Navigateur
            $table->text('referrer')->nullable(); // Page précédente
            $table->integer('click_count')->default(0); // Nombre de clics sur la page
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['page_path', 'created_at']);
            $table->index(['session_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
