<?php
/**
 * Test de l'API avec code de connexion
 */

echo "🔐 Test de l'API avec code de connexion\n";
echo "======================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8000';

// 1. Se connecter pour obtenir un code
echo "1. 🔑 Connexion pour obtenir un code\n";
echo "-----------------------------------\n";

$loginData = [
    'email' => 'vendeur@test.com',
    'password' => 'password'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
$loginResult = json_decode($response, true);
echo "Réponse: " . json_encode($loginResult, JSON_PRETTY_PRINT) . "\n\n";

if (!$loginResult || !$loginResult['success']) {
    echo "❌ Échec de l'envoi du code\n";
    exit(1);
}

echo "✅ Code envoyé à {$loginResult['email']}\n\n";

// 2. Récupérer le code depuis la base de données
echo "2. 📧 Récupération du code depuis la base de données\n";
echo "--------------------------------------------------\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$authCode = \App\Models\AuthCode::where('email', 'vendeur@test.com')
    ->where('type', 'login')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$authCode) {
    echo "❌ Code non trouvé dans la base de données\n";
    exit(1);
}

$code = $authCode->code;
echo "✅ Code trouvé: $code\n\n";

// 3. Vérifier le code pour obtenir le token
echo "3. 🔓 Vérification du code\n";
echo "-------------------------\n";

$verifyData = [
    'email' => 'vendeur@test.com',
    'code' => $code
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/verify-login-code');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verifyData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
$verifyResult = json_decode($response, true);
echo "Réponse: " . json_encode($verifyResult, JSON_PRETTY_PRINT) . "\n\n";

if (!$verifyResult || !isset($verifyResult['token'])) {
    echo "❌ Échec de la vérification du code\n";
    exit(1);
}

$token = $verifyResult['token'];
echo "✅ Token obtenu: " . substr($token, 0, 20) . "...\n\n";

// 4. Tester l'API des statistiques
echo "4. 📊 Test des statistiques des commandes\n";
echo "----------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/store/orders/stats');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
$statsResult = json_decode($response, true);
echo "Réponse: " . json_encode($statsResult, JSON_PRETTY_PRINT) . "\n\n";

if ($httpCode === 200 && $statsResult['success']) {
    echo "✅ Statistiques récupérées avec succès\n";
    echo "Total des commandes: " . $statsResult['stats']['total_orders'] . "\n";
} else {
    echo "❌ Erreur lors de la récupération des statistiques\n";
}

// 5. Tester l'API des commandes
echo "5. 📋 Test de la liste des commandes\n";
echo "-----------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/store/orders');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
$ordersResult = json_decode($response, true);
echo "Réponse: " . json_encode($ordersResult, JSON_PRETTY_PRINT) . "\n\n";

if ($httpCode === 200 && $ordersResult['success']) {
    echo "✅ Commandes récupérées avec succès\n";
    echo "Nombre de commandes: " . count($ordersResult['orders']) . "\n";
    
    if (count($ordersResult['orders']) > 0) {
        echo "Première commande: " . $ordersResult['orders'][0]['order_number'] . "\n";
    }
} else {
    echo "❌ Erreur lors de la récupération des commandes\n";
}

echo "6. 📝 Résumé\n";
echo "------------\n";
echo "• Code: " . (isset($code) ? "✅ Obtenu" : "❌ Échec") . "\n";
echo "• Token: " . (isset($token) ? "✅ Obtenu" : "❌ Échec") . "\n";
echo "• Statistiques: " . ($httpCode === 200 ? "✅ OK" : "❌ Erreur") . "\n";
echo "• Commandes: " . ($httpCode === 200 ? "✅ OK" : "❌ Erreur") . "\n";

?>
