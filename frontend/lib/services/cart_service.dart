import 'api_service.dart';
import '../config/api_config.dart';

class CartService {
  final ApiService _apiService = ApiService();

  /// Récupérer le panier de l'utilisateur
  Future<Map<String, dynamic>> getCart() async {
    try {
      return await _apiService.get(
        '${ApiConfig.baseUrl}/cart/items',
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Ajouter un produit au panier
  Future<Map<String, dynamic>> addToCart({
    required int productId,
    required int quantity,
    Map<String, String>? attributes, // ✅ Ajout paramètre attributs
  }) async {
    try {
      final Map<String, dynamic> body = {
        'product_id': productId,
        'quantity': quantity,
      };

      // ✅ Ajouter les attributs si présents
      if (attributes != null && attributes.isNotEmpty) {
        body['attributes'] = attributes;
      }

      return await _apiService.post(
        '${ApiConfig.baseUrl}/cart/add',
        body,
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour la quantité d'un produit dans le panier
  Future<Map<String, dynamic>> updateCartItem({
    required int cartItemId,
    required int quantity,
  }) async {
    try {
      return await _apiService.put(
        '${ApiConfig.baseUrl}/cart/update/$cartItemId',
        {'quantity': quantity},
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Supprimer un produit du panier
  Future<Map<String, dynamic>> removeFromCart(int cartItemId) async {
    try {
      return await _apiService.delete(
        '${ApiConfig.baseUrl}/cart/remove/$cartItemId',
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Vider le panier
  Future<Map<String, dynamic>> clearCart() async {
    try {
      return await _apiService.delete(
        '${ApiConfig.baseUrl}/cart/clear',
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Appliquer un code promo
  /// Note: Nécessite le subtotal pour calculer la réduction
  Future<Map<String, dynamic>> applyPromoCode({
    required String code,
    required double subtotal,
  }) async {
    try {
      return await _apiService.post('${ApiConfig.baseUrl}/coupons/apply', {
        'code': code,
        'subtotal': subtotal,
      }, requiresAuth: false); // Public endpoint
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Retirer un code promo
  Future<Map<String, dynamic>> removePromoCode() async {
    try {
      return await _apiService.delete(
        '${ApiConfig.baseUrl}/cart/remove-promo',
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }
}
