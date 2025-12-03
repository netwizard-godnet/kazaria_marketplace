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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la permission (ex: manage_users, manage_products)
            $table->string('slug')->unique(); // Slug de la permission
            $table->text('description')->nullable(); // Description de la permission
            $table->string('module')->nullable(); // Module concerné (ex: users, products, orders)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
