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
        Schema::table('categories', function (Blueprint $table) {
            $table->text('custom_banners')->nullable()->after('custom_layout');
            $table->text('custom_carousels')->nullable()->after('custom_banners');
            $table->text('custom_colors')->nullable()->after('custom_carousels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['custom_banners', 'custom_carousels', 'custom_colors']);
        });
    }
};
