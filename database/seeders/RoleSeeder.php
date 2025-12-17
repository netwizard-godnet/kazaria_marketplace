<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou récupérer le rôle Super Admin avec toutes les permissions
        $superAdmin = Role::firstOrCreate([
            'slug' => 'super-admin',
        ], [
            'name' => 'Super Admin',
            'description' => 'Accès complet à toutes les fonctionnalités du dashboard admin',
            'is_active' => true,
        ]);
        
        // Créer ou récupérer le rôle Moderator
        $moderator = Role::firstOrCreate([
            'slug' => 'moderator',
        ], [
            'name' => 'Moderator',
            'description' => 'Peut gérer les utilisateurs, produits et commandes (sans accès aux paramètres)',
            'is_active' => true,
        ]);
        
        // Créer ou récupérer le rôle Support
        $support = Role::firstOrCreate([
            'slug' => 'support',
        ], [
            'name' => 'Support',
            'description' => 'Peut répondre aux messages clients et gérer les commandes',
            'is_active' => true,
        ]);
        
        // Réinitialiser les permissions
        $superAdmin->permissions()->detach();
        $moderator->permissions()->detach();
        $support->permissions()->detach();
        
        // Récupérer toutes les permissions
        $allPermissions = Permission::all();
        
        // Super Admin a toutes les permissions
        $superAdmin->permissions()->attach($allPermissions->pluck('id')->toArray());
        
        // Moderator a les permissions pour gérer les utilisateurs, produits, commandes, boutiques, contenus et statistiques
        $moderatorPermissions = $allPermissions->filter(function($permission) {
            return in_array($permission->module, [
                'users', 'products', 'orders', 'stores', 'categories', 'subcategories',
                'messages', 'statistics', 'banners', 'carousel', 'brands', 'coupons',
                'attributes', 'payments'
            ]);
        });
        $moderator->permissions()->attach($moderatorPermissions->pluck('id')->toArray());
        
        // Support a les permissions pour voir les commandes, statistiques, gérer les messages
        $supportPermissions = $allPermissions->filter(function($permission) {
            return in_array($permission->module, ['orders', 'messages', 'statistics']);
        });
        $support->permissions()->attach($supportPermissions->pluck('id')->toArray());
        
        $this->command->info('Rôles créés avec succès!');
        $this->command->info('Super Admin: ' . $superAdmin->permissions->count() . ' permissions');
        $this->command->info('Moderator: ' . $moderator->permissions->count() . ' permissions');
        $this->command->info('Support: ' . $support->permissions->count() . ' permissions');
    }
}

