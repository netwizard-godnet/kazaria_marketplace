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
            // Renommer name en nom et ajouter prenoms
            $table->renameColumn('name', 'nom');
            $table->string('prenoms')->after('nom');
            
            // Ajouter les colonnes manquantes
            $table->string('telephone', 20)->nullable()->after('email');
            $table->timestamp('telephone_verified_at')->nullable()->after('telephone');
            $table->string('profile_pic_url')->nullable()->after('telephone_verified_at');
            $table->boolean('is_verified')->default(false)->after('profile_pic_url');
            $table->text('adresse')->nullable()->after('is_verified');
            $table->boolean('newsletter')->default(false)->after('adresse');
            $table->boolean('termes_condition')->default(false)->after('newsletter');
            $table->string('statut')->default('actif')->after('termes_condition');
            $table->string('auth_code', 8)->nullable()->after('statut');
            $table->timestamp('auth_code_expires_at')->nullable()->after('auth_code');
            $table->boolean('auth_code_verified')->default(false)->after('auth_code_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées
            $table->dropColumn([
                'prenoms',
                'telephone',
                'telephone_verified_at',
                'profile_pic_url',
                'is_verified',
                'adresse',
                'newsletter',
                'termes_condition',
                'statut',
                'auth_code',
                'auth_code_expires_at',
                'auth_code_verified'
            ]);
            
            // Renommer nom en name
            $table->renameColumn('nom', 'name');
        });
    }
};