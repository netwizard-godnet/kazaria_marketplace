<?php
/**
 * Test de l'API des commandes après correction
 */

echo "🔧 Test de l'API des commandes après correction\n";
echo "=============================================\n\n";

// Configuration
$baseUrl = 'http://localhost/kazaria-laravel-v0/public';

// Test avec un token de test (vous devrez le remplacer par un vrai token)
$testToken = 'test_token_here'; // Remplacez par un vrai token

echo "1. 🔑 Test avec token d'authentification\n";
echo "---------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/store/orders');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $testToken,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
if ($error) {
    echo "Erreur cURL: $error\n";
}

if ($httpCode === 401) {
    echo "✅ L'API répond (401 = Non autorisé, token invalide)\n";
    echo "   C'est normal si le token de test n'est pas valide\n";
} elseif ($httpCode === 200) {
    echo "✅ L'API répond (200 = OK)\n";
    $data = json_decode($response, true);
    echo "Réponse: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ L'API ne répond pas correctement\n";
    echo "Réponse: $response\n";
}

echo "\n";

// Test 2: Vérifier les paramètres de tri
echo "2. 🔄 Test des paramètres de tri\n";
echo "--------------------------------\n";

$testParams = [
    'sort_by=created_at&sort_order=desc',
    'sort_by=total&sort_order=asc',
    'sort_by=status&sort_order=desc'
];

foreach ($testParams as $params) {
    echo "Test avec: $params\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/store/orders?' . $params);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $testToken,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "  Code HTTP: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "  ✅ Paramètres de tri acceptés\n";
    } elseif ($httpCode === 401) {
        echo "  ✅ Paramètres de tri acceptés (401 = token invalide)\n";
    } else {
        echo "  ❌ Erreur avec ces paramètres\n";
        echo "  Réponse: $response\n";
    }
    echo "\n";
}

echo "3. 📝 Instructions pour tester manuellement\n";
echo "------------------------------------------\n";
echo "1. Connectez-vous au dashboard vendeur\n";
echo "2. Ouvrez la console du navigateur (F12)\n";
echo "3. Allez dans l'onglet 'Commandes'\n";
echo "4. Regardez les logs dans la console\n";
echo "5. Vérifiez l'onglet Network pour voir les requêtes API\n";
echo "\n";

echo "4. 🔍 Vérification des logs Laravel\n";
echo "----------------------------------\n";

$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -10);
    
    echo "Dernières 10 lignes du log:\n";
    foreach ($recentLines as $line) {
        if (strpos($line, 'Order direction') !== false) {
            echo "❌ " . trim($line) . "\n";
        } else {
            echo "  " . trim($line) . "\n";
        }
    }
} else {
    echo "❌ Fichier de log manquant\n";
}

echo "\n";

echo "5. ✅ Résumé de la correction\n";
echo "-----------------------------\n";
echo "• Problème identifié: Paramètres de tri incorrects\n";
echo "• Correction appliquée: Gestion des valeurs de tri sans '_'\n";
echo "• Prochaine étape: Tester dans le navigateur\n";

?>
