<?php
/**
 * Vérifier la session après connexion via navigateur
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VÉRIFICATION SESSION NAVIGATEUR ===\n\n";

// Test 1: Vérifier les sessions en base de données
echo "🔍 TEST 1: Sessions en base de données\n";
echo "======================================\n";

try {
    $sessionCount = \Illuminate\Support\Facades\DB::table('sessions')->count();
    echo "📋 Nombre de sessions: $sessionCount\n";
    
    // Afficher les sessions avec user_id
    $sessionsWithUser = \Illuminate\Support\Facades\DB::table('sessions')
        ->whereNotNull('user_id')
        ->orderBy('last_activity', 'desc')
        ->get();
        
    echo "📋 Sessions avec user_id: " . $sessionsWithUser->count() . "\n";
    
    foreach ($sessionsWithUser as $session) {
        echo "📋 Session:\n";
        echo "   📋 ID: " . $session->id . "\n";
        echo "   📋 User ID: " . $session->user_id . "\n";
        echo "   📋 IP: " . $session->ip_address . "\n";
        echo "   📋 Last Activity: " . date('Y-m-d H:i:s', $session->last_activity) . "\n";
        
        // Décoder le payload pour voir le contenu
        $payload = base64_decode($session->payload);
        echo "   📋 Payload (début): " . substr($payload, 0, 100) . "...\n";
        
        // Chercher des indices d'authentification
        if (strpos($payload, 'login_') !== false) {
            echo "   🔍 Contient 'login_': Oui\n";
        }
        if (strpos($payload, 'auth_') !== false) {
            echo "   🔍 Contient 'auth_': Oui\n";
        }
        if (strpos($payload, 'user_id') !== false) {
            echo "   🔍 Contient 'user_id': Oui\n";
        }
        echo "   ---\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Vérifier l'état d'authentification
echo "🔍 TEST 2: État d'authentification\n";
echo "===================================\n";

try {
    $isAuthenticated = \Illuminate\Support\Facades\Auth::check();
    $user = \Illuminate\Support\Facades\Auth::user();
    
    echo "📋 Auth::check(): " . ($isAuthenticated ? "true" : "false") . "\n";
    
    if ($user) {
        echo "📋 Utilisateur connecté: " . $user->nom . " " . $user->prenoms . "\n";
        echo "📋 Email: " . $user->email . "\n";
        echo "📋 ID: " . $user->id . "\n";
        echo "📋 Vendeur: " . ($user->is_seller ? "Oui" : "Non") . "\n";
    } else {
        echo "📋 Aucun utilisateur connecté\n";
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
    
    // Afficher un extrait du HTML autour de la section d'authentification
    echo "📋 Extrait du HTML (section auth):\n";
    $lines = explode("\n", $html);
    $inAuthSection = false;
    $authLineCount = 0;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        // Détecter le début de la section auth
        if (strpos($line, 'id="auth-section"') !== false) {
            $inAuthSection = true;
            $authLineCount = 0;
        }
        
        // Afficher les lignes de la section auth
        if ($inAuthSection && $authLineCount < 50) {
            if (trim($line) !== '') {
                echo "   " . ($i + 1) . ": " . trim($line) . "\n";
            }
            $authLineCount++;
        }
        
        // Arrêter après 50 lignes
        if ($authLineCount >= 50) {
            break;
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

echo "🎯 DIAGNOSTIC :\n";
echo "===============\n";
echo "📋 Si Auth::check() = false mais qu'il y a des sessions avec user_id :\n";
echo "   → Problème de session non démarrée dans le contexte\n";
echo "   → Middleware de session non appliqué\n";
echo "   → Configuration de session incorrecte\n";
echo "\n📋 SOLUTIONS :\n";
echo "   1. Tester avec un navigateur réel\n";
echo "   2. Vérifier que StartSession est appliqué\n";
echo "   3. Vérifier que la session est démarrée avant l'authentification\n";
echo "   4. Vérifier la configuration de session\n";
?>
