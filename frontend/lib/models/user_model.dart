class UserModel {
  final int id;
  final String nom;
  final String prenoms;
  final String email;
  final String? telephone;
  final String? profilePicUrl;
  final bool isVerified;
  final bool isSeller;
  final bool hasStore;
  final String? adresse;
  final String? codePostal;
  final String? ville;
  final String? pays;
  final String? bio;
  final bool newsletter;
  final String statut;
  final DateTime? emailVerifiedAt;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  UserModel({
    required this.id,
    required this.nom,
    required this.prenoms,
    required this.email,
    this.telephone,
    this.profilePicUrl,
    required this.isVerified,
    required this.isSeller,
    this.hasStore = false,
    this.adresse,
    this.codePostal,
    this.ville,
    this.pays,
    this.bio,
    required this.newsletter,
    required this.statut,
    this.emailVerifiedAt,
    this.createdAt,
    this.updatedAt,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    // Debug: Afficher les données brutes reçues
    print('🔍 [USER_MODEL] JSON reçu: $json');
    print('🔍 [USER_MODEL] is_seller brut: ${json['is_seller']} (type: ${json['is_seller'].runtimeType})');
    final parsedIsSeller = _parseBool(json['is_seller']);
    print('🔍 [USER_MODEL] is_seller après parsing: $parsedIsSeller');
    
    return UserModel(
      id: json['id'] ?? 0,
      nom: json['nom'] ?? '',
      prenoms: json['prenoms'] ?? '',
      email: json['email'] ?? '',
      telephone: json['telephone'],
      profilePicUrl: json['profile_pic_url'],
      isVerified: _parseBool(json['is_verified']),
      isSeller: parsedIsSeller,
      hasStore: _parseBool(json['has_store']),
      adresse: json['adresse'],
      codePostal: json['code_postal'],
      ville: json['ville'],
      pays: json['pays'],
      bio: json['bio'],
      newsletter: _parseBool(json['newsletter']),
      statut: json['statut'] ?? 'active',
      emailVerifiedAt: json['email_verified_at'] != null 
          ? DateTime.parse(json['email_verified_at']) 
          : null,
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : null,
      updatedAt: json['updated_at'] != null 
          ? DateTime.parse(json['updated_at']) 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom': nom,
      'prenoms': prenoms,
      'email': email,
      'telephone': telephone,
      'profile_pic_url': profilePicUrl,
      'is_verified': isVerified,
      'is_seller': isSeller,
      'adresse': adresse,
      'code_postal': codePostal,
      'ville': ville,
      'pays': pays,
      'bio': bio,
      'newsletter': newsletter,
      'statut': statut,
    };
  }

  String get fullName => '$prenoms $nom';

  /// Parse une valeur booléenne depuis différents formats (int, string, bool)
  static bool _parseBool(dynamic value) {
    if (value == null) return false;
    if (value is bool) return value;
    if (value is int) return value == 1;
    if (value is String) {
      return value.toLowerCase() == 'true' || value == '1';
    }
    return false;
  }
}

