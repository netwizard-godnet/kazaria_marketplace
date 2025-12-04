import 'api_service.dart';
import '../config/api_config.dart';

class PaymentHistoryService {
  final ApiService _apiService = ApiService();

  /// Obtenir l'historique des paiements
  Future<Map<String, dynamic>> getPaymentHistory({int page = 1}) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.paymentHistory}?page=$page',
        requiresAuth: true,
      );

      if (result['success'] == true) {
        print(
          '✅ [PAYMENT_SERVICE] Historique chargé: ${result['payments']?.length ?? 0} paiements',
        );
      }

      return result;
    } catch (e) {
      print('❌ [PAYMENT_SERVICE] Erreur: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir les détails d'un paiement
  Future<Map<String, dynamic>> getPaymentDetails(int paymentId) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.paymentDetails}/$paymentId',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [PAYMENT_SERVICE] Erreur détails: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir l'historique des factures
  Future<Map<String, dynamic>> getInvoiceHistory({int page = 1}) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.invoiceHistory}?page=$page',
        requiresAuth: true,
      );

      if (result['success'] == true) {
        print(
          '✅ [INVOICE_SERVICE] Historique chargé: ${result['invoices']?.length ?? 0} factures',
        );
      }

      return result;
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir l'URL de téléchargement d'une facture
  Future<Map<String, dynamic>> getInvoiceDownloadUrl(String orderNumber) async {
    try {
      final result = await _apiService.get(
        '${ApiConfig.invoiceDownload}/$orderNumber/download',
        requiresAuth: true,
      );
      return result;
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur téléchargement: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}
