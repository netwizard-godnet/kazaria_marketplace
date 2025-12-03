# Système de Routage Flutter - KAZARIA Marketplace

## 📋 Vue d'ensemble

Ce système de routage centralisé permet de gérer toutes les navigations de l'application Flutter de manière cohérente et maintenable.

## 🚀 Structure

### Fichiers principaux

1. **`app_router.dart`** - Définit toutes les routes nommées et leurs configurations
2. **`navigation_service.dart`** - Service centralisé pour la navigation avec méthodes helper

## 📍 Routes disponibles

### Authentification
- `/` - Splash Screen
- `/welcome` - Écran d'accueil
- `/login` - Connexion
- `/register` - Inscription
- `/forgot-password` - Mot de passe oublié
- `/verify-code` - Vérification du code

### Navigation principale
- `/main` - Écran principal avec navigation bottom
- `/home` - Accueil
- `/categories` - Catégories
- `/category-products` - Produits d'une catégorie
- `/stores` - Boutiques
- `/cart` - Panier
- `/profile` - Profil

### Produits
- `/product-details` - Détails d'un produit
- `/products-list` - Liste de produits
- `/image-gallery` - Galerie d'images
- `/recent-products` - Produits récents
- `/best-offers` - Meilleures offres

### Boutiques
- `/store-details` - Détails d'une boutique

### Commandes et Paiement
- `/checkout` - Finalisation de commande
- `/my-orders` - Mes commandes
- `/order-details` - Détails d'une commande
- `/track-order` - Suivi de commande
- `/payment-method-selection` - Sélection méthode de paiement
- `/mobile-money-payment` - Paiement mobile money
- `/payment-history` - Historique de paiement

### Profil et Paramètres
- `/edit-profile` - Édition du profil
- `/change-password` - Changement de mot de passe
- `/addresses` - Adresses
- `/add-address` - Ajouter une adresse
- `/favorites` - Favoris
- `/payments` - Méthodes de paiement
- `/invoice-history` - Historique des factures
- `/notifications` - Notifications
- `/language` - Langue

### Vendeur
- `/seller/dashboard` - Tableau de bord vendeur
- `/seller/register` - Inscription vendeur
- `/seller/login` - Connexion vendeur
- `/seller/verify-code` - Vérification code vendeur
- `/seller/store-settings` - Paramètres boutique
- `/seller/products` - Produits vendeur
- `/seller/orders` - Commandes vendeur
- `/seller/add-product` - Ajouter un produit
- `/seller/edit-product` - Éditer un produit

### Wishlist
- `/wishlists` - Listes de souhaits
- `/wishlist-details` - Détails d'une wishlist
- `/wishlist-alerts` - Alertes wishlist
- `/wishlist-notification-preferences` - Préférences notifications
- `/wishlist-share-management` - Gestion du partage

### Comparaison
- `/product-comparison` - Comparaison de produits
- `/comparison-history` - Historique de comparaison

### Autres
- `/reviews` - Avis produits
- `/search` - Recherche
- `/ai-chat` - Chat IA
- `/help` - Aide
- `/contact` - Contact
- `/flash-sales` - Ventes flash
- `/black-friday` - Black Friday
- `/webview` - Affichage web

## 💡 Utilisation

### Navigation basique

```dart
import '../routes/navigation_service.dart';
import '../routes/app_router.dart';

// Navigation simple
NavigationService.pushNamed(AppRouter.home);

// Navigation avec arguments
NavigationService.toProductDetails(
  product: productModel,
  heroTag: 'product_${productModel.id}',
);

// Navigation avec remplacement (pas de retour)
NavigationService.pushReplacementNamed(AppRouter.main);

// Navigation et suppression de l'historique
NavigationService.pushNamedAndRemoveUntil(AppRouter.login);
```

### Navigation depuis le contexte

```dart
Navigator.of(context).pushNamed(
  AppRouter.productDetails,
  arguments: {
    'product': productModel,
    'heroTag': 'hero_tag',
  },
);
```

### Retour en arrière

```dart
NavigationService.pop(); // Retour simple
NavigationService.popUntil(AppRouter.home); // Retour jusqu'à une route
```

## 🔧 Méthodes helper disponibles

- `toHome()` - Aller à l'accueil
- `toLogin()` - Aller à la connexion
- `toProductDetails(product, ...)` - Détails produit
- `toStoreDetails(store)` - Détails boutique
- `toCategoryProducts(category)` - Produits catégorie
- `toCart()` - Panier
- `toCheckout()` - Checkout
- `toOrderDetails(order)` - Détails commande
- `toPaymentMethodSelection(...)` - Sélection paiement
- `toSearch()` - Recherche
- `toReviews(productId)` - Avis
- `toImageGallery(...)` - Galerie
- `toSellerDashboard()` - Dashboard vendeur
- `toEditProduct(productId)` - Éditer produit
- `toWebView(url, title)` - WebView

## ⚠️ Notes importantes

1. **Objets requis** : Certains écrans nécessitent des objets complets (ProductModel, StoreModel, etc.) plutôt que des IDs
2. **Arguments** : Les arguments doivent être passés dans une Map<String, dynamic>
3. **Typage** : Utilisez les méthodes helper pour une meilleure sécurité de type

## 🔄 Migration depuis MaterialPageRoute

Remplacez les navigations directes :

```dart
// Avant
Navigator.push(
  context,
  MaterialPageRoute(builder: (_) => ProductDetailsScreen(product: product)),
);

// Après
NavigationService.toProductDetails(product: product);
// ou
Navigator.of(context).pushNamed(
  AppRouter.productDetails,
  arguments: {'product': product},
);
```

## 📝 Prochaines étapes

1. ✅ Système de routage créé
2. ⏳ Mise à jour des écrans pour utiliser le router (en cours)
3. ⏳ Ajout des routes API mobiles manquantes dans le backend
4. ⏳ Tests de navigation

