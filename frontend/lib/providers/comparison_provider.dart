import 'package:flutter/foundation.dart';
import '../services/comparison_service.dart';
import '../models/product_model.dart';

class ComparisonProvider with ChangeNotifier {
  final ComparisonService _comparisonService = ComparisonService();

  // Produits sélectionnés pour comparaison
  final List<ProductModel> _selectedProducts = [];
  
  // Résultat de la comparaison
  Map<String, dynamic>? _comparisonResult;
  List<Map<String, dynamic>> _history = [];
  
  bool _isLoading = false;
  bool _historyLoading = false;
  String? _error;
  String? _historyError;

  // Getters
  List<ProductModel> get selectedProducts => _selectedProducts;
  Map<String, dynamic>? get comparisonResult => _comparisonResult;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get selectedCount => _selectedProducts.length;
  bool get canCompare => _selectedProducts.length >= 2 && _selectedProducts.length <= 4;
  List<Map<String, dynamic>> get history => _history;
  bool get historyLoading => _historyLoading;
  String? get historyError => _historyError;

  /// Ajouter un produit à la sélection
  bool addProduct(ProductModel product) {
    if (_selectedProducts.length >= 4) {
      print('⚠️ [COMPARISON_PROVIDER] Maximum 4 produits');
      return false;
    }

    if (_selectedProducts.any((p) => p.id == product.id)) {
      print('⚠️ [COMPARISON_PROVIDER] Produit déjà sélectionné');
      return false;
    }

    _selectedProducts.add(product);
    print('✅ [COMPARISON_PROVIDER] Produit ajouté: ${product.name}');
    notifyListeners();
    return true;
  }

  /// Retirer un produit de la sélection
  void removeProduct(int productId) {
    _selectedProducts.removeWhere((p) => p.id == productId);
    print('✅ [COMPARISON_PROVIDER] Produit retiré #$productId');
    notifyListeners();
  }

  /// Basculer la sélection d'un produit
  bool toggleProduct(ProductModel product) {
    if (_selectedProducts.any((p) => p.id == product.id)) {
      removeProduct(product.id);
      return false;
    } else {
      return addProduct(product);
    }
  }

  /// Vérifier si un produit est sélectionné
  bool isSelected(int productId) {
    return _selectedProducts.any((p) => p.id == productId);
  }

  /// Comparer les produits sélectionnés
  Future<bool> compare() async {
    if (!canCompare) {
      _error = 'Sélectionnez entre 2 et 4 produits';
      notifyListeners();
      return false;
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final productIds = _selectedProducts.map((p) => p.id).toList();
      final response = await _comparisonService.compareProducts(productIds);

      if (response['success'] == true) {
        _comparisonResult = response['comparison'];
        print('✅ [COMPARISON_PROVIDER] Comparaison réussie');
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response['message'];
        print('❌ [COMPARISON_PROVIDER] Erreur: $_error');
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = e.toString();
      print('❌ [COMPARISON_PROVIDER] Exception: $_error');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Comparer directement en utilisant une liste d'IDs
  Future<bool> compareWithProductIds(List<int> productIds) async {
    if (productIds.length < 2 || productIds.length > 4) {
      _historyError = 'Sélection invalide dans l\'historique';
      notifyListeners();
      return false;
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _comparisonService.compareProducts(productIds);

      if (response['success'] == true) {
        _comparisonResult = response['comparison'];
        print('✅ [COMPARISON_PROVIDER] Comparaison historique réussie');
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response['message'];
        print('❌ [COMPARISON_PROVIDER] Erreur historique: $_error');
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = e.toString();
      print('❌ [COMPARISON_PROVIDER] Exception historique compare: $_error');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Charger l'historique des comparaisons
  Future<void> loadHistory() async {
    _historyLoading = true;
    _historyError = null;
    notifyListeners();

    try {
      final response = await _comparisonService.getComparisonHistory();

      if (response['success'] == true) {
        _history = List<Map<String, dynamic>>.from(response['history'] ?? []);
        print('✅ [COMPARISON_PROVIDER] Historique chargé (${_history.length})');
      } else {
        _historyError = response['message'];
        print('❌ [COMPARISON_PROVIDER] Erreur historique: $_historyError');
      }
    } catch (e) {
      _historyError = e.toString();
      print('❌ [COMPARISON_PROVIDER] Exception historique: $_historyError');
    }

    _historyLoading = false;
    notifyListeners();
  }

  /// Effacer l'historique en mémoire
  void clearHistoryCache() {
    _history = [];
    _historyError = null;
    _historyLoading = false;
    notifyListeners();
  }

  /// Effacer la sélection
  void clearSelection() {
    _selectedProducts.clear();
    _comparisonResult = null;
    print('✅ [COMPARISON_PROVIDER] Sélection effacée');
    notifyListeners();
  }

  /// Effacer l'erreur
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Réinitialiser
  void reset() {
    _selectedProducts.clear();
    _comparisonResult = null;
    _isLoading = false;
    _error = null;
    _history = [];
    _historyLoading = false;
    _historyError = null;
    notifyListeners();
  }
}

