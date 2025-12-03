import 'api_service.dart';
import '../config/api_config.dart';

class CategoryService {
  final ApiService _apiService = ApiService();

  /// Récupérer les sous-catégories d'une catégorie
  Future<Map<String, dynamic>> getSubcategories(int categoryId) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/categories/$categoryId/subcategories',
        requiresAuth: false, // Public endpoint
      );
      return response;
    } catch (e) {
      print('❌ [CATEGORY] Erreur récupération sous-catégories: $e');
      return {
        'success': false,
        'message': e.toString(),
        'subcategories': [],
      };
    }
  }

  /// Récupérer toutes les catégories
  Future<Map<String, dynamic>> getAllCategories() async {
    try {
      final response = await _apiService.get(
        ApiConfig.categories,
        requiresAuth: false, // Public endpoint
      );
      return response;
    } catch (e) {
      print('❌ [CATEGORY] Erreur récupération catégories: $e');
      return {
        'success': false,
        'message': e.toString(),
        'categories': [],
      };
    }
  }
}
