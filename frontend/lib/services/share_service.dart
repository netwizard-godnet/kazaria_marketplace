import 'package:share_plus/share_plus.dart';
import 'package:flutter/services.dart';
import 'api_service.dart';
import 'package:url_launcher/url_launcher.dart';
import '../config/api_config.dart';

class ShareService {
  final ApiService _apiService = ApiService();

  /// Partager un produit
  Future<void> shareProduct({
    required int productId,
    required String productName,
    String? storeName,
  }) async {
    try {
      // Récupérer le lien de partage depuis l'API
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/share/product/$productId/link',
        requiresAuth: true,
      );

      if (response['success']) {
        final shareUrl = response['share_url'];
        final shareText = response['share_text'] ?? 
                         'Découvrez ce produit : $productName${storeName != null ? ' - $storeName' : ''}';

        await Share.share(
          '$shareText\n\n$shareUrl',
          subject: 'Produit partagé - $productName',
        );
      } else {
        throw Exception(response['message'] ?? 'Erreur lors de la génération du lien de partage');
      }
    } catch (e) {
      print('❌ [SHARE] Erreur partage produit: $e');
      rethrow;
    }
  }

  /// Partager une boutique
  Future<void> shareStore({
    required int storeId,
    required String storeName,
  }) async {
    try {
      // Récupérer le lien de partage depuis l'API
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/share/store/$storeId/link',
        requiresAuth: true,
      );

      if (response['success']) {
        final shareUrl = response['share_url'];
        final shareText = response['share_text'] ?? 
                         'Découvrez la boutique $storeName';

        await Share.share(
          '$shareText\n\n$shareUrl',
          subject: 'Boutique partagée - $storeName',
        );
      } else {
        throw Exception(response['message'] ?? 'Erreur lors de la génération du lien de partage');
      }
    } catch (e) {
      print('❌ [SHARE] Erreur partage boutique: $e');
      rethrow;
    }
  }

  /// Copier le lien de partage d'un produit dans le presse-papiers
  Future<void> copyProductShareLink({
    required int productId,
    required String productName,
  }) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/share/product/$productId/link',
        requiresAuth: true,
      );

      if (response['success']) {
        final shareUrl = response['share_url'];
        await Clipboard.setData(ClipboardData(text: shareUrl));
        
        // Optionnel : afficher un message de confirmation
        // Vous pouvez utiliser un SnackBar ou une notification ici
      } else {
        throw Exception(response['message'] ?? 'Erreur lors de la génération du lien de partage');
      }
    } catch (e) {
      print('❌ [SHARE] Erreur copie lien produit: $e');
      rethrow;
    }
  }

  /// Copier le lien de partage d'une boutique dans le presse-papiers
  Future<void> copyStoreShareLink({
    required int storeId,
    required String storeName,
  }) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/share/store/$storeId/link',
        requiresAuth: true,
      );

      if (response['success']) {
        final shareUrl = response['share_url'];
        await Clipboard.setData(ClipboardData(text: shareUrl));
        
        // Optionnel : afficher un message de confirmation
        // Vous pouvez utiliser un SnackBar ou une notification ici
      } else {
        throw Exception(response['message'] ?? 'Erreur lors de la génération du lien de partage');
      }
    } catch (e) {
      print('❌ [SHARE] Erreur copie lien boutique: $e');
      rethrow;
    }
  }

  /// Ouvrir le lien de partage dans le navigateur
  Future<void> openShareLink(String url) async {
    try {
      final Uri uri = Uri.parse(url);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        throw Exception('Impossible d\'ouvrir le lien');
      }
    } catch (e) {
      print('❌ [SHARE] Erreur ouverture lien: $e');
      rethrow;
    }
  }

  /// Générer un lien de partage simple (sans appel API)
  String generateSimpleShareLink({
    required String type, // 'product' ou 'store'
    required String slug,
  }) {
    return 'https://kazaria-marketplace.com/share/$type/$slug';
  }

  /// Partager avec un lien simple (sans appel API)
  Future<void> shareWithSimpleLink({
    required String type,
    required String slug,
    required String title,
    String? description,
  }) async {
    try {
      final shareUrl = generateSimpleShareLink(type: type, slug: slug);
      final shareText = description != null 
          ? '$description\n\n$shareUrl'
          : 'Découvrez $title\n\n$shareUrl';

      await Share.share(
        shareText,
        subject: title,
      );
    } catch (e) {
      print('❌ [SHARE] Erreur partage simple: $e');
      rethrow;
    }
  }
}
