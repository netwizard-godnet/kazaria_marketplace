import 'dart:io';
import 'package:flutter/material.dart';
import '../services/seller_service.dart';
import '../models/product_model.dart';
import '../models/order_model.dart';

class SellerProvider extends ChangeNotifier {
  final SellerService _sellerService = SellerService();
  
  // État
  bool _isLoading = false;
  String? _error;
  
  // Stats
  Map<String, dynamic>? _stats;
  
  // Produits
  List<ProductModel> _products = [];
  int _productsCurrentPage = 1;
  int _productsLastPage = 1;
  bool _hasMoreProducts = true;
  
  // Commandes
  List<OrderModel> _orders = [];
  int _ordersCurrentPage = 1;
  int _ordersLastPage = 1;
  bool _hasMoreOrders = true;
  
  // Commandes récentes
  List<dynamic> _recentOrders = [];
  
  // Infos boutique
  Map<String, dynamic>? _storeInfo;

  // Getters
  bool get isLoading => _isLoading;
  String? get error => _error;
  Map<String, dynamic>? get stats => _stats;
  List<ProductModel> get products => _products;
  bool get hasMoreProducts => _hasMoreProducts;
  List<OrderModel> get orders => _orders;
  bool get hasMoreOrders => _hasMoreOrders;
  List<dynamic> get recentOrders => _recentOrders;
  Map<String, dynamic>? get storeInfo => _storeInfo;

  /// Charger les statistiques
  Future<void> loadStats() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _sellerService.getStats();
      
      if (response['success']) {
        _stats = response['stats'];
      } else {
        _error = response['message'] ?? 'Erreur lors du chargement des statistiques';
      }
    } catch (e) {
      _error = 'Erreur: $e';
      print('❌ [SELLER_PROVIDER] Erreur stats: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Charger les produits
  Future<void> loadProducts({
    bool refresh = false,
    String? search,
    String? status,
  }) async {
    if (refresh) {
      _productsCurrentPage = 1;
      _products.clear();
      _hasMoreProducts = true;
    }

    if (!_hasMoreProducts && !refresh) return;

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [SELLER_PROVIDER] Chargement produits - Page: $_productsCurrentPage, Recherche: "$search", Statut: "$status"');
      
      final response = await _sellerService.getProducts(
        page: _productsCurrentPage,
        search: search,
        status: status,
      );
      
      if (response['success']) {
        final List<dynamic> productsData = response['products'] ?? [];
        print('✅ [SELLER_PROVIDER] ${productsData.length} produits reçus');
        
        final newProducts = productsData
            .map((json) => ProductModel.fromJson(json))
            .toList();

        if (refresh) {
          _products = newProducts;
        } else {
          _products.addAll(newProducts);
        }

        _productsCurrentPage = response['current_page'] ?? 1;
        _productsLastPage = response['last_page'] ?? 1;
        _hasMoreProducts = _productsCurrentPage < _productsLastPage;
      } else {
        _error = response['message'] ?? 'Erreur lors du chargement des produits';
      }
    } catch (e) {
      _error = 'Erreur: $e';
      print('❌ [SELLER_PROVIDER] Erreur produits: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Charger plus de produits
  Future<void> loadMoreProducts({String? search, String? status}) async {
    if (_hasMoreProducts && !_isLoading) {
      _productsCurrentPage++;
      await loadProducts(search: search, status: status);
    }
  }

  /// Créer un produit
  Future<Map<String, dynamic>> createProduct({
    required String name,
    required String description,
    required double price,
    required int quantity,
    required int categoryId,
    int? subcategoryId,
    String? brand,
    String? model,
    String? warranty,
    String? tags,
    double? promoPrice,
    double? discount,
  }) async {
    try {
      final response = await _sellerService.createProduct(
        name: name,
        description: description,
        price: price,
        quantity: quantity,
        categoryId: categoryId,
        subcategoryId: subcategoryId,
        brand: brand,
        model: model,
        warranty: warranty,
        tags: tags,
        promoPrice: promoPrice,
        discount: discount,
      );

      if (response['success']) {
        // Recharger les produits
        await loadProducts();
        // Recharger les statistiques
        await loadStats();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur création produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour un produit
  Future<Map<String, dynamic>> updateProduct({
    required int productId,
    required String name,
    required String description,
    required double price,
    required int quantity,
    String? brand,
    String? model,
    String? warranty,
    String? tags,
    double? promoPrice,
    double? discount,
  }) async {
    try {
      final response = await _sellerService.updateProduct(
        productId: productId,
        name: name,
        description: description,
        price: price,
        quantity: quantity,
        brand: brand,
        model: model,
        warranty: warranty,
        tags: tags,
        promoPrice: promoPrice,
        discount: discount,
      );

      if (response['success']) {
        // Recharger les produits
        await loadProducts();
        // Recharger les statistiques
        await loadStats();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur mise à jour produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer un produit par ID
  Future<Map<String, dynamic>> getProduct(int productId) async {
    try {
      final response = await _sellerService.getProduct(productId);
      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur récupération produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Supprimer un produit
  Future<Map<String, dynamic>> deleteProduct(int productId) async {
    try {
      final response = await _sellerService.deleteProduct(productId);

      if (response['success']) {
        // Recharger les produits
        await loadProducts();
        // Recharger les statistiques
        await loadStats();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur suppression produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Charger les commandes
  Future<void> loadOrders({
bool refresh = false,
    String? status,
  }) async {
    if (refresh) {
      _ordersCurrentPage = 1;
      _orders.clear();
      _hasMoreOrders = true;
    }

    if (!_hasMoreOrders && !refresh) return;

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _sellerService.getOrders(
        page: _ordersCurrentPage,
        status: status,
      );
      
      if (response['success']) {
        // ✅ Gérer le cas où orders peut être une List ou autre chose
        dynamic ordersRaw = response['orders'];
        List<dynamic> ordersData = [];
        
        if (ordersRaw is List) {
          ordersData = ordersRaw;
        } else if (ordersRaw is Map) {
          // Si c'est un Map, essayer d'extraire les valeurs
          ordersData = ordersRaw.values.toList();
          print('⚠️ [SELLER_PROVIDER] Orders est un Map, conversion en List');
        } else if (ordersRaw != null) {
          // Essayer de convertir en List
          try {
            ordersData = [ordersRaw];
          } catch (e) {
            print('❌ [SELLER_PROVIDER] Impossible de convertir orders en List: $e');
            ordersData = [];
          }
        }
        
        print('📊 [SELLER_PROVIDER] Nombre de commandes reçues: ${ordersData.length}');
        
        final newOrders = <OrderModel>[];
        for (var json in ordersData) {
          try {
            if (json is Map) {
              // ✅ Convertir en Map<String, dynamic>
              final orderMap = Map<String, dynamic>.from(json);
              newOrders.add(OrderModel.fromJson(orderMap));
            }
          } catch (e) {
            print('❌ [SELLER_PROVIDER] Erreur parsing commande: $e');
            print('   Données: $json');
          }
        }
        
        print('✅ [SELLER_PROVIDER] Commandes parsées avec succès: ${newOrders.length}');
      
      // ✅ Debug: Afficher les IDs des commandes parsées
      if (newOrders.isNotEmpty) {
        print('📋 [SELLER_PROVIDER] IDs des commandes parsées: ${newOrders.map((o) => o.id).toList()}');
        print('📋 [SELLER_PROVIDER] Numéros des commandes: ${newOrders.map((o) => o.orderNumber).toList()}');
      }

        if (refresh) {
          _orders = newOrders;
        } else {
          _orders.addAll(newOrders);
        }
        
        print('📦 [SELLER_PROVIDER] Total commandes dans la liste: ${_orders.length}');

        // ✅ Extraire la pagination correctement
        final pagination = response['pagination'];
        if (pagination is Map) {
          _ordersCurrentPage = pagination['current_page'] ?? 1;
          _ordersLastPage = pagination['last_page'] ?? 1;
        } else {
          // Fallback si pas de pagination
          _ordersCurrentPage = 1;
          _ordersLastPage = 1;
        }
        
        _hasMoreOrders = _ordersCurrentPage < _ordersLastPage;
        print('📄 [SELLER_PROVIDER] Pagination: page $_ordersCurrentPage/$_ordersLastPage, hasMore: $_hasMoreOrders');
      } else {
        _error = response['message'] ?? 'Erreur lors du chargement des commandes';
      }
    } catch (e) {
      _error = 'Erreur: $e';
      print('❌ [SELLER_PROVIDER] Erreur commandes: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Charger plus de commandes
  Future<void> loadMoreOrders({String? status}) async {
    if (_hasMoreOrders && !_isLoading) {
      _ordersCurrentPage++;
      await loadOrders(status: status);
    }
  }

  /// Charger les commandes récentes (pour le dashboard)
  Future<void> loadRecentOrders({int limit = 5}) async {
    try {
      print('🔄 [SELLER_PROVIDER] Chargement commandes récentes...');
      final response = await _sellerService.getRecentOrders(limit: limit);
      
      if (response['success']) {
        _recentOrders = response['orders'] ?? [];
        print('✅ [SELLER_PROVIDER] Commandes récentes chargées: ${_recentOrders.length}');
      } else {
        _recentOrders = [];
        print('⚠️ [SELLER_PROVIDER] Aucune commande récente');
      }
      
      notifyListeners();
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur commandes récentes: $e');
      _recentOrders = [];
      notifyListeners();
    }
  }


  /// Charger les informations de la boutique
  Future<void> loadStoreInfo() async {
    print('🔄 [SELLER_PROVIDER] Début chargement infos boutique');
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _sellerService.getStoreInfo();
      print('📊 [SELLER_PROVIDER] Réponse API complète: $response');
      print('📊 [SELLER_PROVIDER] Type de réponse: ${response.runtimeType}');
      
      if (response['success'] == true) {
        _storeInfo = response['store'];
        print('✅ [SELLER_PROVIDER] Store info chargée: $_storeInfo');
        print('✅ [SELLER_PROVIDER] Store name: ${_storeInfo?['name']}');
        print('✅ [SELLER_PROVIDER] Store email: ${_storeInfo?['email']}');
      } else {
        _error = response['message'] ?? 'Erreur lors du chargement de la boutique';
        print('❌ [SELLER_PROVIDER] Erreur API: $_error');
        print('❌ [SELLER_PROVIDER] Response success: ${response['success']}');
      }
    } catch (e) {
      _error = 'Erreur: $e';
      print('❌ [SELLER_PROVIDER] Exception: $e');
      print('❌ [SELLER_PROVIDER] Stack trace: ${StackTrace.current}');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Obtenir les informations de la boutique
  Future<Map<String, dynamic>> getStoreInfo() async {
    try {
      final response = await _sellerService.getStoreInfo();
      
      if (response['success']) {
        _storeInfo = response['store'];
        notifyListeners();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur récupération info: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour les informations de la boutique
  Future<Map<String, dynamic>> updateStoreInfo({
    String? name,
    String? description,
    String? phone,
    String? email,
    String? address,
    String? city,
    String? facebook,
    String? instagram,
    String? twitter,
    String? website,
  }) async {
    try {
      final response = await _sellerService.updateStoreInfo(
        name: name,
        description: description,
        phone: phone,
        email: email,
        address: address,
        city: city,
        facebook: facebook,
        instagram: instagram,
        twitter: twitter,
        website: website,
      );
      
      if (response['success']) {
        await loadStoreInfo();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur mise à jour boutique: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Upload du logo de la boutique
  Future<Map<String, dynamic>> uploadLogo(File logoFile) async {
    try {
      final response = await _sellerService.uploadLogo(logoFile);
      
      if (response['success']) {
        await loadStoreInfo();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur upload logo: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Upload de la bannière de la boutique
  Future<Map<String, dynamic>> uploadBanner(File bannerFile) async {
    try {
      final response = await _sellerService.uploadBanner(bannerFile);
      
      if (response['success']) {
        await loadStoreInfo();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur upload bannière: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Upload d'un document
  Future<Map<String, dynamic>> uploadDocument(File documentFile, String type) async {
    try {
      final response = await _sellerService.uploadDocument(documentFile, type);
      
      if (response['success']) {
        await loadStoreInfo();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur upload document: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour l'image principale d'un produit
  Future<Map<String, dynamic>> updateProductMainImage(int productId, File image) async {
    try {
      final response = await _sellerService.updateProductMainImage(productId, image);
      
      if (response['success']) {
        await loadProducts();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur mise à jour image principale: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Upload d'images supplémentaires pour un produit
  Future<Map<String, dynamic>> uploadProductImages(int productId, List<File> images) async {
    try {
      final response = await _sellerService.uploadProductImages(productId, images);
      
      if (response['success']) {
        await loadProducts();
      }

      return response;
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur upload images produit: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour le statut d'une commande
  /// Note: Utilise orderNumber (String) au lieu de orderId (int)
  Future<bool> updateOrderStatus(String orderNumber, String newStatus) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      final response = await _sellerService.updateOrderStatus(orderNumber, newStatus);
      
      if (response['success']) {
        print('✅ [SELLER_PROVIDER] Statut mis à jour, rechargement des données...');
        
        // Recharger les commandes
        await loadOrders(refresh: true);
        
        // Recharger les commandes récentes pour le dashboard
        await loadRecentOrders();
        
        // Recharger les statistiques
        await loadStats();
        
        print('✅ [SELLER_PROVIDER] Toutes les données ont été rechargées');
        return true;
      } else {
        _error = response['message'] ?? 'Erreur lors de la mise à jour du statut';
        return false;
      }
    } catch (e) {
      print('❌ [SELLER_PROVIDER] Erreur mise à jour statut commande: $e');
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Réinitialiser l'état
  void reset() {
    _isLoading = false;
    _error = null;
    _stats = null;
    _products.clear();
    _productsCurrentPage = 1;
    _productsLastPage = 1;
    _hasMoreProducts = true;
    _orders.clear();
    _ordersCurrentPage = 1;
    _ordersLastPage = 1;
    _hasMoreOrders = true;
    _storeInfo = null;
    notifyListeners();
  }
}
