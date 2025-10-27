<?php
/**
 * Test simple du système de modification de produit
 */

echo "🧪 TEST SIMPLE DU SYSTÈME DE MODIFICATION\n";
echo "=========================================\n\n";

// Test 1: Vérifier les fichiers essentiels
echo "1️⃣ Vérification des fichiers...\n";

$files = [
    'public/js/product-edit.js' => 'Script JavaScript de modification',
    'resources/views/store/dashboard.blade.php' => 'Dashboard vendeur',
    'app/Http/Controllers/Seller/ProductController.php' => 'Contrôleur produit',
    'routes/web.php' => 'Routes web'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description: $file (MANQUANT)\n";
    }
}

echo "\n";

// Test 2: Vérifier le contenu du script JavaScript
echo "2️⃣ Vérification du script JavaScript...\n";
$jsFile = 'public/js/product-edit.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    
    $functions = [
        'editProductInternal',
        'showEditProductModal',
        'updateProduct',
        'deleteProductInternal',
        'validateFormData'
    ];
    
    foreach ($functions as $function) {
        if (strpos($jsContent, "function $function") !== false) {
            echo "✅ Fonction $function présente\n";
        } else {
            echo "❌ Fonction $function manquante\n";
        }
    }
    
    // Vérifier les logs de debug
    if (strpos($jsContent, 'console.log') !== false) {
        echo "✅ Logs de debug présents\n";
    } else {
        echo "❌ Logs de debug manquants\n";
    }
} else {
    echo "❌ Fichier JavaScript non trouvé\n";
}

echo "\n";

// Test 3: Vérifier l'inclusion du script dans le dashboard
echo "3️⃣ Vérification de l'inclusion du script...\n";
$dashboardFile = 'resources/views/store/dashboard.blade.php';
if (file_exists($dashboardFile)) {
    $dashboardContent = file_get_contents($dashboardFile);
    
    if (strpos($dashboardContent, 'product-edit.js') !== false) {
        echo "✅ Script inclus dans le dashboard\n";
    } else {
        echo "❌ Script non inclus dans le dashboard\n";
    }
    
    // Vérifier la présence du meta CSRF
    if (strpos($dashboardContent, 'csrf-token') !== false) {
        echo "✅ Meta CSRF présent\n";
    } else {
        echo "❌ Meta CSRF manquant\n";
    }
} else {
    echo "❌ Dashboard non trouvé\n";
}

echo "\n";

// Test 4: Vérifier les routes
echo "4️⃣ Vérification des routes...\n";
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    // Vérifier les routes pour les produits
    if (strpos($routesContent, 'store/api/products') !== false) {
        echo "✅ Routes produits présentes\n";
    } else {
        echo "❌ Routes produits manquantes\n";
    }
    
    // Vérifier les middlewares
    if (strpos($routesContent, 'seller') !== false) {
        echo "✅ Middleware seller présent\n";
    } else {
        echo "❌ Middleware seller manquant\n";
    }
} else {
    echo "❌ Fichier routes non trouvé\n";
}

echo "\n";

// Test 5: Vérifier le contrôleur
echo "5️⃣ Vérification du contrôleur...\n";
$controllerFile = 'app/Http/Controllers/Seller/ProductController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    // Vérifier les méthodes
    $methods = ['getProduct', 'update', 'delete'];
    foreach ($methods as $method) {
        if (strpos($controllerContent, "function $method") !== false) {
            echo "✅ Méthode $method présente\n";
        } else {
            echo "❌ Méthode $method manquante\n";
        }
    }
    
    // Vérifier la validation
    if (strpos($controllerContent, 'validate(') !== false) {
        echo "✅ Validation présente\n";
    } else {
        echo "❌ Validation manquante\n";
    }
    
    // Vérifier les logs
    if (strpos($controllerContent, 'Log::info') !== false) {
        echo "✅ Logs de debug présents\n";
    } else {
        echo "❌ Logs de debug manquants\n";
    }
} else {
    echo "❌ Contrôleur non trouvé\n";
}

echo "\n";

// Test 6: Vérifier la base de données (version simplifiée)
echo "6️⃣ Vérification de la base de données...\n";
try {
    require_once 'vendor/autoload.php';
    
    // Configuration Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Test simple de connexion
    $productsCount = \DB::table('products')->count();
    echo "✅ Connexion base de données OK ($productsCount produits)\n";
    
    // Vérifier qu'il y a des produits
    if ($productsCount > 0) {
        echo "✅ Produits disponibles pour les tests\n";
    } else {
        echo "❌ Aucun produit disponible\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur base de données: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Résumé et recommandations
echo "7️⃣ RÉSUMÉ ET RECOMMANDATIONS\n";
echo "============================\n";

$totalChecks = 0;
$passedChecks = 0;

// Compter les vérifications
$files = ['public/js/product-edit.js', 'resources/views/store/dashboard.blade.php', 'app/Http/Controllers/Seller/ProductController.php', 'routes/web.php'];
foreach ($files as $file) {
    $totalChecks++;
    if (file_exists($file)) $passedChecks++;
}

echo "Fichiers essentiels: $passedChecks/$totalChecks\n";

if ($passedChecks >= 3) {
    echo "✅ SYSTÈME PRÊT POUR LES TESTS MANUELS\n\n";
    echo "📋 INSTRUCTIONS DE TEST:\n";
    echo "1. Démarrer le serveur Laravel: php artisan serve\n";
    echo "2. Ouvrir le navigateur sur: http://127.0.0.1:8000\n";
    echo "3. Se connecter en tant que vendeur\n";
    echo "4. Aller sur /store/dashboard\n";
    echo "5. Cliquer sur l'onglet 'Produits'\n";
    echo "6. Cliquer 'Modifier' sur un produit\n";
    echo "7. Vérifier que le modal s'ouvre\n";
    echo "8. Ouvrir la console (F12) pour voir les logs\n";
    echo "9. Tester la modification\n\n";
    
    echo "🔍 POINTS À VÉRIFIER:\n";
    echo "- Modal s'ouvre avec les données du produit\n";
    echo "- Console affiche les logs de debug\n";
    echo "- Validation des champs fonctionne\n";
    echo "- Modification se sauvegarde\n";
    echo "- Notifications s'affichent\n";
} else {
    echo "❌ SYSTÈME NON PRÊT - FICHIERS MANQUANTS\n";
    echo "Vérifiez que tous les fichiers sont présents et corrects.\n";
}

echo "\n🎯 Test simple terminé!\n";
?>
