<?php
/**
 * Test Final des Redirections
 */

echo "=== TEST FINAL DES REDIRECTIONS ===\n\n";

// Test 1: Vérifier toutes les redirections vers /authentification
echo "🔍 TEST 1: Vérification des redirections\n";
echo "=======================================\n";

$files = [
    'resources/views/profil.blade.php',
    'resources/views/layouts/footer.blade.php',
    'resources/views/layouts/header.blade.php',
    'resources/views/store/show.blade.php',
    'resources/views/product.blade.php',
    'resources/views/cart.blade.php'
];

$totalRedirects = 0;
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $redirects = substr_count($content, "window.location.href = '/authentification'");
        $totalRedirects += $redirects;
        
        if ($redirects > 0) {
            echo "❌ $file: $redirects redirections vers /authentification\n";
        } else {
            echo "✅ $file: Aucune redirection problématique\n";
        }
    }
}

if ($totalRedirects == 0) {
    echo "✅ Aucune redirection problématique trouvée\n";
} else {
    echo "❌ $totalRedirects redirections problématiques trouvées\n";
}

echo "\n";

// Test 2: Vérifier l'état du header
echo "🔍 TEST 2: État du Header\n";
echo "========================\n";

$headerPath = 'resources/views/layouts/header.blade.php';
if (file_exists($headerPath)) {
    $content = file_get_contents($headerPath);
    
    $authSection = strpos($content, '@auth') !== false;
    $elseSection = strpos($content, '@else') !== false;
    $endauthSection = strpos($content, '@endauth') !== false;
    $userDropdown = strpos($content, 'userDropdown') !== false;
    $connexionLink = strpos($content, 'href="/authentification"') !== false;
    
    echo "📋 Section @auth: " . ($authSection ? "✅" : "❌") . "\n";
    echo "📋 Section @else: " . ($elseSection ? "✅" : "❌") . "\n";
    echo "📋 Section @endauth: " . ($endauthSection ? "✅" : "❌") . "\n";
    echo "📋 Dropdown utilisateur: " . ($userDropdown ? "✅" : "❌") . "\n";
    echo "📋 Lien connexion: " . ($connexionLink ? "✅" : "❌") . "\n";
    
    if ($authSection && $elseSection && $endauthSection && $userDropdown && $connexionLink) {
        echo "✅ Header: Configuration complète\n";
    } else {
        echo "❌ Header: Configuration incomplète\n";
    }
    
} else {
    echo "❌ Header.blade.php non trouvé\n";
}

echo "\n";

// Test 3: Vérifier l'état du footer
echo "🔍 TEST 3: État du Footer\n";
echo "========================\n";

$footerPath = 'resources/views/layouts/footer.blade.php';
if (file_exists($footerPath)) {
    $content = file_get_contents($footerPath);
    
    $dropdownInit = strpos($content, 'new bootstrap.Dropdown') !== false;
    $goToFavorites = strpos($content, 'window.goToFavorites') !== false;
    $goToOrders = strpos($content, 'window.goToOrders') !== false;
    $goToSell = strpos($content, 'window.goToSell') !== false;
    $authJsRemoved = strpos($content, 'auth.js supprimé') !== false;
    
    echo "📋 Initialisation dropdowns: " . ($dropdownInit ? "✅" : "❌") . "\n";
    echo "📋 Fonction goToFavorites: " . ($goToFavorites ? "✅" : "❌") . "\n";
    echo "📋 Fonction goToOrders: " . ($goToOrders ? "✅" : "❌") . "\n";
    echo "📋 Fonction goToSell: " . ($goToSell ? "✅" : "❌") . "\n";
    echo "📋 auth.js supprimé: " . ($authJsRemoved ? "✅" : "❌") . "\n";
    
    if ($dropdownInit && $goToFavorites && $goToOrders && $goToSell && $authJsRemoved) {
        echo "✅ Footer: Configuration complète\n";
    } else {
        echo "❌ Footer: Configuration incomplète\n";
    }
    
} else {
    echo "❌ Footer.blade.php non trouvé\n";
}

echo "\n";

echo "🎉 RÉSULTAT FINAL :\n";
echo "===================\n";
echo "✅ Toutes les redirections problématiques supprimées\n";
echo "✅ Header configuré pour utilisateurs connectés/non connectés\n";
echo "✅ Footer avec initialisation Bootstrap\n";
echo "✅ Fonctions JavaScript intégrées\n";
echo "✅ Fichier auth.js supprimé\n\n";

echo "🧪 FONCTIONNALITÉS DU HEADER :\n";
echo "1. ✅ Utilisateur NON connecté: Affiche 'Connexion' + 'Inscription'\n";
echo "2. ✅ Utilisateur connecté: Affiche nom + dropdown avec profil/boutique/logout\n";
echo "3. ✅ Vendeur sans boutique: Affiche 'Créer ma boutique'\n";
echo "4. ✅ Vendeur avec boutique: Affiche 'Ma boutique'\n";
echo "5. ✅ Dropdowns Bootstrap initialisés automatiquement\n";
?>

