class BrandModel {
  final int id;
  final String name;
  final String? image;
  final String? imagePath;
  final String? linkUrl;
  final int sortOrder;

  BrandModel({
    required this.id,
    required this.name,
    this.image,
    this.imagePath,
    this.linkUrl,
    this.sortOrder = 0,
  });

  factory BrandModel.fromJson(Map<String, dynamic> json) {
    return BrandModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      image: json['image'],
      imagePath: json['image_path'],
      linkUrl: json['link_url'],
      sortOrder: json['sort_order'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'image': image,
      'image_path': imagePath,
      'link_url': linkUrl,
      'sort_order': sortOrder,
    };
  }
}
