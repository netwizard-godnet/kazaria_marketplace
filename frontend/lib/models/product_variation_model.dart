/// Modèle pour une valeur d'attribut (ex: "Rouge", "Bleu", "S", "M")
class AttributeValue {
  final int id;
  final String value;
  final String slug;

  AttributeValue({
    required this.id,
    required this.value,
    required this.slug,
  });

  factory AttributeValue.fromJson(Map<String, dynamic> json) {
    return AttributeValue(
      id: json['id'] as int,
      value: json['value'] as String,
      slug: json['slug'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'value': value,
      'slug': slug,
    };
  }
}

/// Modèle pour un attribut de produit (ex: "Couleur", "Taille")
class ProductAttribute {
  final int id;
  final String name;
  final String slug;
  final String type;
  final List<AttributeValue> values;

  ProductAttribute({
    required this.id,
    required this.name,
    required this.slug,
    required this.type,
    required this.values,
  });

  factory ProductAttribute.fromJson(Map<String, dynamic> json) {
    return ProductAttribute(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      type: json['type'] as String? ?? 'select',
      values: (json['values'] as List<dynamic>?)
              ?.map((v) => AttributeValue.fromJson(v as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'slug': slug,
      'type': type,
      'values': values.map((v) => v.toJson()).toList(),
    };
  }
}

/// Modèle pour un attribut de variation (lié à une variation spécifique)
class VariationAttribute {
  final int attributeId;
  final String attributeName;
  final int valueId;
  final String value;

  VariationAttribute({
    required this.attributeId,
    required this.attributeName,
    required this.valueId,
    required this.value,
  });

  factory VariationAttribute.fromJson(Map<String, dynamic> json) {
    return VariationAttribute(
      attributeId: json['attribute_id'] as int,
      attributeName: json['attribute_name'] as String,
      valueId: json['value_id'] as int,
      value: json['value'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'attribute_id': attributeId,
      'attribute_name': attributeName,
      'value_id': valueId,
      'value': value,
    };
  }
}

/// Modèle pour une variation de produit
class ProductVariation {
  final int id;
  final String sku;
  final double price;
  final double? oldPrice;
  final double? discountPercentage;
  final int stock;
  final String? image;
  final bool isDefault;
  final List<VariationAttribute> attributes;

  ProductVariation({
    required this.id,
    required this.sku,
    required this.price,
    this.oldPrice,
    this.discountPercentage,
    required this.stock,
    this.image,
    required this.isDefault,
    required this.attributes,
  });

  /// Indique si la variation a une réduction
  bool get hasDiscount => oldPrice != null && oldPrice! > price;

  /// Indique si la variation est en stock
  bool get isInStock => stock > 0;

  /// Prix final (avec ou sans réduction)
  double get finalPrice => price;

  /// Prix normal (sans réduction)
  double get normalPrice => oldPrice ?? price;

  factory ProductVariation.fromJson(Map<String, dynamic> json) {
    return ProductVariation(
      id: json['id'] as int,
      sku: json['sku'] as String,
      price: (json['price'] as num).toDouble(),
      oldPrice: json['old_price'] != null ? (json['old_price'] as num).toDouble() : null,
      discountPercentage: json['discount_percentage'] != null
          ? (json['discount_percentage'] as num).toDouble()
          : null,
      stock: json['stock'] as int,
      image: json['image'] as String?,
      isDefault: json['is_default'] as bool? ?? false,
      attributes: (json['attributes'] as List<dynamic>?)
              ?.map((a) => VariationAttribute.fromJson(a as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'sku': sku,
      'price': price,
      'old_price': oldPrice,
      'discount_percentage': discountPercentage,
      'stock': stock,
      'image': image,
      'is_default': isDefault,
      'attributes': attributes.map((a) => a.toJson()).toList(),
    };
  }

  /// Vérifie si cette variation correspond aux attributs sélectionnés
  bool matchesSelection(Map<int, int> selectedAttributes) {
    if (selectedAttributes.isEmpty) return false;
    
    // Toutes les valeurs sélectionnées doivent correspondre
    for (var entry in selectedAttributes.entries) {
      final attributeId = entry.key;
      final valueId = entry.value;
      
      final hasMatch = attributes.any(
        (attr) => attr.attributeId == attributeId && attr.valueId == valueId,
      );
      
      if (!hasMatch) return false;
    }
    
    return true;
  }

  /// Obtenir une description textuelle des attributs
  String get attributesDescription {
    if (attributes.isEmpty) return 'Variation par défaut';
    return attributes.map((a) => '${a.attributeName}: ${a.value}').join(' - ');
  }
}

