class PaymentMethod {
  final String id;
  final String name;
  final String type; // 'mobile_money', 'card', 'cash_on_delivery'
  final String? icon;
  final String? description;
  final bool isActive;
  final Map<String, dynamic>? config;

  PaymentMethod({
    required this.id,
    required this.name,
    required this.type,
    this.icon,
    this.description,
    this.isActive = true,
    this.config,
  });

  factory PaymentMethod.fromJson(Map<String, dynamic> json) {
    return PaymentMethod(
      id: json['id'] ?? '',
      name: json['name'] ?? '',
      type: json['type'] ?? '',
      icon: json['icon'],
      description: json['description'],
      isActive: json['is_active'] ?? true,
      config: json['config'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'type': type,
      'icon': icon,
      'description': description,
      'is_active': isActive,
      'config': config,
    };
  }

  // Méthodes de création pour les méthodes de paiement par défaut
  static PaymentMethod orangeMoney() {
    return PaymentMethod(
      id: 'orange_money',
      name: 'Orange Money',
      type: 'mobile_money',
      icon: 'assets/icons/orange_money.png',
      description: 'Paiement sécurisé avec Orange Money',
      config: {
        'provider': 'orange',
        'country': 'CI',
        'currency': 'XOF',
      },
    );
  }

  static PaymentMethod mtnMoney() {
    return PaymentMethod(
      id: 'mtn_money',
      name: 'MTN Money',
      type: 'mobile_money',
      icon: 'assets/icons/mtn_money.png',
      description: 'Paiement sécurisé avec MTN Money',
      config: {
        'provider': 'mtn',
        'country': 'CI',
        'currency': 'XOF',
      },
    );
  }

  static PaymentMethod moovMoney() {
    return PaymentMethod(
      id: 'moov_money',
      name: 'Moov Money',
      type: 'mobile_money',
      icon: 'assets/icons/moov_money.png',
      description: 'Paiement sécurisé avec Moov Money',
      config: {
        'provider': 'moov',
        'country': 'CI',
        'currency': 'XOF',
      },
    );
  }

  static PaymentMethod cashOnDelivery() {
    return PaymentMethod(
      id: 'cash_on_delivery',
      name: 'Paiement à la livraison',
      type: 'cash_on_delivery',
      icon: 'assets/icons/cash.png',
      description: 'Payez en espèces lors de la livraison',
    );
  }

  static PaymentMethod card() {
    return PaymentMethod(
      id: 'card',
      name: 'Carte bancaire',
      type: 'card',
      icon: 'assets/icons/card.png',
      description: 'Paiement sécurisé par carte bancaire',
    );
  }

  static List<PaymentMethod> getDefaultMethods() {
    return [
      orangeMoney(),
      mtnMoney(),
      moovMoney(),
      cashOnDelivery(),
      card(),
    ];
  }
}
