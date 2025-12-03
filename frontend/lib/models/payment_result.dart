class PaymentResult {
  final bool success;
  final String? transactionId;
  final String? reference;
  final String? message;
  final String? errorCode;
  final Map<String, dynamic>? data;

  PaymentResult({
    required this.success,
    this.transactionId,
    this.reference,
    this.message,
    this.errorCode,
    this.data,
  });

  factory PaymentResult.fromJson(Map<String, dynamic> json) {
    return PaymentResult(
      success: json['success'] ?? false,
      transactionId: json['transaction_id'],
      reference: json['reference'],
      message: json['message'],
      errorCode: json['error_code'],
      data: json['data'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'success': success,
      'transaction_id': transactionId,
      'reference': reference,
      'message': message,
      'error_code': errorCode,
      'data': data,
    };
  }

  static PaymentResult paymentSuccess({
    String? transactionId,
    String? reference,
    String? message,
    Map<String, dynamic>? data,
  }) {
    return PaymentResult(
      success: true,
      transactionId: transactionId,
      reference: reference,
      message: message ?? 'Paiement réussi',
      data: data,
    );
  }

  static PaymentResult paymentFailure({
    String? errorCode,
    String? message,
    Map<String, dynamic>? data,
  }) {
    return PaymentResult(
      success: false,
      errorCode: errorCode,
      message: message ?? 'Paiement échoué',
      data: data,
    );
  }
}
