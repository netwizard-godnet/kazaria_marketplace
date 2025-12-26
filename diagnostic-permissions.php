<?php

/**
 * Script de Diagnostic des Permissions Admin
 * 
 * Exécuter avec: php diagnostic-permissions.php
 */

// Charger Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n========================================\n";
echo "DIAGNOSTIC DU SYSTEME DE PERMISSIONS\n";
echo "========================================\n\n";

// 1. Vérifier les permissions
echo "1. PERMISSIONS DANS LA BASE DE DONNEES\n";
echo "----------------------------------------\n";
$permissions = App\Models\Permission::all();
echo "Total : " . $permissions->count() . " permissions\n";
echo "Permissions par module :\n";
foreach ($permissions->groupBy('module') as $module => $perms) {
    echo "  - " . $module . " : " . $perms->count() . " permission(s)\n";
}
echo "\n";

// 2. Vérifier les rôles
echo "2. ROLES ET LEURS PERMISSIONS\n";
echo "----------------------------------------\n";
$roles = App\Models\Role::with('permissions')->get();
foreach ($roles as $role) {
    echo "Role : " . $role->name . " (" . $role->slug . ")\n";
    echo "  Statut : " . ($role->is_active ? '✅ Actif' : '❌ Inactif') . "\n";
    echo "  Permissions : " . $role->permissions->count() . "\n";
    if ($role->permissions->count() > 0) {
        echo "  Modules : " . $role->permissions->pluck('module')->unique()->implode(', ') . "\n";
    }
    echo "\n";
}

// 3. Vérifier les utilisateurs admin
echo "3. UTILISATEURS ADMINISTRATEURS\n";
echo "----------------------------------------\n";
$admins = App\Models\User::where('is_admin', true)->with('role.permissions')->get();
echo "Total : " . $admins->count() . " administrateur(s)\n\n";

foreach ($admins as $admin) {
    echo "👤 " . $admin->email . "\n";
    echo "   is_admin : " . ($admin->is_admin ? '✅ Oui' : '❌ Non') . "\n";
    echo "   role_id : " . ($admin->role_id ? $admin->role_id : '⚠️  Aucun rôle assigné') . "\n";
    
    if ($admin->role) {
        echo "   Rôle : " . $admin->role->name . "\n";
        echo "   Permissions du rôle : " . $admin->role->permissions->count() . "\n";
        
        // Tester quelques permissions clés
        $keyPermissions = [
            'view_users',
            'view_products', 
            'view_orders',
            'manage_messages',
            'manage_payments',
            'view_statistics',
            'manage_banners',
        ];
        
        echo "   Test des permissions clés :\n";
        foreach ($keyPermissions as $perm) {
            $has = $admin->role->hasPermission($perm);
            echo "     - " . $perm . " : " . ($has ? '✅' : '❌') . "\n";
        }
    } else if ($admin->is_admin && !$admin->role_id) {
        echo "   ⚠️  Super Admin sans rôle (accès total par défaut)\n";
    }
    echo "\n";
}

// 4. Vérifier le helper
echo "4. VERIFICATION DU HELPER\n";
echo "----------------------------------------\n";
if (function_exists('canAccess')) {
    echo "✅ Fonction canAccess() chargée\n";
} else {
    echo "❌ Fonction canAccess() NON chargée\n";
    echo "   Solution : Exécuter 'composer dump-autoload'\n";
}

if (function_exists('canAccessAny')) {
    echo "✅ Fonction canAccessAny() chargée\n";
} else {
    echo "❌ Fonction canAccessAny() NON chargée\n";
}

if (function_exists('isSuperAdmin')) {
    echo "✅ Fonction isSuperAdmin() chargée\n";
} else {
    echo "❌ Fonction isSuperAdmin() NON chargée\n";
}
echo "\n";

// 5. Recommandations
echo "5. RECOMMANDATIONS\n";
echo "----------------------------------------\n";

$issues = [];

// Vérifier si toutes les permissions sont créées
$expectedPermissions = [
    'view_users', 'create_users', 'edit_users', 'delete_users',
    'view_products', 'create_products', 'edit_products', 'delete_products',
    'view_orders', 'manage_orders', 'cancel_orders',
    'view_stores', 'approve_stores', 'delete_stores',
    'manage_categories', 'manage_settings', 'manage_roles', 'manage_permissions',
    'view_reports', 'export_reports', 'manage_messages', 'manage_payments',
    'view_statistics', 'manage_banners', 'manage_carousel', 'manage_brands',
    'manage_coupons', 'manage_subcategories', 'manage_attributes'
];

$existingSlugs = $permissions->pluck('slug')->toArray();
$missingPermissions = array_diff($expectedPermissions, $existingSlugs);

if (count($missingPermissions) > 0) {
    $issues[] = "❌ Permissions manquantes : " . implode(', ', $missingPermissions);
    echo "❌ Permissions manquantes détectées\n";
    echo "   Solution : Exécuter 'php artisan db:seed --class=PermissionSeeder'\n\n";
} else {
    echo "✅ Toutes les permissions sont créées\n\n";
}

// Vérifier si les rôles ont des permissions
foreach ($roles as $role) {
    if ($role->permissions->count() === 0) {
        $issues[] = "⚠️  Le rôle '{$role->name}' n'a aucune permission";
        echo "⚠️  Le rôle '{$role->name}' n'a aucune permission\n";
        echo "   Solution : Exécuter 'php artisan db:seed --class=RoleSeeder'\n\n";
    }
}

// Vérifier si les admins ont des rôles
$adminsWithoutRole = $admins->filter(function($admin) {
    return $admin->is_admin && !$admin->role_id;
});

if ($adminsWithoutRole->count() > 0) {
    echo "⚠️  " . $adminsWithoutRole->count() . " administrateur(s) sans rôle assigné\n";
    echo "   Ces admins ont accès total par défaut (ancien système)\n";
    echo "   Pour utiliser le nouveau système, assignez-leur un rôle :\n\n";
    echo "   php artisan tinker\n";
    echo "   >>> \$admin = App\\Models\\User::find(" . $adminsWithoutRole->first()->id . ");\n";
    echo "   >>> \$role = App\\Models\\Role::where('slug', 'super-admin')->first();\n";
    echo "   >>> \$admin->role_id = \$role->id;\n";
    echo "   >>> \$admin->save();\n\n";
}

// Vérifier le helper
if (!function_exists('canAccess')) {
    $issues[] = "❌ Helper de permissions non chargé";
    echo "❌ Helper de permissions non chargé\n";
    echo "   Solution : Exécuter 'composer dump-autoload'\n\n";
}

if (count($issues) === 0) {
    echo "✅ Aucun problème détecté ! Le système fonctionne correctement.\n\n";
} else {
    echo "\n⚠️  " . count($issues) . " problème(s) détecté(s)\n\n";
}

echo "========================================\n";
echo "FIN DU DIAGNOSTIC\n";
echo "========================================\n\n";

