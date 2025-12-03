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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('rating')->default(5); // 1-5 étoiles
            $table->string('title')->nullable();
            $table->text('comment');
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            
            // Un utilisateur ne peut laisser qu'un seul avis par produit
            $table->unique(['user_id', 'product_id']);
            
            // Index pour les requêtes fréquentes
            $table->index(['product_id', 'is_approved', 'created_at']);
        });

        // Table pour les votes "utile" sur les avis
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->boolean('is_helpful')->default(true); // true = utile, false = pas utile
            $table->timestamps();
            
            // Un utilisateur/session ne peut voter qu'une fois par avis
            $table->unique(['user_id', 'review_id']);
            $table->unique(['session_id', 'review_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('reviews');
    }
};
