<?php
/**
 * Test d'authentification via requête HTTP simulée
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST AUTHENTIFICATION HTTP ===\n\n";

// Test 1: Simuler une connexion via le navigateur
echo "🔍 TEST 1: Simulation de connexion\n";
echo "===================================\n";

try {
    // Créer une requête simulée
    $request = \Illuminate\Http\Request::create('/authentification', 'POST', [
        'email' => 'moses@example.com',
        'password' => 'password123'
    ]);
    
    // Simuler l'authentification
    $credentials = $request->only('email', 'password');
    $authenticated = \Illuminate\Support\Facades\Auth::attempt($credentials);
    
    echo "📋 Tentative d'authentification: " . ($authenticated ? "✅ Succès" : "❌ Échec") . "\n";
    
    if ($authenticated) {
        $user = \Illuminate\Support\Facades\Auth::user();
        echo "📋 Utilisateur connecté: " . $user->nom . " " . $user->prenoms . "\n";
        echo "📋 Email: " . $user->email . "\n";
        echo "📋 ID: " . $user->id . "\n";
        echo "📋 Vendeur: " . ($user->is_seller ? "Oui" : "Non") . "\n";
        
        // Vérifier la session
        $sessionId = session_id();
        echo "📋 Session ID: " . ($sessionId ?: 'Aucune') . "\n";
        
        // Vérifier les données de session
        $sessionData = session()->all();
        echo "📋 Données de session:\n";
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'login_') === 0 || strpos($key, 'auth_') === 0 || strpos($key, 'laravel_session') === 0) {
                echo "   📋 $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
            }
        }
        
        // Vérifier Auth::check()
        $isAuthenticated = \Illuminate\Support\Facades\Auth::check();
        echo "📋 Auth::check(): " . ($isAuthenticated ? "true" : "false") . "\n";
        
    } else {
        echo "❌ Échec de l'authentification\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Vérifier la session en base de données
echo "🔍 TEST 2: Session en base de données\n";
echo "=====================================\n";

try {
    $sessionCount = \Illuminate\Support\Facades\DB::table('sessions')->count();
    echo "📋 Nombre de sessions: $sessionCount\n";
    
    // Afficher la session la plus récente
    $latestSession = \Illuminate\Support\Facades\DB::table('sessions')
        ->orderBy('last_activity', 'desc')
        ->first();
        
    if ($latestSession) {
        echo "📋 Session la plus récente:\n";
        echo "   📋 ID: " . $latestSession->id . "\n";
        echo "       User ID: " . ($latestSession->user_id ?: 'NULL') . "\n";
        echo "       IP: " . $latestSession->ip_address . "\n";
        echo "       Last Activity: " . date('Y-m-d H:i:s', $latestSession->last_activity) . "\n";
        
        // Décoder le payload pour voir le contenu
        $payload = base64_decode($latestSession->payload);
        echo "       Payload (début): " . substr($payload, 0, 200) . "...\n";
        
        // Chercher des indices d'authentification dans le payload
        if (strpos($payload, 'login_') !== false) {
            echo "       🔍 Contient 'login_': Oui\n";
        }
        if (strpos($payload, 'auth_') !== false) {
            echo "       🔍 Contient 'auth_': Oui\n";
        }
        if (strpos($payload, 'user_id') !== false) {
            echo "       🔍 Contient 'user_id': Oui\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Test de la directive @auth
echo "🔍 TEST 3: Test de la directive @auth\n";
echo "=====================================\n";

try {
    // Simuler le rendu d'une vue avec @auth
    $view = view('layouts.header');
    $html = $view->render();
    
    // Chercher des indices dans le HTML
    if (strpos($html, 'Bonjour') !== false) {
        echo "📋 Contient 'Bonjour': Oui (utilisateur connecté)\n";
    } else {
        echo "📋 Contient 'Bonjour': Non (utilisateur non connecté)\n";
    }
    
    if (strpos($html, 'Se connecter') !== false) {
        echo "📋 Contient 'Se connecter': Oui (utilisateur non connecté)\n";
    } else {
        echo "📋 Contient 'Se connecter': Non (utilisateur connecté)\n";
    }
    
    // Afficher un extrait du HTML
    echo "📋 Extrait du HTML (lignes 150-200):\n";
    $lines = explode("\n", $html);
    for ($i = 149; $i < 200 && $i < count($lines); $i++) {
        if (trim($lines[$i]) !== '') {
            echo "   " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

echo "🎯 DIAGNOSTIC FINAL :\n";
echo "=====================\n";
echo "📋 Si Auth::attempt() réussit mais @auth ne fonctionne pas :\n";
echo "   → Problème de session non persistante\n";
echo "   → Middleware de session non appliqué\n";
echo "   → Configuration de session incorrecte\n";
echo "\n📋 SOLUTIONS :\n";
echo "   1. Vérifier que StartSession est dans le bon ordre\n";
echo "   2. Vérifier que la session est démarrée avant l'authentification\n";
echo "   3. Tester avec un navigateur réel\n";
?>
