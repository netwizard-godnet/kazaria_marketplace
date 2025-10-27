<?php
/**
 * Script pour vérifier les commandes dans la base de données
 * 
 * Ce script vérifie s'il y a des commandes et leur relation avec les boutiques.
 */

require_once 'vendor/autoload.php';

// Configuration de la base de données (ajustez selon votre configuration)
$host = 'localhost';
$dbname = 'kazaria'; // Remplacez par votre nom de base de données
$username = 'root'; // Remplacez par votre nom d'utilisateur
$password = ''; // Remplacez par votre mot de passe

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Vérification des commandes dans la base de données\n";
    echo "==================================================\n\n";
    
    // 1. Vérifier le nombre total de commandes
    echo "1. 📊 Nombre total de commandes\n";
    echo "-------------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total des commandes: $totalOrders\n\n";
    
    if ($totalOrders == 0) {
        echo "❌ Aucune commande dans la base de données\n";
        echo "   Pour tester, créez d'abord des commandes via l'interface client\n";
        exit(0);
    }
    
    // 2. Vérifier les boutiques
    echo "2. 🏪 Boutiques disponibles\n";
    echo "--------------------------\n";
    
    $stmt = $pdo->query("SELECT id, name, user_id FROM stores");
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stores as $store) {
        echo "ID: {$store['id']}, Nom: {$store['name']}, User ID: {$store['user_id']}\n";
    }
    echo "\n";
    
    // 3. Vérifier les produits et leurs boutiques
    echo "3. 📦 Produits par boutique\n";
    echo "----------------------------\n";
    
    $stmt = $pdo->query("
        SELECT s.id as store_id, s.name as store_name, COUNT(p.id) as product_count
        FROM stores s
        LEFT JOIN products p ON s.id = p.store_id
        GROUP BY s.id, s.name
    ");
    $storeProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($storeProducts as $store) {
        echo "Boutique {$store['store_id']} ({$store['store_name']}): {$store['product_count']} produits\n";
    }
    echo "\n";
    
    // 4. Vérifier les commandes avec leurs articles
    echo "4. 🛒 Commandes et articles\n";
    echo "---------------------------\n";
    
    $stmt = $pdo->query("
        SELECT 
            o.id as order_id,
            o.order_number,
            o.status,
            o.created_at,
            oi.product_id,
            p.name as product_name,
            p.store_id,
            s.name as store_name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        JOIN stores s ON p.store_id = s.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orderItems)) {
        echo "❌ Aucun article de commande trouvé\n";
        echo "   Vérifiez que les commandes ont des articles associés\n";
    } else {
        echo "Dernières commandes avec articles:\n";
        foreach ($orderItems as $item) {
            echo "Commande {$item['order_number']} - {$item['product_name']} (Boutique: {$item['store_name']})\n";
        }
    }
    echo "\n";
    
    // 5. Vérifier les commandes par boutique
    echo "5. 🏪 Commandes par boutique\n";
    echo "----------------------------\n";
    
    foreach ($stores as $store) {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT o.id) as order_count
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            WHERE p.store_id = ?
        ");
        $stmt->execute([$store['id']]);
        $orderCount = $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];
        
        echo "Boutique {$store['name']} (ID: {$store['id']}): $orderCount commandes\n";
    }
    echo "\n";
    
    // 6. Vérifier les relations manquantes
    echo "6. 🔍 Vérification des relations\n";
    echo "--------------------------------\n";
    
    // Commandes sans articles
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE oi.id IS NULL
    ");
    $ordersWithoutItems = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($ordersWithoutItems > 0) {
        echo "⚠️  $ordersWithoutItems commande(s) sans articles\n";
    } else {
        echo "✅ Toutes les commandes ont des articles\n";
    }
    
    // Articles sans produits
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE p.id IS NULL
    ");
    $itemsWithoutProducts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($itemsWithoutProducts > 0) {
        echo "⚠️  $itemsWithoutProducts article(s) de commande sans produit\n";
    } else {
        echo "✅ Tous les articles de commande ont des produits\n";
    }
    
    // Produits sans boutique
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM products p
        LEFT JOIN stores s ON p.store_id = s.id
        WHERE s.id IS NULL
    ");
    $productsWithoutStore = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($productsWithoutStore > 0) {
        echo "⚠️  $productsWithoutStore produit(s) sans boutique\n";
    } else {
        echo "✅ Tous les produits ont une boutique\n";
    }
    
    echo "\n7. 💡 Recommandations\n";
    echo "--------------------\n";
    
    if ($totalOrders == 0) {
        echo "• Créez des commandes de test via l'interface client\n";
        echo "• Vérifiez que le processus de commande fonctionne\n";
    } elseif (empty($orderItems)) {
        echo "• Vérifiez que les commandes ont des articles\n";
        echo "• Vérifiez la table order_items\n";
    } else {
        echo "• Les données semblent correctes\n";
        echo "• Le problème pourrait être dans l'API ou l'authentification\n";
        echo "• Vérifiez les logs de l'application\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
    echo "Vérifiez votre configuration de base de données.\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

?>
