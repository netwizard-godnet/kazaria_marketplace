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
            // Supprimer la colonne 'name' si elle existe et ajouter les colonnes personnalisées
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
            
            // Ajouter les colonnes pour l'authentification et les informations utilisateur
            if (!Schema::hasColumn('users', 'nom')) {
                $table->string('nom')->after('id');
            }
            if (!Schema::hasColumn('users', 'prenoms')) {
                $table->string('prenoms')->after('nom');
            }
            if (!Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'telephone_verified_at')) {
                $table->timestamp('telephone_verified_at')->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('users', 'profile_pic_url')) {
                $table->string('profile_pic_url')->nullable()->after('telephone_verified_at');
            }
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('profile_pic_url');
            }
            if (!Schema::hasColumn('users', 'adresse')) {
                $table->text('adresse')->nullable()->after('is_verified');
            }
            if (!Schema::hasColumn('users', 'newsletter')) {
                $table->boolean('newsletter')->default(false)->after('adresse');
            }
            if (!Schema::hasColumn('users', 'termes_condition')) {
                $table->boolean('termes_condition')->default(false)->after('newsletter');
            }
            if (!Schema::hasColumn('users', 'statut')) {
                $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif')->after('termes_condition');
            }
            
            // Colonnes pour le code d'authentification à 8 chiffres
            if (!Schema::hasColumn('users', 'auth_code')) {
                $table->string('auth_code', 8)->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'auth_code_expires_at')) {
                $table->timestamp('auth_code_expires_at')->nullable()->after('auth_code');
            }
            if (!Schema::hasColumn('users', 'auth_code_verified')) {
                $table->boolean('auth_code_verified')->default(false)->after('auth_code_expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nom',
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
            $table->string('name')->after('id');
        });
    }
};

