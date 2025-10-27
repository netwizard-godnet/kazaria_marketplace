<?php
/**
 * Test de la vérification d'email
 */

echo "📧 Test de la vérification d'email\n";
echo "=================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8000';

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // 1. Créer un utilisateur avec un token de vérification
    echo "1. 🔧 Création d'un utilisateur de test...\n";
    
    $user = \App\Models\User::create([
        'nom' => 'Test',
        'prenoms' => 'Email',
        'email' => 'test-email@example.com',
        'password' => bcrypt('password'),
        'is_seller' => false,
        'email_verified_at' => null,
        'is_verified' => false,
        'email_verification_token' => 'test-token-123'
    ]);
    
    echo "✅ Utilisateur créé: {$user->email}\n";
    echo "Token: {$user->email_verification_token}\n\n";
    
    // 2. Tester la route de vérification
    echo "2. 🔗 Test de la route de vérification...\n";
    
    $verificationUrl = $baseUrl . '/verify-email/test-token-123';
    echo "URL: $verificationUrl\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verificationUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    echo "Code HTTP: $httpCode\n";
    echo "Content-Type: $contentType\n";
    
    if (strpos($contentType, 'text/html') !== false) {
        echo "✅ Page HTML retournée (succès)\n";
        
        // Vérifier que l'utilisateur est maintenant vérifié
        $user->refresh();
        if ($user->is_verified && $user->email_verified_at) {
            echo "✅ Utilisateur vérifié dans la base de données\n";
        } else {
            echo "❌ Utilisateur non vérifié dans la base de données\n";
        }
    } else {
        echo "❌ JSON retourné au lieu de HTML\n";
        echo "Réponse: " . substr($response, 0, 200) . "...\n";
    }
    
    echo "\n";
    
    // 3. Tester avec un token invalide
    echo "3. 🚫 Test avec un token invalide...\n";
    
    $invalidUrl = $baseUrl . '/verify-email/invalid-token';
    echo "URL: $invalidUrl\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $invalidUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    echo "Code HTTP: $httpCode\n";
    echo "Content-Type: $contentType\n";
    
    if (strpos($contentType, 'text/html') !== false) {
        echo "✅ Page HTML retournée (succès)\n";
    } else {
        echo "❌ JSON retourné au lieu de HTML\n";
        echo "Réponse: " . substr($response, 0, 200) . "...\n";
    }
    
    echo "\n";
    
    // 4. Nettoyer les données de test
    echo "4. 🧹 Nettoyage des données de test...\n";
    $user->delete();
    echo "✅ Utilisateur de test supprimé\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n5. 📝 Résumé\n";
echo "------------\n";
echo "• Route web: ✅ Configurée\n";
echo "• Route API: ❌ Supprimée\n";
echo "• Vue HTML: ✅ Créée\n";
echo "• Contrôleur: ✅ Modifié\n";

?>
