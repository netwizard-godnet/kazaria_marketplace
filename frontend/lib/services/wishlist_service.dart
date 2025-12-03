import 'api_service.dart';
import '../config/api_config.dart';

class WishlistService {
  final ApiService _apiService = ApiService();

  /// Obtenir toutes les wishlists
  Future<Map<String, dynamic>> getWishlists() async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.baseUrl}/wishlists',
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
      final result = await _apiService.post(
        '${ApiConfig.baseUrl}/wishlists',
        {
          'name': name,
          'description': description,
          'icon': icon ?? '❤️',
          'privacy': privacy,
        },
        requiresAuth: true,
      );
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
        '${ApiConfig.baseUrl}/wishlists/$id',
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
        '${ApiConfig.baseUrl}/wishlists/$id',
        data,
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
        '${ApiConfig.baseUrl}/wishlists/$id',
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
        '${ApiConfig.baseUrl}/wishlists/items',
        {
          'wishlist_id': wishlistId,
          'product_id': productId,
          'target_price': targetPrice,
          'note': note,
          'priority': priority,
        },
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur ajout produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Retirer un produit d'une wishlist
  Future<Map<String, dynamic>> removeProduct(int itemId) async {
    try {
      final result = await _apiService.delete(
        '${ApiConfig.baseUrl}/wishlists/items/$itemId',
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur suppression produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour un produit dans la wishlist (alertes, note, priorité)
  Future<Map<String, dynamic>> updateWishlistItem(
    int itemId, {
    double? targetPrice,
    bool sendTargetPrice = false,
    bool? priceAlertEnabled,
    bool? stockAlertEnabled,
    String? note,
    int? priority,
  }) async {
    try {
      final payload = <String, dynamic>{};
      if (sendTargetPrice) payload['target_price'] = targetPrice;
      if (priceAlertEnabled != null) payload['price_alert_enabled'] = priceAlertEnabled;
      if (stockAlertEnabled != null) payload['stock_alert_enabled'] = stockAlertEnabled;
      if (note != null) payload['note'] = note;
      if (priority != null) payload['priority'] = priority;

      final result = await _apiService.put(
        '${ApiConfig.baseUrl}/wishlists/items/$itemId',
        payload,
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur mise à jour produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Partager une wishlist
  Future<Map<String, dynamic>> shareWishlist({
    required int wishlistId,
    String? email,
    String permission = 'view',
    int? expiresInDays,
  }) async {
    try {
      final result = await _apiService.post(
        '${ApiConfig.baseUrl}/wishlists/$wishlistId/share',
        {
          'email': email,
          'permission': permission,
          'expires_in_days': expiresInDays,
        },
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Voir une wishlist partagée
  Future<Map<String, dynamic>> viewSharedWishlist(String token) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.baseUrl}/wishlists/shared/$token',
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur wishlist partagée: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Lister les partages d'une wishlist
  Future<Map<String, dynamic>> getWishlistShares(int wishlistId) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.baseUrl}/wishlists/$wishlistId/shares',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur récupération partages: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Révoquer un partage de wishlist
  Future<Map<String, dynamic>> revokeWishlistShare(int shareId) async {
    try {
      final result = await _apiService.delete(
        '${ApiConfig.baseUrl}/wishlists/shares/$shareId',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur révocation partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer tous les items avec alertes actives
  Future<Map<String, dynamic>> getWishlistAlerts() async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.baseUrl}/wishlists/alerts',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur récupération alertes: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer l'historique des alertes déclenchées
  Future<Map<String, dynamic>> getAlertHistory() async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.baseUrl}/wishlists/alerts/history',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur historique alertes: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer les préférences de notification
  Future<Map<String, dynamic>> getNotificationPreferences() async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.baseUrl}/notifications/preferences',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur préférences notifications: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour les préférences de notification
  Future<Map<String, dynamic>> updateNotificationPreferences(Map<String, dynamic> payload) async {
    try {
      final result = await _apiService.put(
        '${ApiConfig.baseUrl}/notifications/preferences',
        payload,
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [WISHLIST_SERVICE] Erreur mise à jour préférences: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}

