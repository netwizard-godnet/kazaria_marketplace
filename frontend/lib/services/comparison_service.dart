import 'api_service.dart';
import '../config/api_config.dart';

class ComparisonService {
  final ApiService _apiService = ApiService();

  /// Comparer plusieurs produits (sans sauvegarder)
  Future<Map<String, dynamic>> compareProducts(List<int> productIds) async {
    try {
      if (productIds.length < 2 || productIds.length > 4) {
        return {
          'success': false,
          'message': 'Veuillez sélectionner entre 2 et 4 produits',
        };
      }

      final result = await _apiService.post(ApiConfig.comparisonCompare, {
        'product_ids': productIds,
      }, requiresAuth: false);

      if (result['success'] == true) {
        print(
          '✅ [COMPARISON_SERVICE] Comparaison réussie de ${productIds.length} produits',
        );
      } else {
        print('❌ [COMPARISON_SERVICE] Erreur: ${result['message']}');
      }

      return result;
    } catch (e) {
      print('❌ [COMPARISON_SERVICE] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Créer et sauvegarder une comparaison
  Future<Map<String, dynamic>> createComparison(
    List<int> productIds, {
    String? name,
  }) async {
    try {
      final result = await _apiService.post(ApiConfig.comparison, {
        'product_ids': productIds,
        'name': name,
      }, requiresAuth: false);
      return result;
    } catch (e) {
      print('❌ [COMPARISON_SERVICE] Erreur création: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir l'historique des comparaisons
  Future<Map<String, dynamic>> getComparisonHistory() async {
    try {
      final result = await _apiService.get(
        ApiConfig.comparison,
        requiresAuth: false,
      );
      return result;
    } catch (e) {
      print('❌ [COMPARISON_SERVICE] Erreur historique: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir une comparaison spécifique
  Future<Map<String, dynamic>> getComparison(int id) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.comparison}/$id',
        requiresAuth: false,
      );
      return result;
    } catch (e) {
      print('❌ [COMPARISON_SERVICE] Erreur: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Supprimer une comparaison
  Future<Map<String, dynamic>> deleteComparison(int id) async {
    try {
      final result = await _apiService.delete(
        '${ApiConfig.comparison}/$id',
        requiresAuth: false,
      );
      return result;
    } catch (e) {
      print('❌ [COMPARISON_SERVICE] Erreur suppression: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}
