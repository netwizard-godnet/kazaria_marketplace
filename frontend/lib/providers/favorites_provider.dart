import 'package:flutter/foundation.dart';
import '../models/product_model.dart';
import '../services/auth_service.dart';

class FavoritesProvider with ChangeNotifier {
  final AuthService _authService = AuthService();
  
  List<ProductModel> _favorites = [];
  Set<int> _favoriteIds = {};
  bool _isLoading = false;
  String? _error;

  // Getters
  List<ProductModel> get favorites => _favorites;
  Set<int> get favoriteIds => _favoriteIds;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get favoritesCount => _favorites.length;

  /// Vérifier si un produit est en favori
  bool isFavorite(int productId) {
    return _favoriteIds.contains(productId);
  }

  /// Charger les favoris depuis l'API
  Future<void> loadFavorites() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _authService.getFavorites();
      
      if (response['success']) {
        // Essayer différentes structures de réponse
        List<dynamic> favoritesData = [];
        
        if (response['data'] != null) {
          if (response['data'] is List) {
            // Structure directe : response['data'] = [products...]
            favoritesData = response['data'];
          } else if (response['data']['favorites'] != null) {
            // Structure avec clé favorites : response['data']['favorites'] = [products...]
            favoritesData = response['data']['favorites'];
          }
        } else if (response['favorites'] != null) {
          // Structure directe : response['favorites'] = [products...]
          favoritesData = response['favorites'];
        }
        
        _favorites = favoritesData
            .map((item) {
              try {
                // Si c'est un objet avec clé 'product', l'utiliser
                if (item is Map<String, dynamic> && item.containsKey('product')) {
                  return ProductModel.fromJson(item['product']);
                }
                // Sinon, utiliser l'objet directement
                return ProductModel.fromJson(item);
              } catch (e) {
                print('❌ [FAVORITES] Erreur parsing produit: $e');
                print('❌ [FAVORITES] Données: $item');
                return null;
              }
            })
            .where((product) => product != null)
            .cast<ProductModel>()
            .toList();
            
        _favoriteIds = _favorites.map((p) => p.id).toSet();
        print('✅ [FAVORITES] Chargés: ${_favorites.length} favoris');
        print('📊 [FAVORITES] Structure reçue: ${response.keys}');
      } else {
        _error = response['message'] ?? 'Erreur lors du chargement des favoris';
        print('❌ [FAVORITES] Erreur: $_error');
        print('📊 [FAVORITES] Réponse complète: $response');
      }
    } catch (e) {
      _error = e.toString();
      print('💥 [FAVORITES] Exception: $_error');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// Ajouter/Retirer un produit des favoris
  Future<Map<String, dynamic>> toggleFavorite(int productId) async {
    print('🔄 [FAVORITES] Toggle favori pour produit $productId');
    print('📊 [FAVORITES] État avant: ${_favoriteIds.length} favoris');
    
    try {
      final response = await _authService.toggleFavorite(productId);
      
      print('📥 [FAVORITES] Réponse API: ${response['success']}');
      print('📥 [FAVORITES] Message: ${response['message']}');
      print('📥 [FAVORITES] is_favorite: ${response['is_favorite']}');
      
      if (response['success']) {
        final isFavorite = response['is_favorite'] ?? false;
        
        if (isFavorite) {
          // Ajouter aux favoris (mise à jour locale uniquement)
          if (!_favoriteIds.contains(productId)) {
            _favoriteIds.add(productId);
            print('✅ [FAVORITES] Produit $productId ajouté aux favoris');
            print('📊 [FAVORITES] Total favoris maintenant: ${_favoriteIds.length}');
            
            // Note: Ne pas recharger immédiatement car le backend peut avoir un délai
            // La liste sera rechargée quand l'utilisateur ouvrira la page des favoris
          }
        } else {
          // Retirer des favoris
          _favoriteIds.remove(productId);
          _favorites.removeWhere((p) => p.id == productId);
          print('✅ [FAVORITES] Produit $productId retiré des favoris');
          print('📊 [FAVORITES] Total favoris maintenant: ${_favoriteIds.length}');
        }
        
        notifyListeners();
        
        return {
          'success': true,
          'message': response['message'] ?? 'Favori mis à jour',
          'is_favorite': isFavorite,
        };
      } else {
        print('❌ [FAVORITES] Erreur API: ${response['message']}');
        return response;
      }
    } catch (e) {
      print('💥 [FAVORITES] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Ajouter un produit aux favoris (sans appel API)
  void addToFavorites(ProductModel product) {
    if (!_favoriteIds.contains(product.id)) {
      _favoriteIds.add(product.id);
      _favorites.add(product);
      notifyListeners();
    }
  }

  /// Retirer un produit des favoris (sans appel API)
  void removeFromFavorites(int productId) {
    _favoriteIds.remove(productId);
    _favorites.removeWhere((p) => p.id == productId);
    notifyListeners();
  }

  /// Vider les favoris
  void clearFavorites() {
    _favorites.clear();
    _favoriteIds.clear();
    notifyListeners();
  }

  /// Effacer les erreurs
  void clearError() {
    _error = null;
    notifyListeners();
  }
}
