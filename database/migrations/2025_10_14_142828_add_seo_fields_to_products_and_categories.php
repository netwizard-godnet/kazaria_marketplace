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
        // Ajouter les champs SEO à la table products
        Schema::table('products', function (Blueprint $table) {
            $table->text('meta_description')->nullable()->after('description');
            $table->text('meta_keywords')->nullable()->after('meta_description');
        });

        // Ajouter les champs SEO à la table categories
        Schema::table('categories', function (Blueprint $table) {
            $table->text('meta_description')->nullable()->after('description');
            $table->text('meta_keywords')->nullable()->after('meta_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['meta_description', 'meta_keywords']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['meta_description', 'meta_keywords']);
        });
    }
};
