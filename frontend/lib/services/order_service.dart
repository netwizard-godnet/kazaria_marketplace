import 'api_service.dart';
import '../config/api_config.dart';

class OrderService {
  final ApiService _apiService = ApiService();

  /// Créer une nouvelle commande
  Future<Map<String, dynamic>> createOrder({
    required String shippingName,
    required String shippingEmail,
    required String shippingPhone,
    required String shippingAddress,
    required String shippingCity,
    required String shippingCountry,
    required String paymentMethod,
    String? shippingPostalCode,
    String? customerNotes,
    String? promoCode,
  }) async {
    try {
      return await _apiService.post(
        ApiConfig.createOrder,
        {
          'shipping_name': shippingName,
          'shipping_email': shippingEmail,
          'shipping_phone': shippingPhone,
          'shipping_address': shippingAddress,
          'shipping_city': shippingCity,
          'shipping_country': shippingCountry,
          'shipping_postal_code': shippingPostalCode,
          'payment_method': paymentMethod,
          'customer_notes': customerNotes,
          'promo_code': promoCode,
        },
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir les commandes de l'utilisateur
  Future<Map<String, dynamic>> getMyOrders() async {
    try {
      return await _apiService.get(
        ApiConfig.myOrders,
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir les détails d'une commande
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> getOrderDetails(String orderNumber) async {
    try {
      return await _apiService.get(
        '${ApiConfig.orderDetails}/$orderNumber',
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Annuler une commande
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> cancelOrder(String orderNumber) async {
    try {
      return await _apiService.post(
        '${ApiConfig.orderDetails}/$orderNumber/cancel',
        {},
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer les commandes de l'utilisateur
  Future<Map<String, dynamic>> getUserOrders() async {
    try {
      return await _apiService.get(
        ApiConfig.getUserOrders,
        requiresAuth: true,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// 🔍 Suivre une commande par numéro et email (public, sans authentification)
  /// Avec retry automatique en cas d'échec réseau
  Future<Map<String, dynamic>> trackOrder({
    required String orderNumber,
    required String email,
    int maxRetries = 3,
  }) async {
    int attempts = 0;
    
    while (attempts < maxRetries) {
      try {
        print('🔍 [TRACK_ORDER] Tentative ${attempts + 1}/$maxRetries');
        
        return await _apiService.post(
          ApiConfig.trackOrder,
          {
            'order_number': orderNumber,
            'email': email,
          },
          requiresAuth: false,
        );
      } catch (e) {
        attempts++;
        
        // Si c'est la dernière tentative, retourner l'erreur
        if (attempts >= maxRetries) {
          print('❌ [TRACK_ORDER] Échec après $maxRetries tentatives: $e');
          return {
            'success': false,
            'message': _getErrorMessage(e),
            'error': e.toString(),
          };
        }
        
        // Attendre avant de réessayer (backoff exponentiel: 1s, 2s, 4s...)
        final delaySeconds = attempts * attempts;
        print('⏳ [TRACK_ORDER] Nouvelle tentative dans ${delaySeconds}s...');
        await Future.delayed(Duration(seconds: delaySeconds));
      }
    }
    
    return {'success': false, 'message': 'Échec après plusieurs tentatives'};
  }
  
  /// 📝 Convertir les erreurs techniques en messages clairs
  String _getErrorMessage(dynamic error) {
    final errorString = error.toString().toLowerCase();
    
    if (errorString.contains('socketexception') || 
        errorString.contains('network') ||
        errorString.contains('connection')) {
      return 'Pas de connexion Internet. Vérifiez votre réseau.';
    }
    
    if (errorString.contains('timeout')) {
      return 'Le serveur met trop de temps à répondre. Réessayez.';
    }
    
    if (errorString.contains('order_not_found')) {
      return 'Commande introuvable. Vérifiez le numéro.';
    }
    
    if (errorString.contains('invalid_email')) {
      return 'Email incorrect. Utilisez l\'email de commande.';
    }
    
    if (errorString.contains('429') || errorString.contains('too many')) {
      return 'Trop de tentatives. Attendez quelques instants.';
    }
    
    return 'Une erreur est survenue. Réessayez plus tard.';
  }

  /// 📄 Télécharger la facture PDF d'une commande
  /// Note: Route web (pas API), utilise orderNumber (string)
  Future<String?> downloadInvoiceUrl(String orderNumber) async {
    try {
      // URL pour télécharger la facture (route web, pas API)
      final baseUrl = ApiConfig.baseUrl.replaceAll('/api', '');
      return '$baseUrl/order/download/$orderNumber';
    } catch (e) {
      return null;
    }
  }
}

