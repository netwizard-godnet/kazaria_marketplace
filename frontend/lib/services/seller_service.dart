import 'dart:io';
import '../config/api_config.dart';
import 'api_service.dart';

class SellerService {
  final ApiService _apiService = ApiService();

  /// Obtenir les statistiques de la boutique
  Future<Map<String, dynamic>> getStats() async {
    try {
      final response = await _apiService.get(
        ApiConfig.sellerStats,
        requiresAuth: true,
      );
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur stats: $e');
      rethrow;
    }
  }

  /// Obtenir les commandes récentes
  Future<Map<String, dynamic>> getRecentOrders({int limit = 5}) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.sellerRecentOrders}?limit=$limit',
        requiresAuth: true,
      );
      print('📊 [SELLER_SERVICE] Réponse commandes récentes: $response');
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur commandes récentes: $e');
      rethrow;
    }
  }

  /// Obtenir la liste des produits de la boutique
  Future<Map<String, dynamic>> getProducts({
    int page = 1,
    String? search,
    String? status,
  }) async {
    try {
      String url = '${ApiConfig.sellerProducts}?page=$page';

      if (search != null && search.isNotEmpty) {
        url += '&search=$search';
      }

      if (status != null && status.isNotEmpty) {
        url += '&status=$status';
      }

      final response = await _apiService.get(url, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur liste produits: $e');
      rethrow;
    }
  }

  /// Créer un nouveau produit
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
      final data = {
        'name': name,
        'description': description,
        'price': price.toString(),
        'stock': quantity.toString(),
        'categorie_id': categoryId.toString(),
        if (subcategoryId != null)
          'sous_categorie_id': subcategoryId.toString(),
        if (brand != null && brand.isNotEmpty) 'brand': brand,
        if (model != null && model.isNotEmpty) 'model': model,
        if (warranty != null && warranty.isNotEmpty) 'warranty': warranty,
        if (tags != null && tags.isNotEmpty) 'tags': tags,
        if (promoPrice != null) 'promo_price': promoPrice.toString(),
        if (discount != null) 'discount': discount.toString(),
      };

      return await _apiService.post(
        '${ApiConfig.baseUrl}/store/products',
        data,
        requiresAuth: true,
      );
    } catch (e) {
      print('❌ [SELLER] Erreur création produit: $e');
      rethrow;
    }
  }

  /// Récupérer un produit par ID
  Future<Map<String, dynamic>> getProduct(int productId) async {
    try {
      return await _apiService.get(
        '${ApiConfig.baseUrl}/store/products/$productId',
        requiresAuth: true,
      );
    } catch (e) {
      print('❌ [SELLER] Erreur récupération produit: $e');
      rethrow;
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
      final data = <String, dynamic>{
        'name': name,
        'description': description,
        'price': price.toString(),
        'stock': quantity.toString(),
      };

      if (brand != null && brand.isNotEmpty) data['brand'] = brand;
      if (model != null && model.isNotEmpty) data['model'] = model;
      if (warranty != null && warranty.isNotEmpty) data['warranty'] = warranty;
      if (tags != null && tags.isNotEmpty) data['tags'] = tags;
      if (promoPrice != null) data['promo_price'] = promoPrice.toString();
      if (discount != null) data['discount'] = discount.toString();

      final url = '${ApiConfig.baseUrl}/store/products/$productId';
      return await _apiService.put(url, data, requiresAuth: true);
    } catch (e) {
      print('❌ [SELLER] Erreur mise à jour produit: $e');
      rethrow;
    }
  }

  /// Supprimer un produit
  Future<Map<String, dynamic>> deleteProduct(int productId) async {
    try {
      final url = '${ApiConfig.baseUrl}/store/products/$productId';
      final response = await _apiService.delete(url, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur suppression produit: $e');
      rethrow;
    }
  }

  /// Obtenir les commandes de la boutique
  Future<Map<String, dynamic>> getOrders({int page = 1, String? status}) async {
    try {
      String url = '${ApiConfig.sellerOrders}?page=$page';

      if (status != null && status.isNotEmpty) {
        url += '&status=$status';
      }

      final response = await _apiService.get(url, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur liste commandes: $e');
      rethrow;
    }
  }

  /// Obtenir les détails d'une commande
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> getOrderDetails(String orderNumber) async {
    try {
      final url = '${ApiConfig.sellerOrders}/$orderNumber';
      final response = await _apiService.get(url, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur détails commande: $e');
      rethrow;
    }
  }

  /// Obtenir les statistiques des commandes
  Future<Map<String, dynamic>> getOrderStats() async {
    try {
      final url = '${ApiConfig.sellerOrders}/stats';
      final response = await _apiService.get(url, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur stats commandes: $e');
      rethrow;
    }
  }

  /// Mettre à jour le statut d'une commande
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> updateOrderStatus(
    String orderNumber,
    String newStatus,
  ) async {
    try {
      final url = '${ApiConfig.sellerOrders}/$orderNumber/status';
      final response = await _apiService.put(url, {
        'status': newStatus,
      }, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur mise à jour statut commande: $e');
      rethrow;
    }
  }

  /// Marquer une commande comme expédiée
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> markAsShipped(String orderNumber) async {
    try {
      final url = '${ApiConfig.sellerOrders}/$orderNumber/ship';
      final response = await _apiService.post(url, {}, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur marquer comme expédié: $e');
      rethrow;
    }
  }

  /// Marquer une commande comme livrée
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> markAsDelivered(String orderNumber) async {
    try {
      final url = '${ApiConfig.sellerOrders}/$orderNumber/deliver';
      final response = await _apiService.post(url, {}, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur marquer comme livré: $e');
      rethrow;
    }
  }

  /// Annuler une commande (vendeur)
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> cancelOrderSeller(String orderNumber) async {
    try {
      final url = '${ApiConfig.sellerOrders}/$orderNumber/cancel';
      final response = await _apiService.post(url, {}, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur annulation commande: $e');
      rethrow;
    }
  }

  /// Changer le statut de paiement d'une commande
  /// Note: Utilise orderNumber (string) au lieu de orderId (int)
  Future<Map<String, dynamic>> changePaymentStatus(
    String orderNumber,
    String paymentStatus,
  ) async {
    try {
      final url = '${ApiConfig.sellerOrders}/$orderNumber/payment-status';
      final response = await _apiService.put(url, {
        'payment_status': paymentStatus,
      }, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur changement statut paiement: $e');
      rethrow;
    }
  }

  /// Obtenir les informations complètes de la boutique
  Future<Map<String, dynamic>> getStoreInfo() async {
    try {
      print('🔄 [SELLER_SERVICE] Appel API: ${ApiConfig.sellerStoreInfo}');
      final response = await _apiService.get(
        ApiConfig.sellerStoreInfo,
        requiresAuth: true,
      );
      print('📊 [SELLER_SERVICE] Réponse API: $response');
      return response;
    } catch (e) {
      print('❌ [SELLER_SERVICE] Erreur récupération info: $e');
      rethrow;
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
      final data = <String, dynamic>{};

      if (name != null && name.isNotEmpty) data['name'] = name;
      if (description != null && description.isNotEmpty)
        data['description'] = description;
      if (phone != null && phone.isNotEmpty) data['phone'] = phone;
      if (email != null && email.isNotEmpty) data['email'] = email;
      if (address != null && address.isNotEmpty) data['address'] = address;
      if (city != null && city.isNotEmpty) data['city'] = city;
      if (facebook != null && facebook.isNotEmpty) data['facebook'] = facebook;
      if (instagram != null && instagram.isNotEmpty)
        data['instagram'] = instagram;
      if (twitter != null && twitter.isNotEmpty) data['twitter'] = twitter;
      if (website != null && website.isNotEmpty) data['website'] = website;

      return await _apiService.put(
        '${ApiConfig.baseUrl}/store/update',
        data,
        requiresAuth: true,
      );
    } catch (e) {
      print('❌ [SELLER] Erreur mise à jour boutique: $e');
      rethrow;
    }
  }

  /// Upload du logo de la boutique
  Future<Map<String, dynamic>> uploadLogo(File logoFile) async {
    try {
      return await _apiService.uploadFile(
        '${ApiConfig.baseUrl}/store/upload-logo',
        'logo',
        logoFile,
        requiresAuth: true,
      );
    } catch (e) {
      print('❌ [SELLER] Erreur upload logo: $e');
      rethrow;
    }
  }

  /// Upload de la bannière de la boutique
  Future<Map<String, dynamic>> uploadBanner(File bannerFile) async {
    try {
      return await _apiService.uploadFile(
        '${ApiConfig.baseUrl}/store/upload-banner',
        'banner',
        bannerFile,
        requiresAuth: true,
      );
    } catch (e) {
      print('❌ [SELLER] Erreur upload bannière: $e');
      rethrow;
    }
  }

  /// Upload d'un document (DFE ou registre de commerce)
  Future<Map<String, dynamic>> uploadDocument(
    File documentFile,
    String type,
  ) async {
    try {
      return await _apiService.uploadFile(
        '${ApiConfig.baseUrl}/store/upload-document',
        'document',
        documentFile,
        requiresAuth: true,
        additionalFields: {'type': type},
      );
    } catch (e) {
      print('❌ [SELLER] Erreur upload document: $e');
      rethrow;
    }
  }

  /// Mettre à jour l'image principale d'un produit
  Future<Map<String, dynamic>> updateProductMainImage(
    int productId,
    File image,
  ) async {
    try {
      return await _apiService.uploadFile(
        '${ApiConfig.baseUrl}/store/products/$productId/image',
        'image',
        image,
        requiresAuth: true,
      );
    } catch (e) {
      print('❌ [SELLER] Erreur mise à jour image principale: $e');
      rethrow;
    }
  }

  /// Upload d'images supplémentaires pour un produit
  Future<Map<String, dynamic>> uploadProductImages(
    int productId,
    List<File> images,
  ) async {
    try {
      return await _apiService.uploadMultipleFiles(
        '${ApiConfig.baseUrl}/store/products/$productId/images',
        'images',
        images,
        requiresAuth: true,
      );
    } catch (e) {
      print('❌ [SELLER] Erreur upload images produit: $e');
      rethrow;
    }
  }

  /// Supprimer une image d'un produit
  Future<Map<String, dynamic>> deleteProductImage(
    int productId,
    int imageId,
  ) async {
    try {
      final url = '${ApiConfig.baseUrl}/store/products/$productId/images';
      final response = await _apiService.delete(url, requiresAuth: true);
      // Note: Le backend peut nécessiter imageId dans le body ou query param
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur suppression image: $e');
      rethrow;
    }
  }

  /// Activer/Désactiver la boutique
  Future<Map<String, dynamic>> toggleStoreStatus() async {
    try {
      final url = '${ApiConfig.baseUrl}/store/toggle-status';
      final response = await _apiService.post(url, {}, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur toggle statut boutique: $e');
      rethrow;
    }
  }

  /// Supprimer la boutique
  Future<Map<String, dynamic>> deleteStore() async {
    try {
      final url = '${ApiConfig.baseUrl}/store/delete';
      final response = await _apiService.delete(url, requiresAuth: true);
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur suppression boutique: $e');
      rethrow;
    }
  }

  /// Créer une nouvelle boutique
  Future<Map<String, dynamic>> createStore({
    required String name,
    required String description,
    required int categoryId,
    int? subcategoryId,
    required String phone,
    required String email,
    String? address,
    String? city,
    File? logoFile,
    File? bannerFile,
    required File dfeDocument,
    required File commerceRegister,
    String? facebook,
    String? instagram,
    String? twitter,
    String? website,
  }) async {
    try {
      // Préparer les champs du formulaire
      final fields = <String, String>{
        'name': name,
        'description': description,
        'category_id': categoryId.toString(),
        'phone': phone,
        'email': email,
      };

      if (subcategoryId != null) {
        fields['subcategory_id'] = subcategoryId.toString();
      }
      if (address != null && address.isNotEmpty) {
        fields['address'] = address;
      }
      if (city != null && city.isNotEmpty) {
        fields['city'] = city;
      }
      if (facebook != null && facebook.isNotEmpty) {
        fields['facebook'] = facebook;
      }
      if (instagram != null && instagram.isNotEmpty) {
        fields['instagram'] = instagram;
      }
      if (twitter != null && twitter.isNotEmpty) {
        fields['twitter'] = twitter;
      }
      if (website != null && website.isNotEmpty) {
        fields['website'] = website;
      }

      // Préparer les fichiers
      final files = <String, String>{
        'dfe_document': dfeDocument.path,
        'commerce_register': commerceRegister.path,
      };

      if (logoFile != null) {
        files['logo'] = logoFile.path;
      }
      if (bannerFile != null) {
        files['banner'] = bannerFile.path;
      }

      print('🏪 [SELLER] Création boutique: $name');

      final response = await _apiService.postMultipart(
        '${ApiConfig.baseUrl}/store/create',
        fields,
        files: files,
        requiresAuth: true,
      );

      print('✅ [SELLER] Boutique créée: ${response['success']}');
      return response;
    } catch (e) {
      print('❌ [SELLER] Erreur création boutique: $e');
      rethrow;
    }
  }
}
