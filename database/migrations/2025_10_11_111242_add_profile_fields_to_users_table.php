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
        Schema::table('users', function (Blueprint $table) {
            $table->string('code_postal')->nullable()->after('adresse');
            $table->string('ville')->nullable()->after('code_postal');
            $table->string('pays')->default('CI')->after('ville'); // CI = Côte d'Ivoire
            $table->text('bio')->nullable()->after('pays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['code_postal', 'ville', 'pays', 'bio']);
        });
    }
};
