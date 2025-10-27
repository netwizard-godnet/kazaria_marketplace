<?php
/**
 * Vérifier si l'utilisateur existe
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VÉRIFICATION UTILISATEUR ===\n\n";

// Test 1: Vérifier tous les utilisateurs
echo "🔍 TEST 1: Liste des utilisateurs\n";
echo "==================================\n";

try {
    $users = \App\Models\User::all();
    echo "📋 Nombre d'utilisateurs: " . $users->count() . "\n\n";
    
    foreach ($users as $user) {
        echo "📋 Utilisateur:\n";
        echo "   📋 ID: " . $user->id . "\n";
        echo "   📋 Nom: " . $user->nom . "\n";
        echo "   📋 Prénoms: " . $user->prenoms . "\n";
        echo "   📋 Email: " . $user->email . "\n";
        echo "   📋 Vendeur: " . ($user->is_seller ? "Oui" : "Non") . "\n";
        echo "   📋 Email vérifié: " . ($user->email_verified_at ? "Oui" : "Non") . "\n";
        echo "   📋 Créé: " . $user->created_at . "\n";
        echo "   ---\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Tester l'authentification avec différents utilisateurs
echo "🔍 TEST 2: Test d'authentification\n";
echo "===================================\n";

try {
    $users = \App\Models\User::all();
    
    foreach ($users as $user) {
        echo "📋 Test avec: " . $user->email . "\n";
        
        // Tester avec le mot de passe par défaut
        $credentials = [
            'email' => $user->email,
            'password' => 'password123'
        ];
        
        $authenticated = \Illuminate\Support\Facades\Auth::attempt($credentials);
        echo "   📋 Authentification: " . ($authenticated ? "✅ Succès" : "❌ Échec") . "\n";
        
        if ($authenticated) {
            \Illuminate\Support\Facades\Auth::logout();
        }
        
        echo "   ---\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Vérifier la configuration de hash
echo "🔍 TEST 3: Configuration de hash\n";
echo "=================================\n";

try {
    $hashDriver = config('hashing.default');
    echo "📋 Driver de hash: " . $hashDriver . "\n";
    
    // Tester le hash d'un mot de passe
    $password = 'password123';
    $hashed = \Illuminate\Support\Facades\Hash::make($password);
    echo "📋 Hash de 'password123': " . substr($hashed, 0, 50) . "...\n";
    
    // Vérifier si le hash correspond
    $check = \Illuminate\Support\Facades\Hash::check($password, $hashed);
    echo "📋 Vérification du hash: " . ($check ? "✅ Valide" : "❌ Invalide") . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

echo "🎯 RECOMMANDATIONS :\n";
echo "====================\n";
echo "1. Vérifier que l'utilisateur existe\n";
echo "2. Vérifier que le mot de passe est correct\n";
echo "3. Tester avec un navigateur réel\n";
echo "4. Vérifier la configuration de hash\n";
?>
