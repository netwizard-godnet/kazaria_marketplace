import 'package:flutter/foundation.dart';
import '../models/payment_method.dart';
import '../models/payment_transaction.dart';
import '../models/payment_result.dart';
import '../services/payment_service.dart';

class PaymentProvider with ChangeNotifier {
  List<PaymentMethod> _availableMethods = [];
  PaymentMethod? _selectedMethod;
  PaymentTransaction? _currentTransaction;
  List<PaymentTransaction> _paymentHistory = [];
  bool _isLoading = false;
  String? _error;
  bool _isProcessing = false;

  // Getters
  List<PaymentMethod> get availableMethods => _availableMethods;
  PaymentMethod? get selectedMethod => _selectedMethod;
  PaymentTransaction? get currentTransaction => _currentTransaction;
  List<PaymentTransaction> get paymentHistory => _paymentHistory;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isProcessing => _isProcessing;

  // Méthodes de gestion d'état
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String? error) {
    _error = error;
    notifyListeners();
  }

  void _setProcessing(bool processing) {
    _isProcessing = processing;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Charger les méthodes de paiement disponibles
  Future<void> loadPaymentMethods() async {
    _setLoading(true);
    _setError(null);

    try {
      _availableMethods = await PaymentService.getAvailablePaymentMethods();
    } catch (e) {
      _setError('Erreur lors du chargement des méthodes de paiement: $e');
    } finally {
      _setLoading(false);
    }
  }

  /// Sélectionner une méthode de paiement
  void selectPaymentMethod(PaymentMethod method) {
    _selectedMethod = method;
    _setError(null);
    notifyListeners();
  }

  /// Initier un paiement Mobile Money
  Future<PaymentResult> initiateMobileMoneyPayment({
    required String orderId,
    required String phoneNumber,
    required double amount,
    String? description,
  }) async {
    if (_selectedMethod == null) {
      return PaymentResult.paymentFailure(message: 'Aucune méthode de paiement sélectionnée');
    }

    if (_selectedMethod!.type != 'mobile_money') {
      return PaymentResult.paymentFailure(message: 'Méthode de paiement invalide');
    }

    _setProcessing(true);
    _setError(null);

    try {
      final provider = _selectedMethod!.config?['provider'] ?? '';
      
      if (kDebugMode) {
        // Mode simulation pour les tests
        final result = await PaymentService.simulateMobileMoneyPayment(
          orderId: orderId,
          provider: provider,
          phoneNumber: phoneNumber,
          amount: amount,
        );
        
        if (result.success && result.transactionId != null) {
          _currentTransaction = PaymentTransaction(
            id: result.transactionId!,
            orderId: orderId,
            paymentMethodId: _selectedMethod!.id,
            status: 'completed',
            amount: amount,
            currency: 'XOF',
            transactionReference: result.reference,
            createdAt: DateTime.now(),
            completedAt: DateTime.now(),
          );
        }
        
        return result;
      } else {
        // Mode production
        final result = await PaymentService.initiateMobileMoneyPayment(
          orderId: orderId,
          provider: provider,
          phoneNumber: phoneNumber,
          amount: amount,
          description: description,
        );

        if (result.success && result.transactionId != null) {
          _currentTransaction = PaymentTransaction(
            id: result.transactionId!,
            orderId: orderId,
            paymentMethodId: _selectedMethod!.id,
            status: 'processing',
            amount: amount,
            currency: 'XOF',
            transactionReference: result.reference,
            createdAt: DateTime.now(),
          );
        }

        return result;
      }
    } catch (e) {
      _setError('Erreur lors de l\'initiation du paiement: $e');
      return PaymentResult.paymentFailure(message: 'Erreur lors de l\'initiation du paiement: $e');
    } finally {
      _setProcessing(false);
    }
  }

  /// Vérifier le statut d'un paiement
  Future<PaymentTransaction?> checkPaymentStatus(String transactionId) async {
    try {
      final transaction = await PaymentService.checkPaymentStatus(transactionId);
      
      if (transaction != null) {
        _currentTransaction = transaction;
        
        // Ajouter à l'historique si le paiement est terminé
        if (transaction.isCompleted && !_paymentHistory.any((t) => t.id == transaction.id)) {
          _paymentHistory.insert(0, transaction);
        }
        
        notifyListeners();
      }
      
      return transaction;
    } catch (e) {
      _setError('Erreur lors de la vérification du statut: $e');
      return null;
    }
  }

  /// Confirmer un paiement
  Future<PaymentResult> confirmPayment({
    required String transactionId,
    String? verificationCode,
  }) async {
    _setProcessing(true);
    _setError(null);

    try {
      final result = await PaymentService.confirmPayment(
        transactionId: transactionId,
        verificationCode: verificationCode,
      );

      if (result.success) {
        // Mettre à jour la transaction courante
        if (_currentTransaction != null && _currentTransaction!.id == transactionId) {
          _currentTransaction = _currentTransaction!.copyWith(
            status: 'completed',
            completedAt: DateTime.now(),
          );
        }
      }

      return result;
    } catch (e) {
      _setError('Erreur lors de la confirmation du paiement: $e');
      return PaymentResult.paymentFailure(message: 'Erreur lors de la confirmation du paiement: $e');
    } finally {
      _setProcessing(false);
    }
  }

  /// Annuler un paiement
  Future<PaymentResult> cancelPayment(String transactionId) async {
    _setProcessing(true);
    _setError(null);

    try {
      final result = await PaymentService.cancelPayment(transactionId);

      if (result.success) {
        // Mettre à jour la transaction courante
        if (_currentTransaction != null && _currentTransaction!.id == transactionId) {
          _currentTransaction = _currentTransaction!.copyWith(
            status: 'cancelled',
            updatedAt: DateTime.now(),
          );
        }
      }

      return result;
    } catch (e) {
      _setError('Erreur lors de l\'annulation du paiement: $e');
      return PaymentResult.paymentFailure(message: 'Erreur lors de l\'annulation du paiement: $e');
    } finally {
      _setProcessing(false);
    }
  }

  /// Charger l'historique des paiements
  Future<void> loadPaymentHistory({int page = 1, int limit = 20}) async {
    _setLoading(true);
    _setError(null);

    try {
      final history = await PaymentService.getPaymentHistory(page: page, limit: limit);
      
      if (page == 1) {
        _paymentHistory = history;
      } else {
        _paymentHistory.addAll(history);
      }
    } catch (e) {
      _setError('Erreur lors du chargement de l\'historique: $e');
    } finally {
      _setLoading(false);
    }
  }

  /// Valider un numéro de téléphone
  bool validatePhoneNumber(String phoneNumber) {
    if (_selectedMethod == null || _selectedMethod!.type != 'mobile_money') {
      return false;
    }

    final provider = _selectedMethod!.config?['provider'] ?? '';
    return PaymentService.validatePhoneNumber(phoneNumber, provider);
  }

  /// Formater un numéro de téléphone
  String formatPhoneNumber(String phoneNumber) {
    return PaymentService.formatPhoneNumber(phoneNumber);
  }

  /// Réinitialiser l'état du paiement
  void resetPayment() {
    _selectedMethod = null;
    _currentTransaction = null;
    _setError(null);
    _setProcessing(false);
    notifyListeners();
  }

  /// Nettoyer les données
  void dispose() {
    super.dispose();
  }
}
