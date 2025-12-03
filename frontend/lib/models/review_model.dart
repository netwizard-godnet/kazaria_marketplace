class ReviewModel {
  final int id;
  final int productId;
  final int userId;
  final String userName;
  final String? userProfilePic;
  final double rating;
  final String? title;
  final String comment;
  final bool isVerifiedPurchase;
  final int upvotes;
  final int downvotes;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  ReviewModel({
    required this.id,
    required this.productId,
    required this.userId,
    required this.userName,
    this.userProfilePic,
    required this.rating,
    this.title,
    required this.comment,
    required this.isVerifiedPurchase,
    required this.upvotes,
    required this.downvotes,
    this.createdAt,
    this.updatedAt,
  });

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    // Récupérer les infos utilisateur
    final user = json['user'] as Map<String, dynamic>?;
    
    // La table users a 'nom' et 'prenoms', pas 'name'
    String userName = 'Utilisateur';
    if (user != null) {
      final nom = user['nom'] ?? '';
      final prenoms = user['prenoms'] ?? '';
      userName = '$prenoms $nom'.trim();
      if (userName.isEmpty) {
        userName = user['email']?.split('@')[0] ?? 'Utilisateur';
      }
    }
    
    final userProfilePic = user?['profile_pic_url'] ?? user?['profile_photo'] ?? json['user_profile_pic'];

    return ReviewModel(
      id: json['id'] ?? 0,
      productId: json['product_id'] ?? 0,
      userId: json['user_id'] ?? 0,
      userName: userName,
      userProfilePic: userProfilePic,
      rating: double.tryParse(json['rating'].toString()) ?? 0.0,
      title: json['title'],
      comment: json['comment'] ?? '',
      isVerifiedPurchase: json['verified_purchase'] ?? json['is_verified_purchase'] ?? false,
      upvotes: json['helpful_count'] ?? json['upvotes'] ?? 0,
      downvotes: json['not_helpful_count'] ?? json['downvotes'] ?? 0,
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
      'product_id': productId,
      'user_id': userId,
      'rating': rating,
      'title': title,
      'comment': comment,
      'is_verified_purchase': isVerifiedPurchase,
      'upvotes': upvotes,
      'downvotes': downvotes,
    };
  }

  /// Créer une copie avec des modifications
  ReviewModel copyWith({
    int? id,
    int? productId,
    int? userId,
    String? userName,
    String? userProfilePic,
    double? rating,
    String? title,
    String? comment,
    bool? isVerifiedPurchase,
    int? upvotes,
    int? downvotes,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return ReviewModel(
      id: id ?? this.id,
      productId: productId ?? this.productId,
      userId: userId ?? this.userId,
      userName: userName ?? this.userName,
      userProfilePic: userProfilePic ?? this.userProfilePic,
      rating: rating ?? this.rating,
      title: title ?? this.title,
      comment: comment ?? this.comment,
      isVerifiedPurchase: isVerifiedPurchase ?? this.isVerifiedPurchase,
      upvotes: upvotes ?? this.upvotes,
      downvotes: downvotes ?? this.downvotes,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  int get totalVotes => upvotes - downvotes;
}

