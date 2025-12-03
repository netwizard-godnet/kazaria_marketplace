import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import 'storage_service.dart';

class StoreService {
  static final StoreService _instance = StoreService._internal();
  factory StoreService() => _instance;
  StoreService._internal();

  final StorageService _storageService = StorageService();

  /// Headers avec token d'authentification
  Future<Map<String, String>> _getHeaders() async {
    final token = await _storageService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  Map<String, dynamic>? _asMap(dynamic data) {
    if (data is Map<String, dynamic>) {
      return data;
    }
    return null;
  }

  String _extractErrorMessage(Map<String, dynamic>? data, String fallback) {
    if (data != null) {
      final dynamic message = data['message'];
      if (message is String && message.trim().isNotEmpty) {
        return message;
      }
    }
    return fallback;
  }

  /// Récupérer toutes les boutiques
  Future<Map<String, dynamic>> getAllStores({
    int page = 1,
    int perPage = 20,
    String? search,
    String? category,
    String? city,
    bool verifiedOnly = false,
  }) async {
    try {
      final headers = await _getHeaders();

      // Construire les paramètres de requête
      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
      };

      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }
      if (category != null && category.isNotEmpty) {
        queryParams['category'] = category;
      }
      if (city != null && city.isNotEmpty) {
        queryParams['city'] = city;
      }
      if (verifiedOnly) {
        queryParams['verified_only'] = '1';
      }

      final uri = Uri.parse(
        ApiConfig.mobileStores,
      ).replace(queryParameters: queryParams);

      print('🔄 [STORE] Récupération des boutiques: $uri');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final dataMap = _asMap(data);

      print('📥 [STORE] Réponse API: ${response.statusCode}');
      print('📊 [STORE] Données: ${dataMap?.keys ?? []}');

      if (response.statusCode == 200) {
        if (dataMap == null) {
          return {
            'success': false,
            'message': 'Réponse invalide du serveur',
            'stores': [],
            'pagination': {},
          };
        }
        return {
          'success': true,
          'stores': dataMap['stores'] ?? dataMap['data'] ?? [],
          'pagination': dataMap['pagination'] ?? {},
        };
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            dataMap,
            'Erreur lors de la récupération des boutiques',
          ),
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer les détails d'une boutique
  Future<Map<String, dynamic>> getStoreDetails(int storeId) async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse('${ApiConfig.mobileStoreDetails}/$storeId');

      print('🔄 [STORE] Récupération détails boutique: $storeId');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final errorMap = _asMap(data);

      print('📥 [STORE] Réponse détails: ${response.statusCode}');

      if (response.statusCode == 200) {
        // Vérifier le type de données reçues
        dynamic storeData;

        if (data is Map<String, dynamic>) {
          // Si data est un objet, chercher 'store', 'data' ou utiliser data directement
          storeData = data['store'] ?? data['data'];

          // Si toujours null et que data contient 'id', c'est probablement le store directement
          if (storeData == null && data.containsKey('id')) {
            storeData = data;
          }
        } else if (data is List && data.isNotEmpty) {
          // Si data est une liste, prendre le premier élément
          storeData = data[0];
        }

        // Si on n'a toujours pas de données valides
        if (storeData == null || (storeData is! Map<String, dynamic>)) {
          print('❌ [STORE] Format de données invalide: ${data.runtimeType}');
          return {
            'success': false,
            'message': 'Format de données invalide reçu du serveur',
          };
        }

        return {'success': true, 'store': storeData};
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            errorMap,
            'Erreur lors de la récupération des détails',
          ),
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception détails: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer les produits d'une boutique
  Future<Map<String, dynamic>> getStoreProducts(
    int storeId, {
    int page = 1,
    int perPage = 20,
    String? category,
    String? search,
    String sortBy = 'created_at',
    String sortOrder = 'desc',
    double? minPrice,
    double? maxPrice,
    bool inStockOnly = false,
  }) async {
    try {
      final headers = await _getHeaders();

      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
        'sort_by': sortBy,
        'sort_order': sortOrder,
      };

      if (category != null && category.isNotEmpty) {
        queryParams['category'] = category;
      }
      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }
      if (minPrice != null) {
        queryParams['min_price'] = minPrice.toInt().toString();
      }
      if (maxPrice != null) {
        queryParams['max_price'] = maxPrice.toInt().toString();
      }
      if (inStockOnly) {
        queryParams['in_stock'] = '1';
      }

      final uri = Uri.parse(
        '${ApiConfig.mobileStores}/$storeId/products',
      ).replace(queryParameters: queryParams);

      print('🔄 [STORE] Récupération produits boutique: $storeId');
      print('📡 [STORE] URL complète: $uri');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final dataMap = _asMap(data);

      print('📥 [STORE] Réponse produits: ${response.statusCode}');
      print('📊 [STORE] Type de data: ${data.runtimeType}');
      print(
        '📊 [STORE] Contenu data: ${dataMap?.keys.toList() ?? "PAS UN MAP"}',
      );

      if (response.statusCode == 200) {
        if (dataMap == null) {
          return {
            'success': false,
            'message': 'Réponse invalide du serveur',
            'products': [],
            'pagination': {},
          };
        }
        final productsRaw = dataMap['products'] ?? dataMap['data'] ?? [];
        print('📊 [STORE] Type de products: ${productsRaw.runtimeType}');
        print(
          '📊 [STORE] Nombre de produits: ${productsRaw is List ? productsRaw.length : "PAS UNE LISTE"}',
        );

        return {
          'success': true,
          'products': productsRaw,
          'pagination': dataMap['pagination'] ?? {},
        };
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            dataMap,
            'Erreur lors de la récupération des produits',
          ),
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception produits: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer les boutiques populaires
  Future<Map<String, dynamic>> getPopularStores({int limit = 10}) async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse(
        '${ApiConfig.mobileStores}/popular',
      ).replace(queryParameters: {'limit': limit.toString()});

      print('🔄 [STORE] Récupération boutiques populaires');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final dataMap = _asMap(data);

      print('📥 [STORE] Réponse populaires: ${response.statusCode}');

      if (response.statusCode == 200) {
        if (dataMap == null) {
          return {
            'success': false,
            'message': 'Réponse invalide du serveur',
            'stores': [],
          };
        }
        return {
          'success': true,
          'stores': dataMap['stores'] ?? dataMap['data'] ?? [],
        };
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            dataMap,
            'Erreur lors de la récupération des boutiques populaires',
          ),
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception populaires: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer les meilleures offres des boutiques officielles
  Future<Map<String, dynamic>> getOfficialStoresBestOffers({
    int limit = 12,
  }) async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse(
        '${ApiConfig.mobileStores}/best-offers',
      ).replace(queryParameters: {'limit': limit.toString()});

      print('🔄 [STORE] Récupération meilleures offres boutiques officielles');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final dataMap = _asMap(data);

      print('📥 [STORE] Réponse meilleures offres: ${response.statusCode}');

      if (response.statusCode == 200) {
        if (dataMap == null) {
          return {
            'success': false,
            'message': 'Réponse invalide du serveur',
            'products': [],
          };
        }
        return {
          'success': true,
          'products': dataMap['products'] ?? dataMap['data'] ?? [],
        };
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            dataMap,
            'Erreur lors de la récupération des meilleures offres',
          ),
          'products': [],
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception meilleures offres: $e');
      return {'success': false, 'message': e.toString(), 'products': []};
    }
  }

  /// Récupérer les nouveautés des boutiques officielles
  Future<Map<String, dynamic>> getOfficialStoresNewProducts({
    int limit = 12,
  }) async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse(
        '${ApiConfig.mobileStores}/new-products',
      ).replace(queryParameters: {'limit': limit.toString()});

      print('🔄 [STORE] Récupération nouveautés boutiques officielles');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final dataMap = _asMap(data);

      print('📥 [STORE] Réponse nouveautés: ${response.statusCode}');

      if (response.statusCode == 200) {
        if (dataMap == null) {
          return {
            'success': false,
            'message': 'Réponse invalide du serveur',
            'products': [],
            'total': 0,
            'has_more': false,
          };
        }
        return {
          'success': true,
          'products': dataMap['products'] ?? dataMap['data'] ?? [],
          'total': dataMap['total'] ?? 0,
          'has_more': dataMap['has_more'] ?? false,
        };
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            dataMap,
            'Erreur lors de la récupération des nouveautés',
          ),
          'products': [],
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception nouveautés: $e');
      return {'success': false, 'message': e.toString(), 'products': []};
    }
  }

  /// Récupérer les boutiques vérifiées
  Future<Map<String, dynamic>> getVerifiedStores({
    int limit = 10,
    bool officialOnly = false,
  }) async {
    try {
      final headers = await _getHeaders();
      final queryParams = <String, String>{'limit': limit.toString()};
      if (officialOnly) {
        queryParams['official_only'] = '1';
      }

      final uri = Uri.parse(
        '${ApiConfig.mobileStores}/verified',
      ).replace(queryParameters: queryParams);

      print('🔄 [STORE] Récupération boutiques vérifiées');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final dataMap = _asMap(data);

      print('📥 [STORE] Réponse vérifiées: ${response.statusCode}');

      if (response.statusCode == 200) {
        if (dataMap == null) {
          return {
            'success': false,
            'message': 'Réponse invalide du serveur',
            'stores': [],
          };
        }
        return {
          'success': true,
          'stores': dataMap['stores'] ?? dataMap['data'] ?? [],
        };
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            dataMap,
            'Erreur lors de la récupération des boutiques vérifiées',
          ),
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception vérifiées: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Rechercher des boutiques
  Future<Map<String, dynamic>> searchStores(
    String query, {
    int page = 1,
    int perPage = 20,
  }) async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse('${ApiConfig.stores}/search').replace(
        queryParameters: {
          'q': query,
          'page': page.toString(),
          'per_page': perPage.toString(),
        },
      );

      print('🔄 [STORE] Recherche boutiques: $query');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);
      final dataMap = _asMap(data);

      print('📥 [STORE] Réponse recherche: ${response.statusCode}');

      if (response.statusCode == 200) {
        if (dataMap == null) {
          return {
            'success': false,
            'message': 'Réponse invalide du serveur',
            'stores': [],
            'pagination': {},
          };
        }
        return {
          'success': true,
          'stores': dataMap['stores'] ?? dataMap['data'] ?? [],
          'pagination': dataMap['pagination'] ?? {},
        };
      } else {
        return {
          'success': false,
          'message': _extractErrorMessage(
            dataMap,
            'Erreur lors de la recherche',
          ),
        };
      }
    } catch (e) {
      print('💥 [STORE] Exception recherche: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}
