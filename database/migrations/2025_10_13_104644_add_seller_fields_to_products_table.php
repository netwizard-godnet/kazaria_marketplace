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
        Schema::table('products', function (Blueprint $table) {
            $table->string('model')->nullable()->after('brand');
            $table->string('warranty')->nullable()->after('stock');
            $table->json('attributes')->nullable()->after('images');
            $table->json('tags')->nullable()->after('attributes');
            $table->integer('views')->default(0)->after('reviews_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['model', 'warranty', 'attributes', 'tags', 'views']);
        });
    }
};
