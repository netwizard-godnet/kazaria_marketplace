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
        Schema::table('popups', function (Blueprint $table) {
            if (!Schema::hasColumn('popups', 'width')) {
                $table->unsignedInteger('width')->default(300)->after('image');
            }
            if (!Schema::hasColumn('popups', 'height')) {
                $table->unsignedInteger('height')->default(300)->after('width');
            }
            if (!Schema::hasColumn('popups', 'layout')) {
                $table->string('layout')->default('stacked')->after('height');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->dropColumn(['width', 'height', 'layout']);
        });
    }
};
