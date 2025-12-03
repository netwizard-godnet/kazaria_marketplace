import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/payment_method.dart';
import '../models/payment_transaction.dart';
import '../models/payment_result.dart';

class PaymentService {
  static const String _baseUrl = ApiConfig.baseUrl;

  /// Récupérer les méthodes de paiement disponibles
  static Future<List<PaymentMethod>> getAvailablePaymentMethods() async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/api/payment-methods'),
        headers: ApiConfig.headers(),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          final methods = (data['payment_methods'] as List)
              .map((method) => PaymentMethod.fromJson(method))
              .toList();
          return methods;
        }
      }

      // Fallback vers les méthodes par défaut si l'API échoue
      return PaymentMethod.getDefaultMethods();
    } catch (e) {
      print('Erreur récupération méthodes de paiement: $e');
      return PaymentMethod.getDefaultMethods();
    }
  }

  /// Initier un paiement Mobile Money
  static Future<PaymentResult> initiateMobileMoneyPayment({
    required String orderId,
    required String provider, // 'orange', 'mtn', 'moov'
    required String phoneNumber,
    required double amount,
    String? description,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/api/payments/mobile-money/initiate'),
        headers: ApiConfig.headers(),
        body: json.encode({
          'order_id': orderId,
          'provider': provider,
          'phone_number': phoneNumber,
          'amount': amount,
          'currency': 'XOF',
          'description': description ?? 'Paiement commande #$orderId',
        }),
      );

      final data = json.decode(response.body);
      return PaymentResult.fromJson(data);
    } catch (e) {
      print('Erreur initiation paiement Mobile Money: $e');
      return PaymentResult.paymentFailure(
        message: 'Erreur lors de l\'initiation du paiement: $e',
      );
    }
  }

  /// Vérifier le statut d'un paiement
  static Future<PaymentTransaction?> checkPaymentStatus(String transactionId) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/api/payments/$transactionId/status'),
        headers: ApiConfig.headers(),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          return PaymentTransaction.fromJson(data['transaction']);
        }
      }

      return null;
    } catch (e) {
      print('Erreur vérification statut paiement: $e');
      return null;
    }
  }

  /// Confirmer un paiement
  static Future<PaymentResult> confirmPayment({
    required String transactionId,
    String? verificationCode,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/api/payments/$transactionId/confirm'),
        headers: ApiConfig.headers(),
        body: json.encode({
          'verification_code': verificationCode,
        }),
      );

      final data = json.decode(response.body);
      return PaymentResult.fromJson(data);
    } catch (e) {
      print('Erreur confirmation paiement: $e');
      return PaymentResult.paymentFailure(
        message: 'Erreur lors de la confirmation du paiement: $e',
      );
    }
  }

  /// Annuler un paiement
  static Future<PaymentResult> cancelPayment(String transactionId) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/api/payments/$transactionId/cancel'),
        headers: ApiConfig.headers(),
      );

      final data = json.decode(response.body);
      return PaymentResult.fromJson(data);
    } catch (e) {
      print('Erreur annulation paiement: $e');
      return PaymentResult.paymentFailure(
        message: 'Erreur lors de l\'annulation du paiement: $e',
      );
    }
  }

  /// Récupérer l'historique des paiements
  static Future<List<PaymentTransaction>> getPaymentHistory({
    int page = 1,
    int limit = 20,
  }) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/api/payments/history?page=$page&limit=$limit'),
        headers: ApiConfig.headers(),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          return (data['transactions'] as List)
              .map((transaction) => PaymentTransaction.fromJson(transaction))
              .toList();
        }
      }

      return [];
    } catch (e) {
      print('Erreur récupération historique paiements: $e');
      return [];
    }
  }

  /// Simuler un paiement Mobile Money (pour les tests)
  static Future<PaymentResult> simulateMobileMoneyPayment({
    required String orderId,
    required String provider,
    required String phoneNumber,
    required double amount,
  }) async {
    // Simulation d'un délai de traitement
    await Future.delayed(const Duration(seconds: 2));

    // Simulation d'un succès (90% de chance)
    final isSuccess = (DateTime.now().millisecond % 10) < 9;

    if (isSuccess) {
      return PaymentResult.paymentSuccess(
        transactionId: 'TXN_${DateTime.now().millisecondsSinceEpoch}',
        reference: 'REF_${DateTime.now().millisecondsSinceEpoch}',
        message: 'Paiement Mobile Money réussi',
        data: {
          'provider': provider,
          'phone_number': phoneNumber,
          'amount': amount,
          'timestamp': DateTime.now().toIso8601String(),
        },
      );
    } else {
      return PaymentResult.paymentFailure(
        errorCode: 'PAYMENT_FAILED',
        message: 'Paiement Mobile Money échoué',
      );
    }
  }

  /// Valider un numéro de téléphone pour Mobile Money
  static bool validatePhoneNumber(String phoneNumber, String provider) {
    // Nettoyer le numéro
    String cleanNumber = phoneNumber.replaceAll(RegExp(r'[^\d]'), '');
    
    // Formats attendus pour la Côte d'Ivoire
    switch (provider.toLowerCase()) {
      case 'orange':
        // Orange Money: 07XX XXX XXX ou +225 07XX XXX XXX
        return RegExp(r'^(225)?0?7[0-9]{8}$').hasMatch(cleanNumber);
      case 'mtn':
        // MTN Money: 05XX XXX XXX ou +225 05XX XXX XXX
        return RegExp(r'^(225)?0?5[0-9]{8}$').hasMatch(cleanNumber);
      case 'moov':
        // Moov Money: 01XX XXX XXX ou +225 01XX XXX XXX
        return RegExp(r'^(225)?0?1[0-9]{8}$').hasMatch(cleanNumber);
      default:
        // Format général pour la Côte d'Ivoire
        return RegExp(r'^(225)?0?[157][0-9]{8}$').hasMatch(cleanNumber);
    }
  }

  /// Formater un numéro de téléphone pour l'affichage
  static String formatPhoneNumber(String phoneNumber) {
    String cleanNumber = phoneNumber.replaceAll(RegExp(r'[^\d]'), '');
    
    // Enlever le préfixe pays si présent
    if (cleanNumber.startsWith('225')) {
      cleanNumber = cleanNumber.substring(3);
    }
    
    // Ajouter le 0 si manquant
    if (!cleanNumber.startsWith('0')) {
      cleanNumber = '0$cleanNumber';
    }
    
    // Formater: 0X XX XX XX XX
    if (cleanNumber.length == 10) {
      return '${cleanNumber.substring(0, 2)} ${cleanNumber.substring(2, 4)} ${cleanNumber.substring(4, 6)} ${cleanNumber.substring(6, 8)} ${cleanNumber.substring(8, 10)}';
    }
    
    return cleanNumber;
  }
}
