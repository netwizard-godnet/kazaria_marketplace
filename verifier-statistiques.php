<?php

/**
 * Script de Vérification du Menu Statistiques
 * Exécuter avec: php verifier-statistiques.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n========================================\n";
echo "VERIFICATION DU MENU STATISTIQUES\n";
echo "========================================\n\n";

// 1. Vérifier la permission
echo "1. PERMISSION 'view_statistics'\n";
echo "----------------------------------------\n";
$permission = App\Models\Permission::where('slug', 'view_statistics')->first();
if ($permission) {
    echo "✅ Permission existe\n";
    echo "   Nom : " . $permission->name . "\n";
    echo "   Module : " . $permission->module . "\n";
} else {
    echo "❌ Permission 'view_statistics' INTROUVABLE\n";
    echo "   Solution : Exécuter 'php artisan db:seed --class=PermissionSeeder'\n";
}
echo "\n";

// 2. Vérifier les rôles qui ont cette permission
echo "2. ROLES AVEC LA PERMISSION\n";
echo "----------------------------------------\n";
if ($permission) {
    $roles = App\Models\Role::whereHas('permissions', function($q) use ($permission) {
        $q->where('permission_id', $permission->id);
    })->get();
    
    if ($roles->count() > 0) {
        foreach ($roles as $role) {
            echo "✅ " . $role->name . " (" . $role->slug . ")\n";
        }
    } else {
        echo "⚠️  Aucun rôle n'a cette permission\n";
        echo "   Solution : Exécuter 'php artisan db:seed --class=RoleSeeder'\n";
    }
} else {
    echo "⚠️  Impossible de vérifier (permission n'existe pas)\n";
}
echo "\n";

// 3. Vérifier les utilisateurs qui peuvent voir les statistiques
echo "3. UTILISATEURS AUTORISES\n";
echo "----------------------------------------\n";
$admins = App\Models\User::where('is_admin', true)->with('role.permissions')->get();

foreach ($admins as $admin) {
    echo "👤 " . $admin->email . "\n";
    
    // Cas 1 : Super admin sans rôle
    if ($admin->is_admin && !$admin->role_id) {
        echo "   ✅ Accès : OUI (Super Admin sans rôle - accès total)\n";
    }
    // Cas 2 : Utilisateur avec rôle
    else if ($admin->role) {
        $hasPermission = $admin->role->hasPermission('view_statistics');
        if ($hasPermission) {
            echo "   ✅ Accès : OUI (via rôle " . $admin->role->name . ")\n";
        } else {
            echo "   ❌ Accès : NON (rôle " . $admin->role->name . " n'a pas la permission)\n";
        }
    }
    // Cas 3 : Utilisateur admin sans rôle
    else {
        echo "   ❌ Accès : NON (aucun rôle assigné et is_admin = false)\n";
    }
    echo "\n";
}

// 4. Test du helper
echo "4. TEST DU HELPER\n";
echo "----------------------------------------\n";
if (function_exists('canAccess')) {
    echo "✅ Fonction canAccess() disponible\n";
    
    // Test avec le premier admin
    $testAdmin = $admins->first();
    if ($testAdmin) {
        auth()->login($testAdmin);
        $canSeeStats = canAccess('view_statistics');
        echo "   Test avec " . $testAdmin->email . " : " . ($canSeeStats ? "✅ PEUT voir" : "❌ NE PEUT PAS voir") . "\n";
        auth()->logout();
    }
} else {
    echo "❌ Fonction canAccess() NON disponible\n";
    echo "   Solution : Exécuter 'composer dump-autoload'\n";
}
echo "\n";

// 5. Vérifier la route
echo "5. VERIFICATION DE LA ROUTE\n";
echo "----------------------------------------\n";
try {
    $route = app('router')->getRoutes()->getByName('admin.statistics.index');
    if ($route) {
        echo "✅ Route 'admin.statistics.index' existe\n";
        echo "   URI : " . $route->uri() . "\n";
        
        // Vérifier les middlewares
        $middlewares = $route->middleware();
        echo "   Middlewares : " . implode(', ', $middlewares) . "\n";
        
        if (in_array('permission:view_statistics', $middlewares)) {
            echo "   ✅ Protégée par 'permission:view_statistics'\n";
        } else {
            echo "   ⚠️  PAS protégée par permission\n";
        }
    } else {
        echo "❌ Route 'admin.statistics.index' INTROUVABLE\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
echo "\n";

// Conclusion
echo "========================================\n";
echo "CONCLUSION\n";
echo "========================================\n";

$allGood = true;

if (!$permission) {
    echo "❌ La permission n'existe pas\n";
    $allGood = false;
}

if ($permission && $roles->count() === 0) {
    echo "❌ Aucun rôle n'a la permission\n";
    $allGood = false;
}

if (!function_exists('canAccess')) {
    echo "❌ Le helper n'est pas chargé\n";
    $allGood = false;
}

if ($allGood) {
    echo "✅ Le menu Statistiques est correctement configuré !\n";
    echo "\nSi le menu ne s'affiche pas :\n";
    echo "1. Videz les caches : php artisan cache:clear && php artisan view:clear\n";
    echo "2. Reconnectez-vous au dashboard admin\n";
    echo "3. Vérifiez que votre utilisateur a bien un rôle avec la permission\n";
} else {
    echo "\n⚠️  Des problèmes ont été détectés.\n";
    echo "Suivez les solutions indiquées ci-dessus.\n";
}

echo "\n";

