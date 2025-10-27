<?php
/**
 * Audit simple du système d'authentification
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== AUDIT SIMPLE DU SYSTÈME D'AUTHENTIFICATION ===\n\n";

// Test 1: État d'authentification
echo "🔍 TEST 1: État d'authentification\n";
echo "==================================\n";

try {
    $authCheck = \Illuminate\Support\Facades\Auth::check();
    echo "📋 Auth::check(): " . ($authCheck ? 'true' : 'false') . "\n";
    
    if ($authCheck) {
        $user = \Illuminate\Support\Facades\Auth::user();
        echo "📋 Utilisateur connecté: " . $user->nom . " " . $user->prenoms . "\n";
    } else {
        echo "📋 Aucun utilisateur connecté\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Sessions
echo "🔍 TEST 2: Sessions\n";
echo "===================\n";

try {
    $sessionId = session()->getId();
    echo "📋 Session ID: " . ($sessionId ?: 'Aucune') . "\n";
    
    $connection = \Illuminate\Support\Facades\DB::connection();
    $sessions = $connection->select("SELECT * FROM sessions WHERE id = ?", [$sessionId]);
    
    if (!empty($sessions)) {
        $session = $sessions[0];
        echo "📋 Session en base: ✅ Trouvée\n";
        echo "📋 User ID: " . ($session->user_id ?? 'Aucun') . "\n";
    } else {
        echo "📋 Session en base: ❌ Non trouvée\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Configuration
echo "🔍 TEST 3: Configuration\n";
echo "========================\n";

try {
    $sessionConfig = config('session');
    echo "📋 Driver: " . ($sessionConfig['driver'] ?? 'Non défini') . "\n";
    echo "📋 Lifetime: " . ($sessionConfig['lifetime'] ?? 'Non défini') . " minutes\n";
    echo "📋 Encrypt: " . ($sessionConfig['encrypt'] ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Simulation de connexion
echo "🔍 TEST 4: Simulation de connexion\n";
echo "==================================\n";

try {
    $user = \App\Models\User::where('email', 'wilsonmoise005@gmail.com')->first();
    
    if ($user) {
        echo "📋 Utilisateur trouvé: " . $user->nom . " " . $user->prenoms . "\n";
        
        \Illuminate\Support\Facades\Auth::login($user, true);
        session()->save();
        
        echo "📋 Connexion simulée: ✅\n";
        
        $authCheck = \Illuminate\Support\Facades\Auth::check();
        echo "📋 Auth::check(): " . ($authCheck ? 'true' : 'false') . "\n";
        
        $sessionId = session()->getId();
        echo "📋 Session ID: " . ($sessionId ?: 'Aucune') . "\n";
        
        $connection = \Illuminate\Support\Facades\DB::connection();
        $sessions = $connection->select("SELECT * FROM sessions WHERE id = ?", [$sessionId]);
        
        if (!empty($sessions)) {
            $session = $sessions[0];
            echo "📋 Session en base: ✅ Trouvée\n";
            echo "📋 User ID: " . ($session->user_id ?? 'Aucun') . "\n";
        } else {
            echo "📋 Session en base: ❌ Non trouvée\n";
        }
    } else {
        echo "📋 Utilisateur non trouvé\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Test de la directive @auth
echo "🔍 TEST 5: Test de la directive @auth\n";
echo "=====================================\n";

try {
    $viewContent = '
    @auth
        <div class="user-info">
            <p>Utilisateur connecté: {{ Auth::user()->nom }} {{ Auth::user()->prenoms }}</p>
        </div>
    @else
        <div class="guest-info">
            <p>Utilisateur non connecté</p>
        </div>
    @endauth
    ';
    
    $tempViewPath = 'resources/views/test-auth-simple.blade.php';
    file_put_contents($tempViewPath, $viewContent);
    
    $view = view('test-auth-simple');
    $renderedContent = $view->render();
    
    echo "📋 Vue rendue: ✅\n";
    echo "📋 Contenu: " . $renderedContent . "\n";
    
    if (strpos($renderedContent, 'Utilisateur connecté') !== false) {
        echo "📋 Directive @auth: ✅ Fonctionne\n";
    } else {
        echo "📋 Directive @auth: ❌ Ne fonctionne pas\n";
    }
    
    unlink($tempViewPath);
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

echo "🎯 AUDIT TERMINÉ !\n";
?>
