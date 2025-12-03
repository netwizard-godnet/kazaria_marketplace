class AppConfigModel {
  final String appName;
  final String appDescription;
  final String appUrl;
  final String appLogo;
  final String appLogoPath;
  final String language;
  final String country;
  final String locale;
  final String currency;
  final String currencySymbol;
  final Map<String, String?> social;
  final Map<String, String?> contact;

  AppConfigModel({
    required this.appName,
    required this.appDescription,
    required this.appUrl,
    required this.appLogo,
    required this.appLogoPath,
    required this.language,
    required this.country,
    required this.locale,
    required this.currency,
    required this.currencySymbol,
    required this.social,
    required this.contact,
  });

  factory AppConfigModel.fromJson(Map<String, dynamic> json) {
    return AppConfigModel(
      appName: json['app_name'] ?? 'KAZARIA',
      appDescription: json['app_description'] ?? '',
      appUrl: json['app_url'] ?? '',
      appLogo: json['app_logo'] ?? '',
      appLogoPath: json['app_logo_path'] ?? '',
      language: json['language'] ?? 'fr',
      country: json['country'] ?? 'CI',
      locale: json['locale'] ?? 'fr_CI',
      currency: json['currency'] ?? 'XOF',
      currencySymbol: json['currency_symbol'] ?? 'FCFA',
      social: {
        'facebook': json['social']?['facebook']?.toString(),
        'twitter': json['social']?['twitter']?.toString(),
        'instagram': json['social']?['instagram']?.toString(),
        'linkedin': json['social']?['linkedin']?.toString(),
        'youtube': json['social']?['youtube']?.toString(),
        'whatsapp': json['social']?['whatsapp']?.toString(),
      },
      contact: {
        'email': json['contact']?['email']?.toString(),
        'phone': json['contact']?['phone']?.toString(),
        'address': json['contact']?['address']?.toString(),
      },
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'app_name': appName,
      'app_description': appDescription,
      'app_url': appUrl,
      'app_logo': appLogo,
      'app_logo_path': appLogoPath,
      'language': language,
      'country': country,
      'locale': locale,
      'currency': currency,
      'currency_symbol': currencySymbol,
      'social': social,
      'contact': contact,
    };
  }
}

