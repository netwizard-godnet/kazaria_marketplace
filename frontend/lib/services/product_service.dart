import '../config/api_config.dart';
import 'api_service.dart';

class ProductService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getHomeData() async {
    try {
      return await _apiService.get(ApiConfig.mobileHomeData);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> getCategories() async {
    try {
      return await _apiService.get(ApiConfig.mobileCategories);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> getProducts({
    int? categoryId,
    String? search,
    String? sortBy,
    String sortOrder = 'desc',
    String? specialCategory,
    double? minPrice, // ✅ Nouveau
    double? maxPrice, // ✅ Nouveau
    double? minRating, // ✅ Nouveau
    bool? inStock, // ✅ Nouveau
    List<String>? brands, // ✅ Nouveau
    bool? officialStoresBestOffers, // ✅ Nouveau
    bool officialStoresOnly = false,
    int page = 1,
    int limit = 20,
  }) async {
    try {
      Map<String, dynamic> queryParams = {
        'page': page.toString(),
        'limit': limit.toString(),
      };

      if (categoryId != null) {
        queryParams['category_id'] = categoryId.toString();
      }
      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
        print('🔍 [PRODUCT_SERVICE] Recherche: "$search"');
      }
      if (sortBy != null) {
        queryParams['sort_by'] = sortBy;
      }
      queryParams['sort_order'] = sortOrder;
      if (specialCategory != null) {
        queryParams['special_category'] = specialCategory;
        print('🏷️ [PRODUCT_SERVICE] Catégorie spéciale: "$specialCategory"');
      }
      
      // ✅ Nouveaux filtres
      if (minPrice != null) {
        queryParams['min_price'] = minPrice.toString();
      }
      if (maxPrice != null) {
        queryParams['max_price'] = maxPrice.toString();
      }
      if (minRating != null) {
        queryParams['min_rating'] = minRating.toString();
      }
      if (inStock != null) {
        queryParams['in_stock'] = inStock.toString();
      }
      if (brands != null && brands.isNotEmpty) {
        // Envoyer les marques séparées par des virgules
        queryParams['brands'] = brands.join(',');
        print('🏷️ [PRODUCT_SERVICE] Marques: ${brands.join(", ")}');
      }
      if (officialStoresBestOffers == true) {
        queryParams['official_stores_best_offers'] = '1';
        print('🏪 [PRODUCT_SERVICE] Filtre: Meilleures offres boutiques officielles');
      }
      if (officialStoresOnly == true) {
        queryParams['official_only'] = '1';
      }

      final queryString = queryParams.entries
          .map((e) => '${e.key}=${Uri.encodeComponent(e.value)}')
          .join('&');

      final url = '${ApiConfig.mobileProducts}?$queryString';
      print('📡 [PRODUCT_SERVICE] URL: $url');

      final result = await _apiService.get(url);

      if (result['success'] == true) {
        // L'API mobile retourne 'data' au lieu de 'products'
        final productsList = result['data'] ?? result['products'];
        final productCount = productsList != null && productsList is List
            ? productsList.length
            : 0;
        print('✅ [PRODUCT_SERVICE] Résultats: $productCount produits');
      } else {
        print('❌ [PRODUCT_SERVICE] Erreur: ${result['message']}');
      }

      return result;
    } catch (e) {
      print('💥 [PRODUCT_SERVICE] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> getProductDetails(int productId) async {
    try {
      return await _apiService.get(
        '${ApiConfig.mobileProductDetails}/$productId',
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> searchProducts(String query) async {
    try {
      return await getProducts(search: query);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// ✅ Récupérer les produits similaires avec algorithme intelligent
  Future<Map<String, dynamic>> getSimilarProducts(int productId) async {
    try {
      final url = '${ApiConfig.baseUrl}/mobile/products/$productId/similar';
      print('📡 [PRODUCT_SERVICE] URL produits similaires: $url');

      final result = await _apiService.get(url);

      if (result['success'] == true) {
        // L'API mobile retourne 'data' au lieu de 'products'
        final productsList = result['data'] ?? result['products'];
        final productCount = productsList != null && productsList is List
            ? productsList.length
            : 0;
        print('✅ [PRODUCT_SERVICE] Produits similaires: $productCount');
      } else {
        print('❌ [PRODUCT_SERVICE] Erreur: ${result['message']}');
      }

      return result;
    } catch (e) {
      print('💥 [PRODUCT_SERVICE] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}
