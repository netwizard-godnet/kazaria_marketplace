import 'product_model.dart';

/// 💬 Modèle de message de chat avec l'assistant IA
class ChatMessageModel {
  final String id;
  final String text;
  final bool isUser;
  final DateTime timestamp;
  final List<ProductModel>? suggestedProducts;
  final String? quickAction; // 'view_product', 'add_to_cart', etc.
  final Map<String, dynamic>? metadata;

  ChatMessageModel({
    required this.id,
    required this.text,
    required this.isUser,
    required this.timestamp,
    this.suggestedProducts,
    this.quickAction,
    this.metadata,
  });

  factory ChatMessageModel.fromJson(Map<String, dynamic> json) {
    return ChatMessageModel(
      id: json['id'] ?? DateTime.now().millisecondsSinceEpoch.toString(),
      text: json['text'] ?? '',
      isUser: json['is_user'] ?? false,
      timestamp: json['timestamp'] != null
          ? DateTime.parse(json['timestamp'])
          : DateTime.now(),
      suggestedProducts: json['suggested_products'] != null
          ? (json['suggested_products'] as List)
              .map((e) => ProductModel.fromJson(e))
              .toList()
          : null,
      quickAction: json['quick_action'],
      metadata: json['metadata'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'text': text,
      'is_user': isUser,
      'timestamp': timestamp.toIso8601String(),
      'quick_action': quickAction,
      'metadata': metadata,
    };
  }
}

