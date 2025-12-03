import 'api_service.dart';
import '../config/api_config.dart';

class CouponService {
  final ApiService _apiService = ApiService();

  /// Appliquer un code promo
  /// Nécessite le subtotal pour calculer la réduction
  Future<Map<String, dynamic>> applyCoupon({
    required String code,
    required double subtotal,
  }) async {
    try {
      final response = await _apiService.post(
        ApiConfig.applyCoupon,
        {
          'code': code.toUpperCase(), // Le backend convertit en majuscules
          'subtotal': subtotal,
        },
        requiresAuth: false, // Public endpoint
      );
      return response;
    } catch (e) {
      print('❌ [COUPON] Erreur application code promo: $e');
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }
}

