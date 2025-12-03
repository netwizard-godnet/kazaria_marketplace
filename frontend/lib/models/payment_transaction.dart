class PaymentTransaction {
  final String id;
  final String orderId;
  final String paymentMethodId;
  final String status; // 'pending', 'processing', 'completed', 'failed', 'cancelled'
  final double amount;
  final String currency;
  final String? transactionReference;
  final String? providerTransactionId;
  final Map<String, dynamic>? providerResponse;
  final String? errorMessage;
  final DateTime createdAt;
  final DateTime? updatedAt;
  final DateTime? completedAt;

  PaymentTransaction({
    required this.id,
    required this.orderId,
    required this.paymentMethodId,
    required this.status,
    required this.amount,
    required this.currency,
    this.transactionReference,
    this.providerTransactionId,
    this.providerResponse,
    this.errorMessage,
    required this.createdAt,
    this.updatedAt,
    this.completedAt,
  });

  factory PaymentTransaction.fromJson(Map<String, dynamic> json) {
    return PaymentTransaction(
      id: json['id'] ?? '',
      orderId: json['order_id'] ?? '',
      paymentMethodId: json['payment_method_id'] ?? '',
      status: json['status'] ?? 'pending',
      amount: (json['amount'] ?? 0).toDouble(),
      currency: json['currency'] ?? 'XOF',
      transactionReference: json['transaction_reference'],
      providerTransactionId: json['provider_transaction_id'],
      providerResponse: json['provider_response'],
      errorMessage: json['error_message'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: json['updated_at'] != null 
          ? DateTime.parse(json['updated_at']) 
          : null,
      completedAt: json['completed_at'] != null 
          ? DateTime.parse(json['completed_at']) 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'order_id': orderId,
      'payment_method_id': paymentMethodId,
      'status': status,
      'amount': amount,
      'currency': currency,
      'transaction_reference': transactionReference,
      'provider_transaction_id': providerTransactionId,
      'provider_response': providerResponse,
      'error_message': errorMessage,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
      'completed_at': completedAt?.toIso8601String(),
    };
  }

  PaymentTransaction copyWith({
    String? id,
    String? orderId,
    String? paymentMethodId,
    String? status,
    double? amount,
    String? currency,
    String? transactionReference,
    String? providerTransactionId,
    Map<String, dynamic>? providerResponse,
    String? errorMessage,
    DateTime? createdAt,
    DateTime? updatedAt,
    DateTime? completedAt,
  }) {
    return PaymentTransaction(
      id: id ?? this.id,
      orderId: orderId ?? this.orderId,
      paymentMethodId: paymentMethodId ?? this.paymentMethodId,
      status: status ?? this.status,
      amount: amount ?? this.amount,
      currency: currency ?? this.currency,
      transactionReference: transactionReference ?? this.transactionReference,
      providerTransactionId: providerTransactionId ?? this.providerTransactionId,
      providerResponse: providerResponse ?? this.providerResponse,
      errorMessage: errorMessage ?? this.errorMessage,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      completedAt: completedAt ?? this.completedAt,
    );
  }

  bool get isPending => status == 'pending';
  bool get isProcessing => status == 'processing';
  bool get isCompleted => status == 'completed';
  bool get isFailed => status == 'failed';
  bool get isCancelled => status == 'cancelled';

  String get statusText {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'processing':
        return 'En cours de traitement';
      case 'completed':
        return 'Terminé';
      case 'failed':
        return 'Échoué';
      case 'cancelled':
        return 'Annulé';
      default:
        return 'Inconnu';
    }
  }
}
