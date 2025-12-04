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
  bool _alertsLoading = false;
  String? _alertsError;
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
        _wishlists = List<Map<String, dynamic>>.from(
          response['wishlists'] ?? [],
        );
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
        print(
          '✅ [WISHLIST_PROVIDER] Liste chargée: ${_currentWishlist?['name']}',
        );
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

  /// Créer une alerte de prix pour un produit
  Future<Map<String, dynamic>> createPriceAlert({
    required int productId,
    required double targetPrice,
  }) async {
    try {
      final response = await _wishlistService.createPriceAlert(
        productId: productId,
        targetPrice: targetPrice,
      );

      if (response['success'] == true) {
        await loadAlerts();
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur création alerte: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Supprimer une alerte de prix
  Future<Map<String, dynamic>> deletePriceAlert(int alertId) async {
    try {
      final response = await _wishlistService.deletePriceAlert(alertId);

      if (response['success'] == true) {
        await loadAlerts();
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur suppression alerte: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Retirer un produit d'une wishlist
  Future<Map<String, dynamic>> removeProduct(
    int wishlistId,
    int productId,
  ) async {
    try {
      final response = await _wishlistService.removeProduct(
        wishlistId,
        productId,
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
      print('❌ [WISHLIST_PROVIDER] Erreur suppression: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Partager une wishlist (la rendre publique)
  Future<Map<String, dynamic>> shareWishlist(int wishlistId) async {
    try {
      final response = await _wishlistService.shareWishlist(wishlistId);

      if (response['success'] == true) {
        await loadWishlist(wishlistId);
        await loadWishlists();
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Arrêter le partage d'une wishlist (la rendre privée)
  Future<Map<String, dynamic>> unshareWishlist(int wishlistId) async {
    try {
      final response = await _wishlistService.unshareWishlist(wishlistId);

      if (response['success'] == true) {
        await loadWishlist(wishlistId);
        await loadWishlists();
      }

      return response;
    } catch (e) {
      print('❌ [WISHLIST_PROVIDER] Erreur arrêt partage: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Voir une wishlist partagée (par token)
  Future<void> loadSharedWishlist(String token) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _wishlistService.viewSharedWishlist(token);

      if (response['success'] == true) {
        _currentWishlist = response['wishlist'];
        print('✅ [WISHLIST_PROVIDER] Wishlist partagée chargée');
      } else {
        _error = response['message'];
      }
    } catch (e) {
      _error = e.toString();
      print('❌ [WISHLIST_PROVIDER] Erreur wishlist partagée: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// Charger toutes les alertes de prix
  Future<void> loadAlerts() async {
    _alertsLoading = true;
    _alertsError = null;
    notifyListeners();

    try {
      final response = await _wishlistService.getPriceAlerts();

      if (response['success'] == true) {
        _alertItems = List<Map<String, dynamic>>.from(response['alerts'] ?? []);
        print('✅ [WISHLIST_PROVIDER] ${_alertItems.length} alertes chargées');
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

  /// Supprimer une wishlist
  Future<Map<String, dynamic>> deleteWishlist(int id) async {
    try {
      final response = await _wishlistService.deleteWishlist(id);

      if (response['success'] == true) {
        await loadWishlists();
        await loadAlerts();
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
    _alertsLoading = false;
    _alertsError = null;
    _isLoading = false;
    _error = null;
    notifyListeners();
  }
}
