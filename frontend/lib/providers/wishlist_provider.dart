import 'package:flutter/foundation.dart';
import '../services/wishlist_service.dart';

class WishlistProvider with ChangeNotifier {
  final WishlistService _wishlistService = WishlistService();

  List<Map<String, dynamic>> _wishlists = [];
  Map<String, dynamic>? _currentWishlist;
  final Map<int, List<Map<String, dynamic>>> _sharesByWishlist = {};
  final Map<int, bool> _sharesLoading = {};
  final Map<int, String?> _sharesErrors = {};
  List<Map<String, dynamic>> _alertItems = [];
  List<Map<String, dynamic>> _alertHistory = [];
  bool _alertsLoading = false;
  bool _alertHistoryLoading = false;
  String? _alertsError;
  String? _alertHistoryError;
  Map<String, dynamic>? _notificationPreferences;
  bool _preferencesLoading = false;
  String? _preferencesError;
  bool _isLoading = false;
  String? _error;

  // Getters
  List<Map<String, dynamic>> get wishlists => _wishlists;
  Map<String, dynamic>? get currentWishlist => _currentWishlist;
  bool get isLoading => _isLoading;
  String? get error => _error;
  List<Map<String, dynamic>> get alertItems => _alertItems;
  bool get alertsLoading => _alertsLoading;
  String? get alertsError => _alertsError;
  List<Map<String, dynamic>> get alertHistory => _alertHistory;
  bool get alertHistoryLoading => _alertHistoryLoading;
  String? get alertHistoryError => _alertHistoryError;
  Map<String, dynamic>? get notificationPreferences => _notificationPreferences;
  bool get preferencesLoading => _preferencesLoading;
  String? get preferencesError => _preferencesError;
  List<Map<String, dynamic>> sharesForWishlist(int wishlistId) =>
      _sharesByWishlist[wishlistId] ?? [];
  bool isSharesLoading(int wishlistId) => _sharesLoading[wishlistId] ?? false;
  String? sharesError(int wishlistId) => _sharesErrors[wishlistId];

  /// Charger toutes les wishlists
  Future<void> loadWishlists() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _wishlistService.getWishlists();

      if (response['success'] == true) {
        _wishlists = List<Map<String, dynamic>>.from(response['wishlists'] ?? []);
        print('✅ [WISHLIST_PROVIDER] ${_wishlists.length} listes chargées');
      } else {
        _error = response['message'];
        print('❌ [WISHLIST_PROVIDER] Erreur: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('❌ [WISHLIST_PROVIDER] Exception: $_error');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// Créer une nouvelle wishlist
  Future<Map<String, dynamic>> createWishlist({
    required String name,
    String? description,
    String? icon,
    String privacy = 'private',
  }) async {
    try {
      final response = await _wishlistService.createWishlist(
        name: name,
        description: description,
        icon: icon,
        privacy: privacy,
      );

      if (response['success'] == true) {
        await loadWishlists();
        print('✅ [WISHLIST_PROVIDER] Liste créée: $name');
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur création: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Charger une wishlist spécifique
  Future<void> loadWishlist(int id) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _wishlistService.getWishlist(id);

      if (response['success'] == true) {
        _currentWishlist = response['wishlist'];
        print('✅ [WISHLIST_PROVIDER] Liste chargée: ${_currentWishlist?['name']}');
      } else {
        _error = response['message'];
      }
    } catch (e) {
      _error = e.toString();
      print('❌ [WISHLIST_PROVIDER] Exception: $_error');
    }

    _isLoading = false;
    notifyListeners();
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
      final response = await _wishlistService.addProduct(
        wishlistId: wishlistId,
        productId: productId,
        targetPrice: targetPrice,
        note: note,
        priority: priority,
      );

      if (response['success'] == true) {
        if (_currentWishlist != null && _currentWishlist!['id'] == wishlistId) {
          await loadWishlist(wishlistId);
        }
        await loadWishlists();
        await loadAlerts();
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur ajout produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour un item (alertes, note, priorité)
  Future<Map<String, dynamic>> updateItem(
    int itemId, {
    double? targetPrice,
    bool sendTargetPrice = false,
    bool? priceAlertEnabled,
    bool? stockAlertEnabled,
    String? note,
    int? priority,
  }) async {
    try {
      final response = await _wishlistService.updateWishlistItem(
        itemId,
        targetPrice: targetPrice,
        sendTargetPrice: sendTargetPrice,
        priceAlertEnabled: priceAlertEnabled,
        stockAlertEnabled: stockAlertEnabled,
        note: note,
        priority: priority,
      );

      if (response['success'] == true) {
        if (_currentWishlist != null) {
          await loadWishlist(_currentWishlist!['id']);
        }
        await loadAlerts();
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur mise à jour item: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Retirer un produit d'une wishlist
  Future<Map<String, dynamic>> removeProduct(int itemId, int wishlistId) async {
    try {
      final response = await _wishlistService.removeProduct(itemId);

      if (response['success'] == true) {
        if (_currentWishlist != null && _currentWishlist!['id'] == wishlistId) {
          await loadWishlist(wishlistId);
        }
        await loadWishlists();
        await loadAlerts();
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur suppression: $e');
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
      final response = await _wishlistService.shareWishlist(
        wishlistId: wishlistId,
        email: email,
        permission: permission,
        expiresInDays: expiresInDays,
      );

      if (response['success'] == true) {
        await loadWishlistShares(wishlistId);
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Charger les partages d'une wishlist
  Future<void> loadWishlistShares(int wishlistId) async {
    _sharesLoading[wishlistId] = true;
    _sharesErrors[wishlistId] = null;
    notifyListeners();

    try {
      final response = await _wishlistService.getWishlistShares(wishlistId);

      if (response['success'] == true) {
        _sharesByWishlist[wishlistId] =
            List<Map<String, dynamic>>.from(response['shares'] ?? []);
      } else {
        _sharesErrors[wishlistId] = response['message'];
      }
    } catch (e) {
      _sharesErrors[wishlistId] = e.toString();
      print('❌ [WISHLIST_PROVIDER] Erreur chargement partages: $e');
    }

    _sharesLoading[wishlistId] = false;
    notifyListeners();
  }

  /// Révoquer un partage
  Future<Map<String, dynamic>> revokeWishlistShare(
    int shareId,
    int wishlistId,
  ) async {
    try {
      final response = await _wishlistService.revokeWishlistShare(shareId);

      if (response['success'] == true) {
        await loadWishlistShares(wishlistId);
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur révocation partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Charger les items avec alertes actives
  Future<void> loadAlerts() async {
    _alertsLoading = true;
    _alertsError = null;
    notifyListeners();

    try {
      final response = await _wishlistService.getWishlistAlerts();

      if (response['success'] == true) {
        _alertItems = List<Map<String, dynamic>>.from(response['items'] ?? []);
      } else {
        _alertsError = response['message'];
      }
    } catch (e) {
      _alertsError = e.toString();
      print('❌ [WISHLIST_PROVIDER] Erreur chargement alertes: $e');
    }

    _alertsLoading = false;
    notifyListeners();
  }

  /// Charger l'historique des alertes déclenchées
  Future<void> loadAlertHistory() async {
    _alertHistoryLoading = true;
    _alertHistoryError = null;
    notifyListeners();

    try {
      final response = await _wishlistService.getAlertHistory();

      if (response['success'] == true) {
        _alertHistory = List<Map<String, dynamic>>.from(response['logs'] ?? []);
      } else {
        _alertHistoryError = response['message'];
      }
    } catch (e) {
      _alertHistoryError = e.toString();
      print('❌ [WISHLIST_PROVIDER] Erreur historique alertes: $e');
    }

    _alertHistoryLoading = false;
    notifyListeners();
  }

  /// Charger les préférences de notification
  Future<void> loadNotificationPreferences() async {
    _preferencesLoading = true;
    _preferencesError = null;
    notifyListeners();

    try {
      final response = await _wishlistService.getNotificationPreferences();

      if (response['success'] == true) {
        _notificationPreferences = Map<String, dynamic>.from(response['preferences'] ?? {});
      } else {
        _preferencesError = response['message'];
      }
    } catch (e) {
      _preferencesError = e.toString();
      print('❌ [WISHLIST_PROVIDER] Erreur chargement préférences: $e');
    }

    _preferencesLoading = false;
    notifyListeners();
  }

  /// Mettre à jour les préférences de notification
  Future<Map<String, dynamic>> updateNotificationPreferences(Map<String, dynamic> payload) async {
    try {
      final response = await _wishlistService.updateNotificationPreferences(payload);

      if (response['success'] == true) {
        _notificationPreferences = Map<String, dynamic>.from(response['preferences'] ?? {});
      }

      notifyListeners();
      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur update préférences: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour localement une préférence (sans API)
  void setNotificationPreferenceValue(String key, dynamic value) {
    final prefs = Map<String, dynamic>.from(_notificationPreferences ?? {});
    prefs[key] = value;
    _notificationPreferences = prefs;
    notifyListeners();
  }

  /// Supprimer une wishlist
  Future<Map<String, dynamic>> deleteWishlist(int id) async {
    try {
      final response = await _wishlistService.deleteWishlist(id);

      if (response['success'] == true) {
        await loadWishlists();
        await loadAlerts();
        await loadAlertHistory();
        _sharesByWishlist.remove(id);
        _sharesLoading.remove(id);
        _sharesErrors.remove(id);
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur suppression: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Effacer l'erreur
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Réinitialiser
  void reset() {
    _wishlists = [];
    _currentWishlist = null;
    _sharesByWishlist.clear();
    _sharesLoading.clear();
    _sharesErrors.clear();
    _alertItems = [];
    _alertHistory = [];
    _alertsLoading = false;
    _alertHistoryLoading = false;
    _alertsError = null;
    _alertHistoryError = null;
    _notificationPreferences = null;
    _preferencesLoading = false;
    _preferencesError = null;
    _isLoading = false;
    _error = null;
    notifyListeners();
  }
}

