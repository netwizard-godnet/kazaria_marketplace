class OrderModel {
  final int id;
  final String orderNumber;
  final int userId;
  final String shippingName;
  final String shippingEmail;
  final String shippingPhone;
  final String shippingAddress;
  final String shippingCity;
  final String? shippingPostalCode;
  final String? shippingCountry;
  final double subtotal;
  final double shippingCost;
  final double tax;
  final double discount;
  final double total;
  final String status;
  final String paymentStatus;
  final String? paymentMethod;
  final String? paymentReference;
  final String? invoicePath;
  final String? customerNotes;
  final String? adminNotes;
  final DateTime? paidAt;
  final DateTime? shippedAt;
  final DateTime? deliveredAt;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final List<OrderItemModel>? items;

  OrderModel({
    required this.id,
    required this.orderNumber,
    required this.userId,
    required this.shippingName,
    required this.shippingEmail,
    required this.shippingPhone,
    required this.shippingAddress,
    required this.shippingCity,
    this.shippingPostalCode,
    this.shippingCountry,
    required this.subtotal,
    required this.shippingCost,
    required this.tax,
    required this.discount,
    required this.total,
    required this.status,
    required this.paymentStatus,
    this.paymentMethod,
    this.paymentReference,
    this.invoicePath,
    this.customerNotes,
    this.adminNotes,
    this.paidAt,
    this.shippedAt,
    this.deliveredAt,
    this.createdAt,
    this.updatedAt,
    this.items,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    return OrderModel(
      id: json['id'] ?? 0,
      orderNumber: json['order_number'] ?? '',
      userId: json['user_id'] ?? 0,
      shippingName: json['shipping_name'] ?? '',
      shippingEmail: json['shipping_email'] ?? '',
      shippingPhone: json['shipping_phone'] ?? '',
      shippingAddress: json['shipping_address'] ?? '',
      shippingCity: json['shipping_city'] ?? '',
      shippingPostalCode: json['shipping_postal_code'],
      shippingCountry: json['shipping_country'],
      subtotal: double.tryParse(json['subtotal'].toString()) ?? 0.0,
      shippingCost: double.tryParse(json['shipping_cost'].toString()) ?? 0.0,
      tax: double.tryParse(json['tax'].toString()) ?? 0.0,
      discount: double.tryParse(json['discount'].toString()) ?? 0.0,
      total: double.tryParse(json['total'].toString()) ?? 0.0,
      status: json['status'] ?? 'pending',
      paymentStatus: json['payment_status'] ?? 'pending',
      paymentMethod: json['payment_method'],
      paymentReference: json['payment_reference'],
      invoicePath: json['invoice_path'],
      customerNotes: json['customer_notes'],
      adminNotes: json['admin_notes'],
      paidAt: json['paid_at'] != null 
          ? DateTime.parse(json['paid_at']) 
          : null,
      shippedAt: json['shipped_at'] != null 
          ? DateTime.parse(json['shipped_at']) 
          : null,
      deliveredAt: json['delivered_at'] != null 
          ? DateTime.parse(json['delivered_at']) 
          : null,
      createdAt: json['created_at'] != null 
          ? OrderModel._parseDateTime(json['created_at']) 
          : null,
      updatedAt: json['updated_at'] != null 
          ? OrderModel._parseDateTime(json['updated_at']) 
          : null,
      items: (json['items'] ?? json['order_items'] ?? json['orderItems']) != null
          ? ((json['items'] ?? json['order_items'] ?? json['orderItems']) as List)
              .map((e) => OrderItemModel.fromJson(e))
              .toList()
          : [],
    );
  }

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'paid':
        return 'Payée';
      case 'processing':
        return 'En préparation';
      case 'shipped':
        return 'Expédiée';
      case 'delivered':
        return 'Livrée';
      case 'cancelled':
        return 'Annulée';
      case 'refunded':
        return 'Remboursée';
      default:
        return status;
    }
  }

  /// Helper pour parser les dates dans différents formats
  static DateTime? _parseDateTime(dynamic dateValue) {
    if (dateValue == null) return null;
    
    try {
      if (dateValue is String) {
        // Essayer de parser directement
        return DateTime.parse(dateValue);
      } else if (dateValue is DateTime) {
        return dateValue;
      }
    } catch (e) {
      print('⚠️ [ORDER_MODEL] Erreur parsing date: $dateValue, erreur: $e');
    }
    
    return null;
  }
}

class OrderItemModel {
  final int id;
  final int orderId;
  final int productId;
  final String productName;
  final String? productImage;
  final double price;
  final int quantity;
  final double total;
  final Map<String, dynamic>? attributes;

  OrderItemModel({
    required this.id,
    required this.orderId,
    required this.productId,
    required this.productName,
    this.productImage,
    required this.price,
    required this.quantity,
    required this.total,
    this.attributes,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    // ✅ Parser les attributs correctement (peut être null, Map, ou List vide)
    Map<String, dynamic>? parsedAttributes;
    if (json['attributes'] != null) {
      if (json['attributes'] is Map) {
        parsedAttributes = json['attributes'] as Map<String, dynamic>;
      } else if (json['attributes'] is List) {
        // Si c'est une liste vide, on laisse null
        parsedAttributes = null;
      }
    }
    
    return OrderItemModel(
      id: json['id'] ?? 0,
      orderId: json['order_id'] ?? 0,
      productId: json['product_id'] ?? 0,
      productName: json['product_name'] ?? '',
      productImage: json['product_image'],
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      quantity: json['quantity'] ?? 0,
      total: double.tryParse(json['total'].toString()) ?? 0.0,
      attributes: parsedAttributes,
    );
  }
}

