import 'package:flutter/material.dart';
import 'app_router.dart';
import '../models/category_model.dart';
import '../models/product_model.dart';
import '../models/store_model.dart';
import '../models/order_model.dart';
import '../models/payment_method.dart';

/// Service de navigation centralisé pour faciliter la navigation dans l'application
class NavigationService {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  
  /// Obtenir le contexte de navigation actuel
  static BuildContext? get context => navigatorKey.currentContext;
  
  /// Navigation vers une route nommée avec arguments
  static Future<T?>? pushNamed<T extends Object?>(
    String routeName, {
    Map<String, dynamic>? arguments,
  }) {
    return navigatorKey.currentState?.pushNamed<T>(
      routeName,
      arguments: arguments,
    );
  }
  
  /// Navigation vers une route nommée avec remplacement (retour impossible)
  static Future<T?>? pushReplacementNamed<T extends Object?, TO extends Object?>(
    String routeName, {
    Map<String, dynamic>? arguments,
    TO? result,
  }) {
    return navigatorKey.currentState?.pushReplacementNamed<T, TO>(
      routeName,
      arguments: arguments,
      result: result,
    );
  }
  
  /// Navigation vers une route nommée en supprimant toutes les routes précédentes
  static Future<T?>? pushNamedAndRemoveUntil<T extends Object?>(
    String routeName, {
    Map<String, dynamic>? arguments,
    bool Function(Route<dynamic>)? predicate,
  }) {
    return navigatorKey.currentState?.pushNamedAndRemoveUntil<T>(
      routeName,
      predicate ?? (route) => false,
      arguments: arguments,
    );
  }
  
  /// Retour en arrière
  static void pop<T extends Object?>([T? result]) {
    navigatorKey.currentState?.pop(result);
  }
  
  /// Retour en arrière si possible
  static bool canPop() {
    return navigatorKey.currentState?.canPop() ?? false;
  }
  
  /// Retour en arrière jusqu'à une route spécifique
  static void popUntil(String routeName) {
    navigatorKey.currentState?.popUntil((route) {
      return route.settings.name == routeName;
    });
  }
  
  // Méthodes de navigation spécifiques pour faciliter l'utilisation
  
  /// Aller à la page d'accueil
  static Future<void> toHome() {
    return pushNamedAndRemoveUntil(AppRouter.main) ?? Future.value();
  }
  
  /// Aller à la page de connexion
  static Future<void> toLogin() {
    return pushNamedAndRemoveUntil(AppRouter.login) ?? Future.value();
  }
  
  /// Aller aux détails d'un produit
  static Future<void> toProductDetails({
    required ProductModel product,
    Map<String, String>? selectedAttributes,
    String? heroTag,
  }) {
    return pushNamed(
      AppRouter.productDetails,
      arguments: {
        'product': product,
        'selectedAttributes': selectedAttributes,
        'heroTag': heroTag,
      },
    ) ?? Future.value();
  }
  
  /// Aller aux détails d'une boutique
  static Future<void> toStoreDetails({
    required StoreModel store,
  }) {
    return pushNamed(
      AppRouter.storeDetails,
      arguments: {
        'store': store,
      },
    ) ?? Future.value();
  }
  
  /// Aller aux produits d'une catégorie
  static Future<void> toCategoryProducts({
    required CategoryModel category,
  }) {
    return pushNamed(
      AppRouter.categoryProducts,
      arguments: {
        'category': category,
      },
    ) ?? Future.value();
  }
  
  /// Aller au panier
  static Future<void> toCart() {
    return pushNamed(AppRouter.cart) ?? Future.value();
  }
  
  /// Aller au checkout
  static Future<void> toCheckout() {
    return pushNamed(AppRouter.checkout) ?? Future.value();
  }
  
  /// Aller aux détails d'une commande
  static Future<void> toOrderDetails(OrderModel order) {
    return pushNamed(
      AppRouter.orderDetails,
      arguments: {'order': order},
    ) ?? Future.value();
  }
  
  /// Aller à la sélection de méthode de paiement
  static Future<void> toPaymentMethodSelection({
    required String orderId,
    required double amount,
    String currency = 'XOF',
    bool selectionOnly = false,
  }) {
    return pushNamed(
      AppRouter.paymentMethodSelection,
      arguments: {
        'orderId': orderId,
        'amount': amount,
        'currency': currency,
        'selectionOnly': selectionOnly,
      },
    ) ?? Future.value();
  }
  
  /// Aller au paiement mobile money
  static Future<void> toMobileMoneyPayment({
    required String orderId,
    required double amount,
    required PaymentMethod paymentMethod,
    String currency = 'XOF',
  }) {
    return pushNamed(
      AppRouter.mobileMoneyPayment,
      arguments: {
        'orderId': orderId,
        'amount': amount,
        'currency': currency,
        'paymentMethod': paymentMethod,
      },
    ) ?? Future.value();
  }
  
  /// Aller à la recherche
  static Future<void> toSearch() {
    return pushNamed(AppRouter.search) ?? Future.value();
  }
  
  /// Aller aux avis d'un produit
  static Future<void> toReviews(int productId) {
    return pushNamed(
      AppRouter.reviews,
      arguments: {'productId': productId},
    ) ?? Future.value();
  }
  
  /// Aller à la galerie d'images
  static Future<void> toImageGallery({
    required List<String> images,
    int initialIndex = 0,
  }) {
    return pushNamed(
      AppRouter.imageGallery,
      arguments: {
        'images': images,
        'initialIndex': initialIndex,
      },
    ) ?? Future.value();
  }
  
  /// Aller au profil vendeur (dashboard)
  static Future<void> toSellerDashboard() {
    return pushNamed(AppRouter.sellerDashboard) ?? Future.value();
  }
  
  /// Aller à l'édition d'un produit
  static Future<void> toEditProduct(int productId) {
    return pushNamed(
      AppRouter.editProduct,
      arguments: {'productId': productId},
    ) ?? Future.value();
  }
  
  /// Aller à la comparaison de produits
  static Future<void> toProductComparison() {
    return pushNamed(AppRouter.productComparison) ?? Future.value();
  }
  
  /// Aller au WebView
  static Future<void> toWebView({
    required String url,
    String? title,
  }) {
    return pushNamed(
      AppRouter.webview,
      arguments: {
        'url': url,
        'title': title,
      },
    ) ?? Future.value();
  }
  
  /// Aller à la liste de produits
  static Future<void> toProductsList({
    required String title,
    required String category,
    IconData icon = Icons.shopping_bag,
  }) {
    return pushNamed(
      AppRouter.productsList,
      arguments: {
        'title': title,
        'category': category,
        'icon': icon,
      },
    ) ?? Future.value();
  }
  
  /// Aller aux détails d'une wishlist
  static Future<void> toWishlistDetails({
    required int wishlistId,
    required String wishlistName,
  }) {
    return pushNamed(
      AppRouter.wishlistDetails,
      arguments: {
        'wishlistId': wishlistId,
        'wishlistName': wishlistName,
      },
    ) ?? Future.value();
  }
  
  /// Aller à l'écran d'ajout/modification d'adresse
  static Future<void> toAddAddress({Map<String, dynamic>? address}) {
    return pushNamed(
      AppRouter.addAddress,
      arguments: {'address': address},
    ) ?? Future.value();
  }
  
  /// Aller à l'écran de vérification de code (auth)
  static Future<void> toVerifyCode(String email) {
    return pushNamed(
      AppRouter.verifyCode,
      arguments: {'email': email},
    ) ?? Future.value();
  }
  
  /// Aller à l'écran de vérification de code vendeur
  static Future<void> toSellerVerifyCode(String email) {
    return pushNamed(
      AppRouter.sellerVerifyCode,
      arguments: {'email': email},
    ) ?? Future.value();
  }
}
