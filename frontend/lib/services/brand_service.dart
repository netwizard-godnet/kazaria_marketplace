import '../config/api_config.dart';
import 'api_service.dart';

class BrandService {
  final ApiService _apiService = ApiService();

  /// Récupérer les marques en collaboration
  Future<Map<String, dynamic>> getBrands() async {
    try {
      final response = await _apiService.get(ApiConfig.mobileBrands);
      
      if (response['success'] == true) {
        final brandsData = response['data'] ?? [];
        print('✅ [BRAND_SERVICE] Marques chargées: ${brandsData.length}');
      } else {
        print('❌ [BRAND_SERVICE] Erreur: ${response['message']}');
      }
      
      return response;
    } catch (e) {
      print('💥 [BRAND_SERVICE] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}
