<?php

/**
 * Script pour extraire le dernier lien de vérification du log
 * Utilisation: php get-verification-link.php
 */

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "❌ Fichier log introuvable: {$logFile}\n";
    exit(1);
}

echo "=== Extraction des Liens de Vérification ===\n\n";

// Lire le fichier log
$logContent = file_get_contents($logFile);

// Chercher tous les liens de vérification d'email
preg_match_all('#(http[s]?://[^\s]+/email/verify/\d+/[^\s]+)#', $logContent, $verifyMatches);

// Chercher tous les liens de réinitialisation de mot de passe
preg_match_all('#(http[s]?://[^\s]+/reset-password/[^\s]+)#', $logContent, $resetMatches);

// Chercher tous les codes d'authentification (8 chiffres)
preg_match_all('#Code d\'authentification[^\d]*(\d{8})#', $logContent, $codeMatches);

echo "📧 LIENS DE VÉRIFICATION D'EMAIL:\n";
echo str_repeat("-", 70) . "\n";

if (!empty($verifyMatches[1])) {
    $verifyLinks = array_unique($verifyMatches[1]);
    $verifyLinks = array_reverse($verifyLinks); // Les plus récents en premier
    
    foreach ($verifyLinks as $index => $link) {
        // Nettoyer le lien (enlever les caractères parasites)
        $link = trim($link, '.,;"\' ');
        echo ($index + 1) . ". " . $link . "\n";
    }
    
    echo "\n💡 Dernier lien (le plus récent):\n";
    echo $verifyLinks[0] . "\n";
} else {
    echo "Aucun lien de vérification trouvé.\n";
}

echo "\n";
echo "🔑 LIENS DE RÉINITIALISATION DE MOT DE PASSE:\n";
echo str_repeat("-", 70) . "\n";

if (!empty($resetMatches[1])) {
    $resetLinks = array_unique($resetMatches[1]);
    $resetLinks = array_reverse($resetLinks);
    
    foreach ($resetLinks as $index => $link) {
        $link = trim($link, '.,;"\' ');
        echo ($index + 1) . ". " . $link . "\n";
    }
    
    echo "\n💡 Dernier lien:\n";
    echo $resetLinks[0] . "\n";
} else {
    echo "Aucun lien de réinitialisation trouvé.\n";
}

echo "\n";
echo "🔢 CODES D'AUTHENTIFICATION (8 chiffres):\n";
echo str_repeat("-", 70) . "\n";

if (!empty($codeMatches[1])) {
    $codes = array_reverse($codeMatches[1]);
    
    foreach ($codes as $index => $code) {
        echo ($index + 1) . ". " . $code . "\n";
    }
    
    echo "\n💡 Dernier code:\n";
    echo $codes[0] . "\n";
} else {
    echo "Aucun code d'authentification trouvé.\n";
}

echo "\n";
echo "=== Instructions ===\n";
echo "1. Copiez le lien ou le code ci-dessus\n";
echo "2. Pour les liens: Collez dans votre navigateur\n";
echo "3. Pour les codes: Entrez-le sur la page de vérification\n";
echo "\n";
echo "💡 Astuce: Ce script affiche toujours les plus récents en premier\n";
echo "\n";

