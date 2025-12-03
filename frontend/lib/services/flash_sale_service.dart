import '../config/api_config.dart';
import '../models/product_model.dart';
import 'api_service.dart';

/// Service pour gérer les ventes flash
class FlashSaleService {
  final ApiService _apiService = ApiService();

  /// Récupérer les produits en vente flash depuis le backend
  Future<Map<String, dynamic>> getFlashSaleProducts({
    int page = 1,
    int limit = 20,
  }) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.mobileFlashSales}?page=$page&limit=$limit',
        requiresAuth: false,
      );

      if (response['success'] == true) {
        final List<dynamic> productsData = response['data'] ?? [];
        final products = productsData
            .map((json) => ProductModel.fromJson(json))
            .toList();

        return {
          'success': true,
          'products': products,
          'total': response['total'] ?? 0,
        };
      }

      return {
        'success': false,
        'message': response['message'] ?? 'Erreur lors du chargement',
        'products': <ProductModel>[],
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur: $e',
        'products': <ProductModel>[],
      };
    }
  }

  /// Vérifier si un produit est en vente flash
  Future<bool> isProductInFlashSale(int productId) async {
    try {
      final response = await _apiService.get(
        '/products/$productId/flash-sale-status',
        requiresAuth: false,
      );

      return response['is_flash_sale'] == true;
    } catch (e) {
      return false;
    }
  }

  /// Obtenir le temps restant pour les ventes flash actuelles
  Future<Map<String, dynamic>> getFlashSaleTimeRemaining() async {
    try {
      final response = await _apiService.get(
        '/flash-sales/time-remaining',
        requiresAuth: false,
      );

      if (response['success'] == true) {
        return {
          'success': true,
          'hours': response['hours'] ?? 0,
          'minutes': response['minutes'] ?? 0,
          'seconds': response['seconds'] ?? 0,
          'end_time': response['end_time'],
        };
      }

      // Valeurs par défaut si pas de vente flash active
      return {'success': false, 'hours': 0, 'minutes': 0, 'seconds': 0};
    } catch (e) {
      return {'success': false, 'hours': 0, 'minutes': 0, 'seconds': 0};
    }
  }
}
