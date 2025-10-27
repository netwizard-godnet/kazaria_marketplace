<?php
/**
 * Afficher les erreurs CSRF récentes
 */

require_once 'vendor/autoload.php';

echo "🔍 ERREURS CSRF RÉCENTES\n";
echo "========================\n\n";

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "❌ Aucun fichier de log trouvé\n";
    exit;
}

// Lire les dernières erreurs CSRF
$logContent = file_get_contents($logFile);
$lines = explode("\n", $logContent);

echo "Recherche des erreurs CSRF...\n\n";

$csrfErrors = [];
foreach ($lines as $i => $line) {
    if (strpos($line, 'CSRF Token Mismatch') !== false) {
        // Extraire les informations de l'erreur
        $errorInfo = [
            'line' => $line,
            'context' => []
        ];
        
        // Chercher le contexte (5 lignes avant et après)
        for ($j = max(0, $i - 5); $j <= min(count($lines) - 1, $i + 5); $j++) {
            if ($j !== $i) {
                $errorInfo['context'][] = $lines[$j];
            }
        }
        
        $csrfErrors[] = $errorInfo;
    }
}

if (empty($csrfErrors)) {
    echo "✅ Aucune erreur CSRF récente trouvée\n";
} else {
    echo "❌ " . count($csrfErrors) . " erreur(s) CSRF trouvée(s):\n\n";
    
    foreach (array_slice($csrfErrors, -10) as $i => $error) { // Afficher les 10 dernières
        echo str_repeat('=', 70) . "\n";
        echo "Erreur #" . ($i + 1) . "\n";
        echo str_repeat('=', 70) . "\n";
        echo $error['line'] . "\n\n";
        
        if (!empty($error['context'])) {
            echo "Contexte:\n";
            foreach (array_slice($error['context'], -5, 5) as $contextLine) {
                echo "  $contextLine\n";
            }
        }
        echo "\n";
    }
}

echo "\n💡 CONSEIL\n";
echo "==========\n";
echo "Si des erreurs CSRF apparaissent, vérifiez:\n";
echo "1. Que le token CSRF est bien envoyé dans les headers\n";
echo "2. Que la session n'expire pas entre les requêtes\n";
echo "3. Que SESSION_SAME_SITE est configuré correctement\n";
?>
