class CategoryBanner {
  final int? id;
  final String? title;
  final String? image;
  final String? linkUrl;
  final int sortOrder;

  CategoryBanner({
    this.id,
    this.title,
    this.image,
    this.linkUrl,
    this.sortOrder = 0,
  });

  factory CategoryBanner.fromJson(Map<String, dynamic> json) {
    return CategoryBanner(
      id: json['id'],
      title: json['title'],
      image: json['image'],
      linkUrl: json['link_url'],
      sortOrder: json['sort_order'] ?? 0,
    );
  }
}

class CategoryModel {
  final int id;
  final String name;
  final String slug;
  final String? icon;
  final String? image;
  final String? description;
  final bool isActive;
  final int order;
  final List<SubcategoryModel>? subcategories;
  final List<CategoryBanner>? customBanners;

  CategoryModel({
    required this.id,
    required this.name,
    required this.slug,
    this.icon,
    this.image,
    this.description,
    required this.isActive,
    required this.order,
    this.subcategories,
    this.customBanners,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      icon: json['icon'],
      image: json['image'],
      description: json['description'],
      isActive: json['is_active'] ?? true,
      order: json['order'] ?? 0,
      subcategories: json['subcategories'] != null
          ? (json['subcategories'] as List)
              .map((e) => SubcategoryModel.fromJson(e))
              .toList()
          : null,
      customBanners: json['custom_banners'] != null
          ? (json['custom_banners'] as List)
              .map((e) => CategoryBanner.fromJson(e))
              .toList()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'slug': slug,
      'icon': icon,
      'image': image,
      'description': description,
      'is_active': isActive,
      'order': order,
    };
  }
}

class SubcategoryModel {
  final int id;
  final int categoryId;
  final String name;
  final String slug;
  final String? icon;
  final bool isActive;
  final int order;

  SubcategoryModel({
    required this.id,
    required this.categoryId,
    required this.name,
    required this.slug,
    this.icon,
    required this.isActive,
    required this.order,
  });

  factory SubcategoryModel.fromJson(Map<String, dynamic> json) {
    return SubcategoryModel(
      id: json['id'] ?? 0,
      categoryId: json['category_id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      icon: json['icon'],
      isActive: json['is_active'] ?? true,
      order: json['order'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'category_id': categoryId,
      'name': name,
      'slug': slug,
      'icon': icon,
      'is_active': isActive,
      'order': order,
    };
  }
}

