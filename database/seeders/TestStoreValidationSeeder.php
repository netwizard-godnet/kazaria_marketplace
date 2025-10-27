<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\User;
use App\Models\Category;

class TestStoreValidationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur vendeur de test
        $seller = User::firstOrCreate(
            ['email' => 'vendeur.test@example.com'],
            [
                'nom' => 'Test',
                'prenoms' => 'Vendeur',
                'telephone' => '+22512345678',
                'is_seller' => true,
                'is_verified' => true,
                'password' => bcrypt('password'),
            ]
        );

        // Obtenir une catégorie
        $category = Category::first();

        if (!$category) {
            $this->command->error('Aucune catégorie trouvée. Veuillez d\'abord exécuter CategorySyncSeeder.');
            return;
        }

        // Créer une boutique en attente de validation
        $store = Store::firstOrCreate(
            ['name' => 'Boutique Test Validation'],
            [
                'user_id' => $seller->id,
                'slug' => 'boutique-test-validation',
                'description' => 'Cette boutique est créée pour tester le système de validation. Elle contient tous les documents nécessaires et respecte nos conditions d\'utilisation.',
                'category_id' => $category->id,
                'phone' => '+22512345678',
                'email' => 'boutique.test@example.com',
                'address' => '123 Rue de Test, Abidjan',
                'city' => 'Abidjan',
                'status' => 'pending',
                'is_verified' => false,
                'logo' => 'stores/logos/test-logo.jpg',
                'banner' => 'stores/banners/test-banner.jpg',
                'dfe_document' => 'stores/documents/test-dfe.pdf',
                'commerce_register' => 'stores/documents/test-register.pdf',
            ]
        );

        $this->command->info("✅ Boutique de test créée: {$store->name}");
        $this->command->info("   - Statut: {$store->status}");
        $this->command->info("   - Vendeur: {$seller->prenoms} {$seller->nom}");
        $this->command->info("   - Email: {$seller->email}");
        $this->command->info("   - Mot de passe: password");
    }
}