import 'dart:convert';
import 'product_model.dart';

class CartItemModel {
  final int id;
  final int productId;
  final ProductModel? product;
  final int quantity;
  final double price;
  final Map<String, dynamic>? attributes;

  CartItemModel({
    required this.id,
    required this.productId,
    this.product,
    required this.quantity,
    required this.price,
    this.attributes,
  });

  factory CartItemModel.fromJson(Map<String, dynamic> json) {
    // Parser les attributs en gérant les différents types
    Map<String, dynamic>? parsedAttributes;
    final rawAttributes = json['attributes'];

    if (rawAttributes != null) {
      if (rawAttributes is Map<String, dynamic>) {
        parsedAttributes = rawAttributes;
      } else if (rawAttributes is List && rawAttributes.isEmpty) {
        // Liste vide → null
        parsedAttributes = null;
      } else if (rawAttributes is String) {
        // String JSON → parser
        try {
          final decoded = jsonDecode(rawAttributes);
          parsedAttributes = decoded is Map<String, dynamic> ? decoded : null;
        } catch (e) {
          parsedAttributes = null;
        }
      }
    }

    return CartItemModel(
      id: json['id'] ?? 0,
      productId: json['product_id'] ?? 0,
      product: json['product'] != null
          ? ProductModel.fromJson(json['product'])
          : null,
      quantity: json['quantity'] ?? 1,
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      attributes: parsedAttributes,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'product_id': productId,
      'quantity': quantity,
      'price': price,
      'attributes': attributes,
    };
  }

  CartItemModel copyWith({
    int? id,
    int? productId,
    ProductModel? product,
    int? quantity,
    double? price,
    Map<String, dynamic>? attributes,
  }) {
    return CartItemModel(
      id: id ?? this.id,
      productId: productId ?? this.productId,
      product: product ?? this.product,
      quantity: quantity ?? this.quantity,
      price: price ?? this.price,
      attributes: attributes ?? this.attributes,
    );
  }

  double get total => price * quantity;
}

class CartModel {
  final List<CartItemModel> items;
  final double subtotal;
  final double shippingCost;
  final double tax;
  final double discount;
  final double total;

  CartModel({
    required this.items,
    required this.subtotal,
    required this.shippingCost,
    required this.tax,
    required this.discount,
    required this.total,
  });

  factory CartModel.fromJson(Map<String, dynamic> json) {
    // Support both 'items' and 'cart_items' keys
    final itemsList = json['items'] ?? json['cart_items'];

    return CartModel(
      items: itemsList != null
          ? (itemsList as List).map((e) => CartItemModel.fromJson(e)).toList()
          : [],
      subtotal: double.tryParse(json['subtotal']?.toString() ?? '0') ?? 0.0,
      shippingCost:
          double.tryParse(json['shipping_cost']?.toString() ?? '0') ?? 0.0,
      tax: double.tryParse(json['tax']?.toString() ?? '0') ?? 0.0,
      discount: double.tryParse(json['discount']?.toString() ?? '0') ?? 0.0,
      total: double.tryParse(json['total']?.toString() ?? '0') ?? 0.0,
    );
  }

  int get itemCount => items.fold(0, (sum, item) => sum + item.quantity);
  bool get isEmpty => items.isEmpty;
  bool get isNotEmpty => items.isNotEmpty;
}
