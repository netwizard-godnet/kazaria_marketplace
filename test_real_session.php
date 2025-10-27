<?php
/**
 * Test de session avec requête HTTP réelle
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST SESSION RÉELLE ===\n\n";

// Test 1: Simuler une requête HTTP complète
echo "🔍 TEST 1: Simulation requête HTTP\n";
echo "===================================\n";

try {
    // Créer une requête POST vers /authentification
    $request = \Illuminate\Http\Request::create('/authentification', 'POST', [
        'email' => 'wilsonmoise005@gmail.com',
        'password' => 'password123'
    ]);
    
    // Ajouter des headers pour simuler un navigateur
    $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $request->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
    $request->headers->set('Accept-Language', 'fr-FR,fr;q=0.9,en;q=0.8');
    $request->headers->set('Accept-Encoding', 'gzip, deflate');
    $request->headers->set('Connection', 'keep-alive');
    $request->headers->set('Upgrade-Insecure-Requests', '1');
    
    // Simuler l'authentification
    $credentials = $request->only('email', 'password');
    $authenticated = \Illuminate\Support\Facades\Auth::attempt($credentials);
    
    echo "📋 Authentification: " . ($authenticated ? "✅ Succès" : "❌ Échec") . "\n";
    
    if ($authenticated) {
        $user = \Illuminate\Support\Facades\Auth::user();
        echo "📋 Utilisateur: " . $user->nom . " " . $user->prenoms . "\n";
        echo "📋 Email: " . $user->email . "\n";
        echo "📋 ID: " . $user->id . "\n";
        
        // Vérifier la session
        $sessionId = session_id();
        echo "📋 Session ID: " . ($sessionId ?: 'Aucune') . "\n";
        
        // Vérifier Auth::check()
        $isAuthenticated = \Illuminate\Support\Facades\Auth::check();
        echo "📋 Auth::check(): " . ($isAuthenticated ? "true" : "false") . "\n";
        
        // Vérifier les données de session
        $sessionData = session()->all();
        echo "📋 Données de session:\n";
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'login_') === 0 || strpos($key, 'auth_') === 0 || strpos($key, 'laravel_session') === 0) {
                echo "   📋 $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
            }
        }
        
        // Test de la directive @auth
        echo "\n📋 Test de la directive @auth:\n";
        $view = view('layouts.header');
        $html = $view->render();
        
        if (strpos($html, 'Bonjour') !== false) {
            echo "   📋 Contient 'Bonjour': Oui (utilisateur connecté)\n";
        } else {
            echo "   📋 Contient 'Bonjour': Non (utilisateur non connecté)\n";
        }
        
        if (strpos($html, 'Se connecter') !== false) {
            echo "   📋 Contient 'Se connecter': Oui (utilisateur non connecté)\n";
        } else {
            echo "   📋 Contient 'Se connecter': Non (utilisateur connecté)\n";
        }
        
        // Afficher un extrait du HTML
        echo "\n📋 Extrait du HTML (section auth):\n";
        $lines = explode("\n", $html);
        $inAuthSection = false;
        $authLineCount = 0;
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            
            if (strpos($line, 'id="auth-section"') !== false) {
                $inAuthSection = true;
                $authLineCount = 0;
            }
            
            if ($inAuthSection && $authLineCount < 20) {
                if (trim($line) !== '') {
                    echo "   " . ($i + 1) . ": " . trim($line) . "\n";
                }
                $authLineCount++;
            }
            
            if ($authLineCount >= 20) {
                break;
            }
        }
        
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
        echo "   📋 User ID: " . ($latestSession->user_id ?: 'NULL') . "\n";
        echo "   📋 IP: " . $latestSession->ip_address . "\n";
        echo "   📋 Last Activity: " . date('Y-m-d H:i:s', $latestSession->last_activity) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

echo "🎯 DIAGNOSTIC :\n";
echo "===============\n";
echo "📋 Si Auth::attempt() réussit mais @auth ne fonctionne pas :\n";
echo "   → Problème de session non persistante\n";
echo "   → Middleware de session non appliqué\n";
echo "   → Configuration de session incorrecte\n";
echo "\n📋 SOLUTIONS :\n";
echo "   1. Tester avec un navigateur réel\n";
echo "   2. Vérifier que StartSession est appliqué\n";
echo "   3. Vérifier que la session est démarrée avant l'authentification\n";
echo "   4. Vérifier la configuration de session\n";
?>
