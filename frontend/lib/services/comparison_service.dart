import 'api_service.dart';
import '../config/api_config.dart';

class ComparisonService {
  final ApiService _apiService = ApiService();

  /// Comparer plusieurs produits
  Future<Map<String, dynamic>> compareProducts(List<int> productIds) async {
    try {
      if (productIds.length < 2 || productIds.length > 4) {
        return {
          'success': false,
          'message': 'Veuillez sélectionner entre 2 et 4 produits',
        };
      }

      final result = await _apiService.post(
        '${ApiConfig.baseUrl}/products/compare',
        {'product_ids': productIds},
      );

      if (result['success'] == true) {
        print('✅ [COMPARISON_SERVICE] Comparaison réussie de ${productIds.length} produits');
      } else {
        print('❌ [COMPARISON_SERVICE] Erreur: ${result['message']}');
      }

      return result;
    } catch (e) {
      print('❌ [COMPARISON_SERVICE] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir l'historique des comparaisons
  Future<Map<String, dynamic>> getComparisonHistory() async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.baseUrl}/products/comparison-history',
      );
      return result;
    } catch (e) {
      print('❌ [COMPARISON_SERVICE] Erreur historique: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}

