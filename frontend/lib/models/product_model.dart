import '../utils/constants.dart';

class ProductModel {
  final int id;
  final int? storeId;
  final int categoryId;
  final int? subcategoryId;
  final String name;
  final String slug;
  final String? description;
  final double price;
  final double? oldPrice;
  final double? discountPercentage;
  final String? brand;
  final String? model;
  final String? warranty;
  final int stock;
  final String? image;
  final String? imageUrl;
  final List<String>? images;
  final Map<String, dynamic>? attributes;
  final List<String>? tags;
  final double rating;
  final int reviewsCount;
  final int views;
  final bool isFeatured;
  final bool isTrending;
  final bool isNew;
  final bool isBestOffer;
  final bool isActive;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final StoreBasicInfo? store;
  final CategoryBasicInfo? category;

  ProductModel({
    required this.id,
    this.storeId,
    required this.categoryId,
    this.subcategoryId,
    required this.name,
    required this.slug,
    this.description,
    required this.price,
    this.oldPrice,
    this.discountPercentage,
    this.brand,
    this.model,
    this.warranty,
    required this.stock,
    this.image,
    this.imageUrl,
    this.images,
    this.attributes,
    this.tags,
    required this.rating,
    required this.reviewsCount,
    required this.views,
    required this.isFeatured,
    required this.isTrending,
    required this.isNew,
    required this.isBestOffer,
    required this.isActive,
    this.createdAt,
    this.updatedAt,
    this.store,
    this.category,
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    try {
      String? sanitizeImage(dynamic value) {
        if (value == null) return null;
        final stringValue = value.toString();
        if (stringValue.isEmpty) return null;
        return ImageUrlHelper.fixImageUrl(stringValue);
      }

      List<String>? sanitizeImages(dynamic rawList) {
        if (rawList == null) return null;
        if (rawList is! List) return null;
        final sanitized = rawList
            .map((item) => sanitizeImage(item))
            .whereType<String>()
            .toList();
        return sanitized.isEmpty ? null : sanitized;
      }

      Map<String, dynamic>? parseAttributes(dynamic raw) {
        if (raw == null || raw is! Map) return null;

        final Map<String, dynamic> normalized = {};
        raw.forEach((key, value) {
          normalized[key.toString()] = value;
        });

        return normalized.map((key, value) {
          if (value is List) {
            return MapEntry(
              key.toString(),
              value.map((v) => v.toString()).toList(),
            );
          }
          return MapEntry(key.toString(), value.toString());
        });
      }

      final imageUrlSanitized = sanitizeImage(json['image_url']);
      final imagePaths = sanitizeImages(
        json['images'] ?? json['images_urls'] ?? json['images_paths'],
      );
      final imageSanitized =
          sanitizeImage(json['image']) ??
          (imagePaths != null && imagePaths.isNotEmpty
              ? imagePaths.first
              : null);

      Map<String, dynamic>? attributesData;
      attributesData = parseAttributes(
        json['attributes_display'] ?? json['attributes'],
      );

      // Parse store avec gestion d'erreur
      StoreBasicInfo? storeInfo;
      if (json['store'] != null) {
        if (json['store'] is Map<String, dynamic>) {
          storeInfo = StoreBasicInfo.fromJson(json['store']);
        } else if (json['store'] is! int) {
          print(
            '⚠️ [ProductModel] store invalide: ${json['store'].runtimeType}',
          );
        }
      }

      // Parse category avec gestion d'erreur
      CategoryBasicInfo? categoryInfo;
      if (json['category'] != null) {
        if (json['category'] is Map<String, dynamic>) {
          categoryInfo = CategoryBasicInfo.fromJson(json['category']);
        } else if (json['category'] is! int) {
          print(
            '⚠️ [ProductModel] category invalide: ${json['category'].runtimeType}',
          );
        }
      }

      return ProductModel(
        id: json['id'] ?? 0,
        storeId: json['store_id'],
        categoryId: json['category_id'] ?? 0,
        subcategoryId: json['subcategory_id'],
        name: json['name'] ?? '',
        slug: json['slug'] ?? '',
        description: json['description'],
        price: double.tryParse(json['price'].toString()) ?? 0.0,
        oldPrice: json['old_price'] != null
            ? double.tryParse(json['old_price'].toString())
            : null,
        discountPercentage: json['discount_percentage'] != null
            ? double.tryParse(json['discount_percentage'].toString())
            : null,
        brand: json['brand'],
        model: json['model'],
        warranty: json['warranty'],
        stock: json['stock'] ?? 0,
        image: imageSanitized,
        imageUrl: imageUrlSanitized ?? imageSanitized,
        images: imagePaths,
        attributes: attributesData,
        tags: json['tags'] != null && json['tags'] is List
            ? List<String>.from(json['tags'])
            : null,
        rating: double.tryParse(json['rating'].toString()) ?? 0.0,
        reviewsCount: json['reviews_count'] ?? 0,
        views: json['views'] ?? 0,
        isFeatured: json['is_featured'] ?? false,
        isTrending: json['is_trending'] ?? false,
        isNew: json['is_new'] ?? false,
        isBestOffer: json['is_best_offer'] ?? false,
        isActive: json['is_active'] ?? true,
        createdAt: json['created_at'] != null
            ? DateTime.parse(json['created_at'])
            : null,
        updatedAt: json['updated_at'] != null
            ? DateTime.parse(json['updated_at'])
            : null,
        store: storeInfo,
        category: categoryInfo,
      );
    } catch (e) {
      print('💥 [ProductModel] Erreur fromJson: $e');
      print('📊 [ProductModel] JSON: ${json.toString().substring(0, 200)}...');
      rethrow;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'store_id': storeId,
      'category_id': categoryId,
      'subcategory_id': subcategoryId,
      'name': name,
      'slug': slug,
      'description': description,
      'price': price,
      'old_price': oldPrice,
      'discount_percentage': discountPercentage,
      'brand': brand,
      'model': model,
      'warranty': warranty,
      'stock': stock,
      'image': image,
      'image_url': imageUrl,
      'images': images,
      'attributes': attributes,
      'tags': tags,
      'rating': rating,
      'reviews_count': reviewsCount,
      'views': views,
      'is_featured': isFeatured,
      'is_trending': isTrending,
      'is_new': isNew,
      'is_best_offer': isBestOffer,
      'is_active': isActive,
    };
  }

  bool get isInStock => stock > 0;
  bool get hasDiscount => oldPrice != null && oldPrice! > price;
}

class StoreBasicInfo {
  final int id;
  final String name;
  final String slug;
  final String? logo;
  final bool isOfficial;

  StoreBasicInfo({
    required this.id,
    required this.name,
    required this.slug,
    this.logo,
    required this.isOfficial,
  });

  factory StoreBasicInfo.fromJson(Map<String, dynamic> json) {
    return StoreBasicInfo(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      logo: json['logo'],
      isOfficial: json['is_official'] ?? false,
    );
  }
}

class CategoryBasicInfo {
  final int id;
  final String name;
  final String slug;
  final String? icon;

  CategoryBasicInfo({
    required this.id,
    required this.name,
    required this.slug,
    this.icon,
  });

  factory CategoryBasicInfo.fromJson(Map<String, dynamic> json) {
    return CategoryBasicInfo(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      icon: json['icon'],
    );
  }
}
