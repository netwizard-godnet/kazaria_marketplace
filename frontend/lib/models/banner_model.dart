import 'dart:convert';

class BannerModel {
  final int id;
  final String title;
  final String? description;
  final String image;
  final String type; // popup, slider, banner, promo_card, carousel, homepage_banner_1, homepage_banner_2
  final String actionType; // none, product, category, url, screen
  final Map<String, dynamic>? actionData;
  final DateTime? startDate;
  final DateTime? endDate;
  final bool isActive;
  final String displayFrequency; // once, daily, always
  final String targetAudience; // all, new_users, returning_users, sellers
  final int priority;
  final String? buttonText; // Texte du bouton pour les slides du carousel

  BannerModel({
    required this.id,
    required this.title,
    this.description,
    required this.image,
    required this.type,
    required this.actionType,
    this.actionData,
    this.startDate,
    this.endDate,
    required this.isActive,
    required this.displayFrequency,
    required this.targetAudience,
    required this.priority,
    this.buttonText,
  });

  factory BannerModel.fromJson(Map<String, dynamic> json) {
    // Parser action_data qui peut être une string JSON ou déjà un objet
    Map<String, dynamic>? actionData;
    if (json['action_data'] != null) {
      if (json['action_data'] is String) {
        try {
          actionData = jsonDecode(json['action_data']) as Map<String, dynamic>;
        } catch (e) {
          print('⚠️ Erreur parsing action_data: $e');
          actionData = null;
        }
      } else if (json['action_data'] is Map) {
        actionData = json['action_data'] as Map<String, dynamic>;
      }
    }

    // ✅ Parser l'image avec gestion d'erreur
    String imageUrl = '';
    if (json['image'] != null) {
      imageUrl = json['image'].toString();
    } else if (json['image_url'] != null) {
      imageUrl = json['image_url'].toString();
    }
    
    // ✅ Parser le type avec support des différents formats
    String bannerType = json['type'] ?? 
                        json['banner_type'] ?? 
                        'carousel';
    
    // ✅ Parser actionType avec gestion de link
    String actionType = json['action_type'] ?? 'none';
    if (actionType == 'none' && json['link'] != null) {
      actionType = 'url';
    }
    
    // ✅ Parser actionData avec gestion du link
    Map<String, dynamic>? finalActionData = actionData;
    if (finalActionData == null && json['link'] != null) {
      finalActionData = {'url': json['link']};
    }
    
    return BannerModel(
      id: json['id'] is int ? json['id'] : (int.tryParse(json['id']?.toString() ?? '0') ?? 0),
      title: json['title'] ?? '',
      description:
          json['subtitle'] ??
          json['description'] ??
          '', // ✅ Support subtitle depuis l'API
      image: imageUrl, // ✅ URL complète depuis l'API
      type: bannerType,
      actionType: actionType,
      actionData: finalActionData,
      startDate: json['start_date'] != null
          ? (json['start_date'] is String ? DateTime.tryParse(json['start_date']) : null)
          : null,
      endDate: json['end_date'] != null
          ? (json['end_date'] is String ? DateTime.tryParse(json['end_date']) : null)
          : null,
      isActive: json['is_active'] ?? true,
      displayFrequency: json['display_frequency'] ?? 'always',
      targetAudience: json['target_audience'] ?? 'all',
      priority:
          json['priority'] ??
          json['sort_order'] ??
          0, // ✅ Support sort_order depuis l'API
      buttonText: json['button_text'], // ✅ Support button_text pour les slides du carousel
    );
  }
}
