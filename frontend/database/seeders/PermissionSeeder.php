<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users Management
            ['name' => 'Voir les utilisateurs', 'slug' => 'view_users', 'description' => 'Peut voir la liste des utilisateurs', 'module' => 'users'],
            ['name' => 'Créer des utilisateurs', 'slug' => 'create_users', 'description' => 'Peut créer de nouveaux utilisateurs', 'module' => 'users'],
            ['name' => 'Modifier les utilisateurs', 'slug' => 'edit_users', 'description' => 'Peut modifier les informations des utilisateurs', 'module' => 'users'],
            ['name' => 'Supprimer les utilisateurs', 'slug' => 'delete_users', 'description' => 'Peut supprimer des utilisateurs', 'module' => 'users'],
            
            // Products Management
            ['name' => 'Voir les produits', 'slug' => 'view_products', 'description' => 'Peut voir la liste des produits', 'module' => 'products'],
            ['name' => 'Créer des produits', 'slug' => 'create_products', 'description' => 'Peut créer de nouveaux produits', 'module' => 'products'],
            ['name' => 'Modifier les produits', 'slug' => 'edit_products', 'description' => 'Peut modifier les produits', 'module' => 'products'],
            ['name' => 'Supprimer les produits', 'slug' => 'delete_products', 'description' => 'Peut supprimer des produits', 'module' => 'products'],
            
            // Orders Management
            ['name' => 'Voir les commandes', 'slug' => 'view_orders', 'description' => 'Peut voir la liste des commandes', 'module' => 'orders'],
            ['name' => 'Gérer les commandes', 'slug' => 'manage_orders', 'description' => 'Peut modifier le statut des commandes', 'module' => 'orders'],
            ['name' => 'Annuler les commandes', 'slug' => 'cancel_orders', 'description' => 'Peut annuler des commandes', 'module' => 'orders'],
            
            // Stores Management
            ['name' => 'Voir les boutiques', 'slug' => 'view_stores', 'description' => 'Peut voir la liste des boutiques', 'module' => 'stores'],
            ['name' => 'Approuver les boutiques', 'slug' => 'approve_stores', 'description' => 'Peut approuver des boutiques', 'module' => 'stores'],
            ['name' => 'Supprimer les boutiques', 'slug' => 'delete_stores', 'description' => 'Peut supprimer des boutiques', 'module' => 'stores'],
            
            // Categories Management
            ['name' => 'Gérer les catégories', 'slug' => 'manage_categories', 'description' => 'Peut créer, modifier et supprimer des catégories', 'module' => 'categories'],
            
            // Settings Management
            ['name' => 'Gérer les paramètres', 'slug' => 'manage_settings', 'description' => 'Peut modifier les paramètres du site', 'module' => 'settings'],
            
            // Roles & Permissions
            ['name' => 'Gérer les rôles', 'slug' => 'manage_roles', 'description' => 'Peut créer, modifier et supprimer des rôles', 'module' => 'roles'],
            ['name' => 'Gérer les permissions', 'slug' => 'manage_permissions', 'description' => 'Peut créer, modifier et supprimer des permissions', 'module' => 'permissions'],
            
            // Reports
            ['name' => 'Voir les rapports', 'slug' => 'view_reports', 'description' => 'Peut voir les rapports et statistiques', 'module' => 'reports'],
            ['name' => 'Exporter les rapports', 'slug' => 'export_reports', 'description' => 'Peut exporter les rapports', 'module' => 'reports'],
            
            // Messages
            ['name' => 'Gérer les messages', 'slug' => 'manage_messages', 'description' => 'Peut voir et répondre aux messages', 'module' => 'messages'],
            
            // Payments
            ['name' => 'Gérer les paiements', 'slug' => 'manage_payments', 'description' => 'Peut gérer les paiements et remboursements', 'module' => 'payments'],
        ];
        
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        
        $this->command->info('Permissions créées avec succès!');
    }
}
