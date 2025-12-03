import 'package:flutter/material.dart';
import '../models/banner_model.dart';
import '../widgets/promo_popup_dialog.dart';
import 'banner_service.dart';

class PopupManagerService {
  static final PopupManagerService _instance = PopupManagerService._internal();
  factory PopupManagerService() => _instance;
  PopupManagerService._internal();

  final BannerService _bannerService = BannerService();

  /// Vérifier et afficher les popups au démarrage de l'app
  Future<void> checkAndShowPopups(BuildContext context) async {
    try {
      // Récupérer les popups actifs
      final result = await _bannerService.getActiveBanners(type: 'popup');
      
      if (result['success'] != true) return;
      
      final List<BannerModel> popups = result['banners'] ?? [];
      
      if (popups.isEmpty) {
        print('📭 Aucun popup à afficher');
        return;
      }
      
      // Trier par priorité
      popups.sort((a, b) => b.priority.compareTo(a.priority));
      
      // Afficher le premier popup éligible
      for (var popup in popups) {
        final shouldShow = await _bannerService.shouldShowBanner(
          popup.id,
          popup.displayFrequency,
        );
        
        if (shouldShow) {
          // Attendre un peu avant d'afficher (UX)
          await Future.delayed(const Duration(seconds: 2));
          
          if (!context.mounted) return;
          
          await PromoPopupDialog.show(
            context,
            popup,
            onAction: (banner) {
              _handleBannerAction(context, banner);
            },
          );
          
          // Afficher seulement 1 popup par session
          break;
        }
      }
    } catch (e) {
      print('⚠️ Erreur affichage popups: $e');
    }
  }

  /// Gérer l'action d'une bannière
  void _handleBannerAction(BuildContext context, BannerModel banner) {
    print('🎯 Action bannière: ${banner.actionType}');
    
    switch (banner.actionType) {
      case 'product':
        final productId = banner.actionData?['product_id'];
        if (productId != null) {
          // Navigator.pushNamed(context, '/product', arguments: productId);
          print('→ Naviguer vers produit $productId');
        }
        break;
        
      case 'category':
        final categoryId = banner.actionData?['category_id'];
        if (categoryId != null) {
          // Navigator.pushNamed(context, '/category', arguments: categoryId);
          print('→ Naviguer vers catégorie $categoryId');
        }
        break;
        
      case 'url':
        final url = banner.actionData?['url'];
        if (url != null) {
          // Ouvrir l'URL dans un navigateur
          print('→ Ouvrir URL: $url');
        }
        break;
        
      case 'screen':
        final screenName = banner.actionData?['screen'];
        if (screenName != null) {
          // Navigator.pushNamed(context, '/$screenName');
          print('→ Naviguer vers $screenName');
        }
        break;
        
      default:
        print('→ Aucune action définie');
    }
  }

  /// Afficher un popup simple (pour tester)
  static Future<void> showSimplePopup(
    BuildContext context, {
    required String title,
    required String message,
    required String imageUrl,
  }) async {
    final banner = BannerModel(
      id: 0,
      title: title,
      description: message,
      image: imageUrl,
      type: 'popup',
      actionType: 'none',
      isActive: true,
      displayFrequency: 'always',
      targetAudience: 'all',
      priority: 1,
    );
    
    await PromoPopupDialog.show(context, banner);
  }
}

