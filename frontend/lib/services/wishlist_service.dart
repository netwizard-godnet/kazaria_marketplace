import 'api_service.dart';
import '../config/api_config.dart';

class WishlistService {
  final ApiService _apiService = ApiService();

  /// Obtenir toutes les wishlists
  Future<Map<String, dynamic>> getWishlists() async {
    try {
      final result = await _apiService.get(
        ApiConfig.wishlists,
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Créer une nouvelle wishlist
  Future<Map<String, dynamic>> createWishlist({
    required String name,
    String? description,
    String? icon,
    String privacy = 'private',
  }) async {
    try {
      final result = await _apiService.post(ApiConfig.wishlists, {
        'name': name,
        'description': description,
        'is_public': privacy == 'public',
      }, requiresAuth: true);
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur création: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir une wishlist spécifique
  Future<Map<String, dynamic>> getWishlist(int id) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.wishlists}/$id',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour une wishlist
  Future<Map<String, dynamic>> updateWishlist(
    int id, {
    String? name,
    String? description,
    String? icon,
    String? privacy,
  }) async {
    try {
      final data = <String, dynamic>{};
      if (name != null) data['name'] = name;
      if (description != null) data['description'] = description;
      if (icon != null) data['icon'] = icon;
      if (privacy != null) data['privacy'] = privacy;

      final result = await _apiService.put(
        '${ApiConfig.wishlists}/$id',
        data,
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur mise à jour: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Supprimer une wishlist
  Future<Map<String, dynamic>> deleteWishlist(int id) async {
    try {
      final result = await _apiService.delete(
        '${ApiConfig.wishlists}/$id',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur suppression: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Ajouter un produit à une wishlist
  Future<Map<String, dynamic>> addProduct({
    required int wishlistId,
    required int productId,
    double? targetPrice,
    String? note,
    int priority = 0,
  }) async {
    try {
      final result = await _apiService.post(
        '${ApiConfig.wishlists}/$wishlistId/products',
        {'product_id': productId, 'notes': note, 'priority': priority},
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur ajout produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Retirer un produit d'une wishlist
  Future<Map<String, dynamic>> removeProduct(
    int wishlistId,
    int productId,
  ) async {
    try {
      final result = await _apiService.delete(
        '${ApiConfig.wishlists}/$wishlistId/products/$productId',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur suppression produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Créer une alerte de prix
  Future<Map<String, dynamic>> createPriceAlert({
    required int productId,
    required double targetPrice,
  }) async {
    try {
      final result = await _apiService.post(ApiConfig.priceAlerts, {
        'product_id': productId,
        'target_price': targetPrice,
      }, requiresAuth: true);
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur création alerte: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir toutes les alertes de prix
  Future<Map<String, dynamic>> getPriceAlerts() async {
    try {
      final result = await _apiService.get(
        ApiConfig.priceAlerts,
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur récupération alertes: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Supprimer une alerte de prix
  Future<Map<String, dynamic>> deletePriceAlert(int alertId) async {
    try {
      final result = await _apiService.delete(
        '${ApiConfig.priceAlerts}/$alertId',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur suppression alerte: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Partager une wishlist (génère un lien public)
  Future<Map<String, dynamic>> shareWishlist(int wishlistId) async {
    try {
      // Mettre la wishlist en public pour générer un token de partage
      final result = await _apiService.put(
        '${ApiConfig.wishlists}/$wishlistId',
        {'is_public': true},
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Voir une wishlist partagée (par token)
  Future<Map<String, dynamic>> viewSharedWishlist(String token) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.wishlistsShared}/$token',
        requiresAuth: false,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur wishlist partagée: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Rendre une wishlist privée (désactiver le partage)
  Future<Map<String, dynamic>> unshareWishlist(int wishlistId) async {
    try {
      final result = await _apiService.put(
        '${ApiConfig.wishlists}/$wishlistId',
        {'is_public': false},
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur désactivation partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer toutes les alertes de prix actives
  Future<Map<String, dynamic>> getWishlistAlerts() async {
    try {
      final result = await _apiService.get(
        ApiConfig.priceAlerts,
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur récupération alertes: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}
