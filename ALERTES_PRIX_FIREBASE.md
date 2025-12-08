# 🔔 Système d'Alertes de Prix avec Firebase

## Vue d'ensemble

Ce système permet d'envoyer automatiquement des notifications push à tous les utilisateurs de l'application mobile lorsqu'un produit voit son prix réduit.

## Architecture

### 1. **Enregistrement des Tokens FCM**

Les tokens Firebase Cloud Messaging (FCM) sont enregistrés automatiquement lorsque :
- Un utilisateur installe l'application
- Un utilisateur accepte les notifications
- L'utilisateur se connecte

**Endpoint API :** `POST /api/notifications/register-token`

**Données requises :**
```json
{
  "token": "fcm_token_here",
  "platform": "android" | "ios",
  "device_name": "Nom de l'appareil",
  "device_model": "Modèle de l'appareil"
}
```

### 2. **Détection Automatique des Changements de Prix**

Un **Observer** (`ProductObserver`) surveille automatiquement les modifications de prix sur les produits :

- **Déclenchement :** Lorsqu'un produit est mis à jour et que son prix baisse
- **Condition :** `nouveau_prix < ancien_prix` ET `produit actif`
- **Action :** Envoi automatique de notifications à tous les utilisateurs

### 3. **Envoi des Notifications**

Le service `FirebaseNotificationService` gère l'envoi :

- **À tous les utilisateurs** : Notification générale de réduction de prix
- **Aux abonnés** : Utilisateurs ayant créé une alerte de prix spécifique pour ce produit

## Configuration Requise

### 1. Variables d'environnement

Ajoutez dans votre fichier `.env` :

```env
FCM_SERVER_KEY=votre_cle_serveur_firebase
```

**Comment obtenir la clé serveur Firebase :**
1. Allez sur [Firebase Console](https://console.firebase.google.com/)
2. Sélectionnez votre projet
3. Allez dans **Paramètres du projet** > **Cloud Messaging**
4. Copiez la **Clé serveur** (Server Key)

### 2. Migration de la base de données

Exécutez la migration pour créer la table `fcm_tokens` :

```bash
php artisan migrate
```

## Utilisation

### Envoi Automatique (Recommandé)

Le système fonctionne automatiquement ! Dès qu'un produit voit son prix réduit :

1. L'observer détecte le changement
2. Une notification est envoyée à tous les utilisateurs
3. Les utilisateurs reçoivent : "💰 Prix réduit ! [Nom du produit] : [Nouveau prix] FCFA (-[%])"

### Envoi Manuel depuis le Backend

Vous pouvez aussi envoyer des notifications manuellement :

```php
use App\Services\FirebaseNotificationService;

$notificationService = new FirebaseNotificationService();

// Envoyer à tous les utilisateurs
$notificationService->sendToAll(
    'Titre de la notification',
    'Message de la notification',
    ['type' => 'promotion', 'product_id' => 123]
);

// Envoyer à un utilisateur spécifique
$notificationService->sendToUser(
    $userId,
    'Titre',
    'Message',
    ['type' => 'order', 'order_id' => 456]
);

// Envoyer une alerte de prix
$notificationService->sendPriceAlert(
    $productId,
    $productName,
    $oldPrice,
    $newPrice
);
```

### Créer une Commande Artisan pour les Alertes de Prix

Créez une commande pour envoyer des alertes en masse :

```bash
php artisan make:command SendPriceAlerts
```

Puis dans la commande :

```php
use App\Services\FirebaseNotificationService;
use App\Models\Product;

public function handle()
{
    $service = new FirebaseNotificationService();
    
    // Récupérer tous les produits avec réduction
    $products = Product::whereColumn('price', '<', 'old_price')
        ->where('is_active', true)
        ->get();
    
    foreach ($products as $product) {
        $service->sendPriceAlert(
            $product->id,
            $product->name,
            $product->old_price,
            $product->price
        );
    }
}
```

## Structure des Notifications

### Format de la notification

```json
{
  "notification": {
    "title": "💰 Prix réduit !",
    "body": "iPhone 15 Pro Max : 450 000 FCFA (-18%)",
    "sound": "default",
    "badge": 1
  },
  "data": {
    "type": "price_alert",
    "product_id": 5,
    "old_price": 550000,
    "new_price": 450000,
    "discount": 100000,
    "discount_percent": 18,
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  }
}
```

### Navigation dans l'application

Quand l'utilisateur clique sur la notification, l'app ouvre automatiquement la page du produit grâce au `product_id` dans les données.

## Gestion des Tokens

### Tokens Invalides

Le système désactive automatiquement les tokens invalides (appareil désinstallé, token expiré) pour optimiser les performances.

### Statistiques

Endpoint pour voir les statistiques (admin uniquement) :
```
GET /api/notifications/stats
```

Réponse :
```json
{
  "success": true,
  "data": {
    "total": 1500,
    "active": 1200,
    "android": 800,
    "ios": 400
  }
}
```

## Exemples d'Utilisation

### 1. Notification de Nouveau Produit

```php
$notificationService->sendToAll(
    '🆕 Nouveau produit disponible !',
    'Découvrez notre nouvelle collection',
    ['type' => 'new_product', 'product_id' => 123]
);
```

### 2. Notification de Commande

```php
$notificationService->sendToUser(
    $userId,
    '📦 Commande confirmée',
    'Votre commande #KAZ-20251204-123456 a été confirmée',
    ['type' => 'order', 'order_id' => 456]
);
```

### 3. Notification de Promotion Flash

```php
$notificationService->sendToAll(
    '⚡ Vente Flash !',
    'Jusqu\'à -70% sur tous les produits électroniques',
    ['type' => 'flash_sale', 'category_id' => 5]
);
```

## Tests

Pour tester l'envoi de notifications :

1. **Créer un produit de test**
2. **Modifier son prix** (le réduire)
3. **Vérifier les logs** : `storage/logs/laravel.log`
4. **Vérifier sur l'appareil** : La notification devrait apparaître

## Limitations

- **Firebase limite** : Maximum 1000 tokens par requête (géré automatiquement)
- **Rate limiting** : Firebase limite le nombre de requêtes par minute
- **Tokens inactifs** : Les tokens sont automatiquement désactivés s'ils sont invalides

## Support

Pour toute question ou problème :
- Vérifiez les logs : `storage/logs/laravel.log`
- Vérifiez que `FCM_SERVER_KEY` est bien configurée
- Vérifiez que les tokens sont bien enregistrés dans la table `fcm_tokens`
