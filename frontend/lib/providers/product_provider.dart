import 'dart:math';

import 'package:flutter/foundation.dart';
import '../models/product_model.dart';
import '../models/category_model.dart';
import '../models/banner_model.dart';
import '../services/product_service.dart';
import '../services/recent_products_service.dart';

class ProductProvider with ChangeNotifier {
  final ProductService _productService = ProductService();

  // État
  List<ProductModel> _featuredProducts = [];
  List<ProductModel> _trendingProducts = [];
  List<ProductModel> _newProducts = [];
  List<ProductModel> _bestOffers = [];
  List<ProductModel> _dealsProducts = [];
  List<ProductModel> _allProducts = [];
  List<CategoryModel> _categories = [];
  List<BannerModel> _banners = []; // ✅ Ajout bannières
  List<BannerModel> _homepageAds = []; // ✅ Publicités de la page d'accueil
  List<ProductModel> _forYouProducts = [];
  List<ProductModel> _recommendedProducts = [];
  List<ProductModel> _recentProducts = [];
  DateTime? _dealsCountdownEnd;
  Map<String, dynamic>? _dealsSettings;
  Map<String, dynamic> _promotions = {};
  Map<String, List<ProductModel>> _categoryProducts = {}; // Produits par catégorie
  List<Map<String, dynamic>> _categoryBanners = []; // Bannières de la catégorie actuelle

  bool _isLoading = false;
  String? _error;
  bool _personalizedLoading = false;

  // Cache système
  bool _homeDataLoaded = false;
  DateTime? _lastHomeDataUpdate;
  static const int _cacheExpirationMinutes = 5; // Durée de vie du cache

  // Getters
  List<ProductModel> get featuredProducts => _featuredProducts;
  List<ProductModel> get trendingProducts => _trendingProducts;
  List<ProductModel> get newProducts => _newProducts;
  List<ProductModel> get bestOffers => _bestOffers;
  List<ProductModel> get dealsProducts => _dealsProducts;
  List<ProductModel> get allProducts => _allProducts;
  List<CategoryModel> get categories => _categories;
  List<BannerModel> get banners => _banners; // ✅ Getter bannières
  List<BannerModel> get homepageAds => _homepageAds; // ✅ Getter publicités page d'accueil
  List<ProductModel> get forYouProducts => _forYouProducts;
  List<ProductModel> get recommendedProducts => _recommendedProducts;
  List<ProductModel> get recentProducts => _recentProducts;
  DateTime? get dealsCountdownEnd => _dealsCountdownEnd;
  Map<String, dynamic>? get dealsSettings => _dealsSettings;
  Map<String, dynamic> get promotions => _promotions;
  Map<String, List<ProductModel>> get categoryProducts => _categoryProducts;
  List<Map<String, dynamic>> get categoryBanners => _categoryBanners;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasData => _homeDataLoaded;
  bool get personalizedLoading => _personalizedLoading;

  Future<void> loadHomeData({bool forceRefresh = false}) async {
    // Vérifier si les données sont en cache et toujours valides
    if (!forceRefresh && _homeDataLoaded && _lastHomeDataUpdate != null) {
      final cacheAge = DateTime.now().difference(_lastHomeDataUpdate!);
      if (cacheAge.inMinutes < _cacheExpirationMinutes) {
        print('✅ [PRODUCT_PROVIDER] Données en cache (${cacheAge.inSeconds}s)');
        return; // Utiliser les données en cache
      }
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [PRODUCT_PROVIDER] Chargement des données depuis le serveur');
      final response = await _productService.getHomeData();

      print('📊 [PRODUCT_PROVIDER] Réponse reçue: ${response['success']}');
      print('📊 [PRODUCT_PROVIDER] Clés disponibles: ${response.keys}');

      if (response['success']) {
        // Les données sont dans response['data']
        final data = response['data'] ?? response;

        print('📦 [PRODUCT_PROVIDER] Clés dans data: ${data.keys}');

        final dealsData = data['deals'];
        if (dealsData is Map) {
          _dealsProducts = (dealsData['products'] as List? ?? [])
              .map((e) => ProductModel.fromJson(e))
              .toList();

          final countdownValue = dealsData['countdown_end'];
          if (countdownValue is num) {
            _dealsCountdownEnd = DateTime.fromMillisecondsSinceEpoch(countdownValue.toInt());
          } else if (countdownValue is String) {
            final parsed = int.tryParse(countdownValue);
            _dealsCountdownEnd = parsed != null
                ? DateTime.fromMillisecondsSinceEpoch(parsed)
                : null;
          } else {
            _dealsCountdownEnd = null;
          }

          _dealsSettings = dealsData['settings'] is Map
              ? Map<String, dynamic>.from(dealsData['settings'])
              : null;
        } else {
          _dealsProducts = [];
          _dealsCountdownEnd = null;
          _dealsSettings = null;
        }

        final promotionsData = data['promotions'];
        if (promotionsData is Map) {
          _promotions = Map<String, dynamic>.from(promotionsData);
        } else {
          _promotions = {};
        }

        // Parser avec gestion des valeurs null
        _featuredProducts = (data['featured_products'] as List? ?? [])
            .map((e) => ProductModel.fromJson(e))
            .toList();

        _trendingProducts = (data['trending_products'] as List? ?? [])
            .map((e) => ProductModel.fromJson(e))
            .toList();

        _newProducts = (data['new_products'] as List? ?? [])
            .map((e) => ProductModel.fromJson(e))
            .toList();

        _bestOffers = (data['best_offers'] as List? ?? [])
            .map((e) => ProductModel.fromJson(e))
            .toList();

        // Parser les catégories avec gestion d'erreur améliorée
        try {
          final categoriesData = data['categories'];
          if (categoriesData is List) {
            _categories = categoriesData
                .map((e) {
                  try {
                    return CategoryModel.fromJson(e);
                  } catch (e) {
                    print('⚠️ [PRODUCT_PROVIDER] Erreur parsing catégorie: $e');
                    return null;
                  }
                })
                .whereType<CategoryModel>() // Filtrer les nulls
                .toList();
            print('✅ [PRODUCT_PROVIDER] ${_categories.length} catégories parsées');
          } else {
            print('⚠️ [PRODUCT_PROVIDER] categories n\'est pas une List, type: ${categoriesData?.runtimeType}');
            _categories = [];
          }
        } catch (e) {
          print('❌ [PRODUCT_PROVIDER] Erreur parsing catégories: $e');
          _categories = [];
        }

        // ✅ Parser les bannières avec gestion d'erreur
        try {
          final bannersData = data['banners'];
          print('📊 [PRODUCT_PROVIDER] Bannières reçues: ${bannersData?.runtimeType}');
          
          if (bannersData is List) {
            print('📊 [PRODUCT_PROVIDER] Nombre de bannières: ${bannersData.length}');
            _banners = bannersData
                .map((e) {
                  try {
                    if (e is Map) {
                      // ✅ Convertir en Map<String, dynamic>
                      final bannerMap = Map<String, dynamic>.from(e);
                      return BannerModel.fromJson(bannerMap);
                    }
                    return null;
                  } catch (err) {
                    print('❌ [PRODUCT_PROVIDER] Erreur parsing bannière: $err');
                    print('   Données: $e');
                    return null;
                  }
                })
                .whereType<BannerModel>()
                .toList();
            print('✅ [PRODUCT_PROVIDER] ${_banners.length} bannières parsées avec succès');
          } else {
            print('⚠️ [PRODUCT_PROVIDER] banners n\'est pas une List, type: ${bannersData?.runtimeType}');
            _banners = [];
          }
        } catch (e) {
          print('❌ [PRODUCT_PROVIDER] Erreur parsing bannières: $e');
          _banners = [];
        }
        
        // ✅ Parser les publicités de la page d'accueil
        _homepageAds = (data['homepage_ads'] as List? ?? [])
            .map((e) => BannerModel.fromJson(e))
            .toList();

        // ✅ Parser les produits par catégorie
        final categoryProductsData = data['category_products'];
        if (categoryProductsData is Map) {
          _categoryProducts = {};
          categoryProductsData.forEach((key, value) {
            if (value is Map && value['products'] is List) {
              _categoryProducts[key] = (value['products'] as List)
                  .map((e) => ProductModel.fromJson(e))
                  .toList();
            }
          });
        } else {
          _categoryProducts = {};
        }

        // Marquer les données comme chargées et mettre à jour le timestamp
        _homeDataLoaded = true;
        _lastHomeDataUpdate = DateTime.now();

        print('✅ [PRODUCT_PROVIDER] Données chargées:');
        print('   - Deals du jour: ${_dealsProducts.length}');
        print('   - Produits vedette: ${_featuredProducts.length}');
        print('   - Produits tendance: ${_trendingProducts.length}');
        print('   - Nouveautés: ${_newProducts.length}');
        print('   - Meilleures offres: ${_bestOffers.length}');
        print('   - Catégories: ${_categories.length}');
        print('   - Bannières: ${_banners.length}'); // ✅ Log bannières
        print('   - Sections par catégorie: ${_categoryProducts.keys.length}'); // ✅ Log produits par catégorie
      } else {
        _error = response['message'];
        print('❌ [PRODUCT_PROVIDER] Erreur API: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('❌ [PRODUCT_PROVIDER] Erreur: $_error');
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> loadPersonalizedSections({bool forceRefresh = false}) async {
    if (_personalizedLoading && !forceRefresh) return;

    _personalizedLoading = true;
    notifyListeners();

    try {
      final recent = await RecentProductsService.getRecentProducts();
      _recentProducts = recent;

      final pool = _dedupeProducts([
        ..._bestOffers,
        ..._trendingProducts,
        ..._newProducts,
        ..._featuredProducts,
      ]);

      final recentIds = _recentProducts.map((p) => p.id).toSet();
      final shuffledPool = List<ProductModel>.from(pool)..shuffle(Random());

      _forYouProducts = shuffledPool
          .where((p) => !recentIds.contains(p.id))
          .take(8)
          .toList();

      if (_forYouProducts.length < 4) {
        _forYouProducts = shuffledPool.take(8).toList();
      }

      final recommendedPool = List<ProductModel>.from(pool)
        ..sort((a, b) => (b.discountPercentage ?? 0)
            .compareTo(a.discountPercentage ?? 0));
      _recommendedProducts = recommendedPool.take(8).toList();
    } catch (e) {
      print('❌ [PRODUCT_PROVIDER] Erreur recommandations: $e');
    } finally {
      _personalizedLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadCategories() async {
    // Ne pas bloquer l'UI si les catégories sont déjà chargées
    if (_categories.isNotEmpty && !_isLoading) {
      print('✅ [PRODUCT_PROVIDER] Catégories déjà chargées (${_categories.length})');
      return;
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [PRODUCT_PROVIDER] Chargement des catégories...');
      final response = await _productService.getCategories();

      if (response['success']) {
        // L'API retourne 'data' au lieu de 'categories'
        final categoriesData = response['data'] ?? response['categories'];
        if (categoriesData is List) {
          _categories = categoriesData
              .map((e) {
                try {
                  return CategoryModel.fromJson(e);
                } catch (e) {
                  print('⚠️ [PRODUCT_PROVIDER] Erreur parsing catégorie: $e');
                  return null;
                }
              })
              .whereType<CategoryModel>() // Filtrer les nulls
              .toList();
          print('✅ [PRODUCT_PROVIDER] ${_categories.length} catégories chargées');
        } else {
          print('⚠️ [PRODUCT_PROVIDER] categories n\'est pas une List');
          _categories = [];
        }
      } else {
        _error = response['message'];
        print('❌ [PRODUCT_PROVIDER] Erreur API catégories: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('❌ [PRODUCT_PROVIDER] Exception chargement catégories: $_error');
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> loadProducts({
    int? categoryId,
    String? search,
    String? sortBy,
    String? specialCategory,
    double? minPrice,          // ✅ Nouveau
    double? maxPrice,          // ✅ Nouveau
    double? minRating,         // ✅ Nouveau
    bool? inStock,             // ✅ Nouveau
    List<String>? brands,      // ✅ Nouveau
    bool? officialStoresBestOffers, // ✅ Nouveau
    int page = 1,
    int limit = 20,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [PRODUCT_PROVIDER] Chargement produits...');

      final response = await _productService.getProducts(
        categoryId: categoryId,
        search: search,
        sortBy: sortBy,
        specialCategory: specialCategory,
        minPrice: minPrice,        // ✅ Nouveau
        maxPrice: maxPrice,        // ✅ Nouveau
        minRating: minRating,      // ✅ Nouveau
        inStock: inStock,          // ✅ Nouveau
        brands: brands,            // ✅ Nouveau
        officialStoresBestOffers: officialStoresBestOffers, // ✅ Nouveau
        page: page,
        limit: limit,
      );

      if (response['success'] == true) {
        // L'API mobile retourne 'data' au lieu de 'products'
        final productsData = response['data'] ?? response['products'];

        print(
          '📊 [PRODUCT_PROVIDER] Type de products: ${productsData?.runtimeType ?? "null"}',
        );
        print(
          '📊 [PRODUCT_PROVIDER] Nombre brut: ${productsData is List ? productsData.length : "pas une liste"}',
        );

        if (productsData is List) {
          final List<ProductModel> parsedProducts = [];

          for (var i = 0; i < productsData.length; i++) {
            try {
              final productData = productsData[i];

              if (productData is Map<String, dynamic>) {
                parsedProducts.add(ProductModel.fromJson(productData));
              } else {
                print(
                  '⚠️ [PRODUCT_PROVIDER] Produit $i n\'est pas une Map: ${productData.runtimeType}',
                );
              }
            } catch (e) {
              print('❌ [PRODUCT_PROVIDER] Erreur parsing produit $i: $e');
            }
          }

          if (page == 1) {
            _allProducts = parsedProducts;
          } else {
            _allProducts.addAll(parsedProducts);
          }

          print(
            '✅ [PRODUCT_PROVIDER] ${_allProducts.length} produits dans _allProducts',
          );

          // ✅ Extraire les bannières de catégorie si disponibles
          if (response['category_banners'] != null && response['category_banners'] is List) {
            _categoryBanners = List<Map<String, dynamic>>.from(response['category_banners']);
            print('✅ [PRODUCT_PROVIDER] ${_categoryBanners.length} bannières de catégorie chargées');
          } else {
            _categoryBanners = [];
          }
        } else {
          print('❌ [PRODUCT_PROVIDER] products n\'est pas une List!');
          _error = 'Format de données invalide';
        }
      } else {
        _error = response['message'] ?? 'Erreur inconnue';
        print('❌ [PRODUCT_PROVIDER] Erreur: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('💥 [PRODUCT_PROVIDER] Exception: $_error');
    }

    _isLoading = false;
    notifyListeners();
  }

  List<ProductModel> _dedupeProducts(List<ProductModel> products) {
    final Map<int, ProductModel> unique = {};
    for (final product in products) {
      unique[product.id] = product;
    }
    return unique.values.toList();
  }

  Future<ProductModel?> getProductDetails(int productId) async {
    try {
      final response = await _productService.getProductDetails(productId);

      if (response['success'] && response['product'] != null) {
        return ProductModel.fromJson(response['product']);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
