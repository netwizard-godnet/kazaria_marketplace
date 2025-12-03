import 'dart:convert';

class StoreModel {
  final int id;
  final int userId;
  final String name;
  final String slug;
  final String? description;
  final int? categoryId;
  final int? subcategoryId;
  final Map<String, dynamic>? category; // Catégorie complète
  final Map<String, dynamic>? subcategory; // Sous-catégorie complète
  final String? phone;
  final String? email;
  final String? address;
  final String? city;
  final String? logo;
  final String? banner;
  final String? logoUrl;
  final String? bannerUrl;
  final String status;
  final bool isVerified;
  final bool isOfficial;
  final double commissionRate;
  final Map<String, dynamic>? businessHours;
  final Map<String, dynamic>? socialLinks;
  final int totalProducts;
  final int totalOrders;
  final double totalSales;
  final double rating;
  final int reviewsCount;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  StoreModel({
    required this.id,
    required this.userId,
    required this.name,
    required this.slug,
    this.description,
    this.categoryId,
    this.subcategoryId,
    this.category,
    this.subcategory,
    this.phone,
    this.email,
    this.address,
    this.city,
    this.logo,
    this.banner,
    this.logoUrl,
    this.bannerUrl,
    required this.status,
    required this.isVerified,
    required this.isOfficial,
    required this.commissionRate,
    this.businessHours,
    this.socialLinks,
    required this.totalProducts,
    required this.totalOrders,
    required this.totalSales,
    required this.rating,
    required this.reviewsCount,
    this.createdAt,
    this.updatedAt,
  });

  factory StoreModel.fromJson(Map<String, dynamic> json) {
    return StoreModel(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      description: json['description'],
      categoryId: json['category_id'],
      subcategoryId: json['subcategory_id'],
      category: json['category'] != null && json['category'] is Map ? Map<String, dynamic>.from(json['category']) : null,
      subcategory: json['subcategory'] != null && json['subcategory'] is Map ? Map<String, dynamic>.from(json['subcategory']) : null,
      phone: json['phone'],
      email: json['email'],
      address: json['address'],
      city: json['city'],
      logo: json['logo'],
      banner: json['banner'],
      logoUrl: json['logo_url'],
      bannerUrl: json['banner_url'],
      status: json['status'] ?? 'pending',
      isVerified: _parseBool(json['is_verified']),
      isOfficial: _parseBool(json['is_official']),
      commissionRate: double.tryParse(json['commission_rate'].toString()) ?? 0.0,
      businessHours: _parseJsonField(json['business_hours']),
      socialLinks: _parseJsonField(json['social_links']),
      totalProducts: json['total_products'] ?? 0,
      totalOrders: json['total_orders'] ?? 0,
      totalSales: double.tryParse(json['total_sales'].toString()) ?? 0.0,
      rating: double.tryParse(json['rating'].toString()) ?? 0.0,
      reviewsCount: json['reviews_count'] ?? 0,
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : null,
      updatedAt: json['updated_at'] != null 
          ? DateTime.parse(json['updated_at']) 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'name': name,
      'slug': slug,
      'description': description,
      'category_id': categoryId,
      'subcategory_id': subcategoryId,
      'phone': phone,
      'email': email,
      'address': address,
      'city': city,
      'logo': logo,
      'banner': banner,
      'logo_url': logoUrl,
      'banner_url': bannerUrl,
      'status': status,
      'is_verified': isVerified,
      'is_official': isOfficial,
      'commission_rate': commissionRate,
      'business_hours': businessHours,
      'social_links': socialLinks,
      'total_products': totalProducts,
      'total_orders': totalOrders,
      'total_sales': totalSales,
      'rating': rating,
      'reviews_count': reviewsCount,
    };
  }

  bool get isActive => status == 'active';
  bool get isPending => status == 'pending';

  /// Parse un champ JSON qui peut être une string ou déjà un Map
  static Map<String, dynamic>? _parseJsonField(dynamic value) {
    if (value == null) return null;
    if (value is Map<String, dynamic>) return value;
    if (value is String) {
      try {
        return Map<String, dynamic>.from(json.decode(value));
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  /// Parse une valeur booléenne depuis différents formats (int, string, bool)
  static bool _parseBool(dynamic value) {
    if (value == null) return false;
    if (value is bool) return value;
    if (value is int) return value == 1;
    if (value is String) {
      return value.toLowerCase() == 'true' || value == '1';
    }
    return false;
  }
}

