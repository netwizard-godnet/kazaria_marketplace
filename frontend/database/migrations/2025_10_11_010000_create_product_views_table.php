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
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable(); // Pour les utilisateurs non connectés
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Pour les utilisateurs connectés
            $table->string('ip_address', 45)->nullable(); // Adresse IP
            $table->string('user_agent')->nullable(); // Navigateur
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['session_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
            
            // Éviter les doublons pour la même session/IP dans un court délai
            $table->unique(['product_id', 'session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
