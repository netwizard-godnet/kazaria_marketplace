<?php
/**
 * Test de l'API avec un token valide
 */

echo "🔐 Test de l'API avec authentification\n";
echo "=====================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8000';

// 1. Se connecter pour obtenir un token
echo "1. 🔑 Connexion pour obtenir un token\n";
echo "------------------------------------\n";

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

if (!$loginResult || !isset($loginResult['token'])) {
    echo "❌ Échec de l'authentification\n";
    exit(1);
}

$token = $loginResult['token'];
echo "✅ Token obtenu: " . substr($token, 0, 20) . "...\n\n";

// 2. Tester l'API des statistiques
echo "2. 📊 Test des statistiques des commandes\n";
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

// 3. Tester l'API des commandes
echo "3. 📋 Test de la liste des commandes\n";
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

// 4. Tester avec des paramètres de tri
echo "4. 🔄 Test avec paramètres de tri\n";
echo "--------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/store/orders?sort_by=created_at&sort_order=desc');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
$sortedResult = json_decode($response, true);
echo "Réponse: " . json_encode($sortedResult, JSON_PRETTY_PRINT) . "\n\n";

if ($httpCode === 200) {
    echo "✅ Paramètres de tri acceptés\n";
} else {
    echo "❌ Erreur avec les paramètres de tri\n";
}

echo "5. 📝 Résumé\n";
echo "------------\n";
echo "• Token: " . (isset($token) ? "✅ Obtenu" : "❌ Échec") . "\n";
echo "• Statistiques: " . ($httpCode === 200 ? "✅ OK" : "❌ Erreur") . "\n";
echo "• Commandes: " . ($httpCode === 200 ? "✅ OK" : "❌ Erreur") . "\n";
echo "• Tri: " . ($httpCode === 200 ? "✅ OK" : "❌ Erreur") . "\n";

?>
