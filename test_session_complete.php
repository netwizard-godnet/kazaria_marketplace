<?php
/**
 * Test complet de la session
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST COMPLET DE LA SESSION ===\n\n";

// Test 1: Vérifier l'état d'authentification
echo "🔍 TEST 1: État d'authentification\n";
echo "===================================\n";

try {
    $authCheck = \Illuminate\Support\Facades\Auth::check();
    $user = \Illuminate\Support\Facades\Auth::user();
    
    echo "📋 Auth::check(): " . ($authCheck ? 'true' : 'false') . "\n";
    
    if ($user) {
        echo "📋 Utilisateur connecté: " . $user->nom . " " . $user->prenoms . "\n";
        echo "📋 ID utilisateur: " . $user->id . "\n";
        echo "📋 Email: " . $user->email . "\n";
        echo "📋 Vendeur: " . ($user->is_seller ? 'Oui' : 'Non') . "\n";
    } else {
        echo "📋 Aucun utilisateur connecté\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Vérifier les sessions en base de données
echo "🔍 TEST 2: Sessions en base de données\n";
echo "========================================\n";

try {
    $connection = \Illuminate\Support\Facades\DB::connection();
    $sessions = $connection->select("SELECT * FROM sessions ORDER BY last_activity DESC LIMIT 10");
    
    echo "📋 Sessions en base de données:\n";
    foreach ($sessions as $session) {
        $data = unserialize(base64_decode($session->payload));
        $userId = $data['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'] ?? null;
        $lastActivity = date('Y-m-d H:i:s', $session->last_activity);
        $isExpired = (time() - $session->last_activity) > (config('session.lifetime') * 60);
        
        echo "   📋 ID: " . $session->id . "\n";
        echo "   📋 User ID: " . ($userId ?: 'Aucun') . "\n";
        echo "   📋 Last Activity: " . $lastActivity . "\n";
        echo "   📋 Expired: " . ($isExpired ? 'Oui' : 'Non') . "\n";
        echo "   📋 ---\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Vérifier la configuration des sessions
echo "🔍 TEST 3: Configuration des sessions\n";
echo "=======================================\n";

try {
    $sessionConfig = config('session');
    
    echo "📋 Configuration de session :\n";
    echo "   📋 Driver: " . ($sessionConfig['driver'] ?? 'Non défini') . "\n";
    echo "   📋 Lifetime: " . ($sessionConfig['lifetime'] ?? 'Non défini') . " minutes\n";
    echo "   📋 Encrypt: " . ($sessionConfig['encrypt'] ? 'true' : 'false') . "\n";
    echo "   📋 Secure: " . ($sessionConfig['secure'] ? 'true' : 'false') . "\n";
    echo "   📋 HTTP Only: " . ($sessionConfig['http_only'] ? 'true' : 'false') . "\n";
    echo "   📋 Same Site: " . ($sessionConfig['same_site'] ?? 'Non défini') . "\n";
    echo "   📋 Cookie Name: " . ($sessionConfig['cookie'] ?? 'Non défini') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Vérifier les middlewares
echo "🔍 TEST 4: Middlewares\n";
echo "========================\n";

try {
    $middlewareAliases = app('router')->getMiddleware();
    
    echo "📋 Middlewares enregistrés:\n";
    foreach ($middlewareAliases as $alias => $class) {
        if (strpos($alias, 'web') !== false || strpos($alias, 'auth') !== false) {
            echo "   📋 $alias: $class\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Vérifier les routes avec @auth
echo "🔍 TEST 5: Routes avec @auth\n";
echo "=============================\n";

try {
    $routes = app('router')->getRoutes();
    
    $authRoutes = [
        '/profil' => 'GET',
        '/store/dashboard' => 'GET',
        '/store/create' => 'GET',
    ];
    
    foreach ($authRoutes as $routePath => $method) {
        try {
            $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($routePath, $method));
            if ($route) {
                $middleware = $route->gatherMiddleware();
                echo "📋 Route $routePath ($method): ✅ Existe\n";
                echo "   📋 Middleware: " . implode(', ', $middleware) . "\n";
            }
        } catch (Exception $e) {
            echo "📋 Route $routePath ($method): ❌ " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Vérifier les vues Blade
echo "🔍 TEST 6: Vues Blade\n";
echo "=====================\n";

try {
    $bladeFiles = [
        'resources/views/layouts/header.blade.php',
        'resources/views/profil.blade.php',
    ];
    
    foreach ($bladeFiles as $file) {
        if (file_exists($file)) {
            echo "📋 $file: ✅ Fichier existe\n";
            
            $content = file_get_contents($file);
            
            if (strpos($content, '@auth') !== false) {
                echo "   📋 @auth: ✅ Directive présente\n";
            } else {
                echo "   📋 @auth: ❌ Directive manquante\n";
            }
            
            if (strpos($content, '@else') !== false) {
                echo "   📋 @else: ✅ Directive présente\n";
            } else {
                echo "   📋 @else: ❌ Directive manquante\n";
            }
            
        } else {
            echo "📋 $file: ❌ Fichier manquant\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Vérifier la configuration de l'application
echo "🔍 TEST 7: Configuration de l'application\n";
echo "==========================================\n";

try {
    $appConfig = config('app');
    $authConfig = config('auth');
    
    echo "📋 Configuration de l'application :\n";
    echo "   📋 Environment: " . ($appConfig['env'] ?? 'Non défini') . "\n";
    echo "   📋 Debug: " . ($appConfig['debug'] ? 'true' : 'false') . "\n";
    echo "   📋 Key: " . (strlen($appConfig['key'] ?? '') > 0 ? 'Définie' : 'Non définie') . "\n";
    
    echo "\n📋 Configuration d'authentification :\n";
    echo "   📋 Default Guard: " . ($authConfig['defaults']['guard'] ?? 'Non défini') . "\n";
    echo "   📋 Default Provider: " . ($authConfig['defaults']['provider'] ?? 'Non défini') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 8: Vérifier les cookies
echo "🔍 TEST 8: Cookies\n";
echo "==================\n";

try {
    $cookies = $_COOKIE;
    
    echo "📋 Cookies présents:\n";
    foreach ($cookies as $name => $value) {
        if (strpos($name, 'session') !== false || strpos($name, 'laravel') !== false) {
            echo "   📋 $name: " . substr($value, 0, 20) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

echo "🎯 RÉSULTAT DU TEST :\n";
echo "=====================\n";
echo "✅ État d'authentification vérifié\n";
echo "✅ Sessions en base de données analysées\n";
echo "✅ Configuration vérifiée\n";
echo "✅ Middlewares vérifiés\n";
echo "✅ Routes vérifiées\n";
echo "✅ Vues Blade vérifiées\n";
echo "✅ Configuration de l'application vérifiée\n";
echo "✅ Cookies vérifiés\n";
echo "\n📋 PROCHAINES ÉTAPES :\n";
echo "1. Analyser les résultats\n";
echo "2. Corriger les problèmes identifiés\n";
echo "3. Tester la reconnexion\n";
?>
