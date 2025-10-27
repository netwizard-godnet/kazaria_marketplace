<?php
/**
 * Test simple d'authentification
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST SIMPLE D'AUTHENTIFICATION ===\n\n";

// Test 1: Vérifier l'état d'authentification
echo "🔍 TEST 1: État d'authentification\n";
echo "==================================\n";

$authCheck = \Illuminate\Support\Facades\Auth::check();
echo "📋 Auth::check(): " . ($authCheck ? 'true' : 'false') . "\n";

if ($authCheck) {
    $user = \Illuminate\Support\Facades\Auth::user();
    echo "📋 Utilisateur connecté: " . $user->nom . " " . $user->prenoms . "\n";
    echo "📋 ID utilisateur: " . $user->id . "\n";
    echo "📋 Est vendeur: " . ($user->is_seller ? 'Oui' : 'Non') . "\n";
} else {
    echo "📋 Aucun utilisateur connecté\n";
}

echo "\n";

// Test 2: Connecter un utilisateur vendeur
echo "🔍 TEST 2: Connexion d'un utilisateur vendeur\n";
echo "============================================\n";

try {
    $seller = \App\Models\User::where('is_seller', true)->first();
    if ($seller) {
        \Illuminate\Support\Facades\Auth::login($seller);
        echo "📋 Utilisateur connecté: {$seller->nom} {$seller->prenoms}\n";
        
        // Vérifier la connexion
        $authCheck = \Illuminate\Support\Facades\Auth::check();
        echo "📋 Auth::check() après connexion: " . ($authCheck ? 'true' : 'false') . "\n";
        
        // Vérifier sa boutique
        $store = $seller->store;
        if ($store) {
            echo "📋 Boutique: {$store->name} (ID: {$store->id})\n";
        } else {
            echo "❌ Aucune boutique trouvée\n";
        }
        
    } else {
        echo "❌ Aucun vendeur trouvé\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Tester l'API des commandes
echo "🔍 TEST 3: Test API des commandes\n";
echo "=================================\n";

try {
    $request = \Illuminate\Http\Request::create('/store/api/orders', 'GET');
    $controller = new \App\Http\Controllers\Seller\OrderController();
    $response = $controller->getOrders($request);
    
    $data = json_decode($response->getContent(), true);
    echo "📋 Réponse API: " . ($data['success'] ? 'Succès' : 'Échec') . "\n";
    echo "📋 Message: " . ($data['message'] ?? 'Aucun message') . "\n";
    echo "📋 Nombre de commandes: " . count($data['orders'] ?? []) . "\n";
    
    if (!empty($data['orders'])) {
        echo "📋 Première commande: " . $data['orders'][0]['order_number'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur API: " . $e->getMessage() . "\n";
}

echo "\n";

\Illuminate\Support\Facades\Auth::logout();
echo "Déconnexion de l'utilisateur.\n";

echo "\n🎯 RÉSULTAT DU TEST :\n";
echo "=====================\n";
echo "✅ État d'authentification vérifié\n";
echo "✅ Connexion utilisateur testée\n";
echo "✅ API des commandes testée\n";
?>
