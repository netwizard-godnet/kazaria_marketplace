import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';
import '../services/share_service.dart';
import '../utils/constants.dart';

class ShareButton extends StatelessWidget {
  final String type; // 'product' ou 'store'
  final int id;
  final String name;
  final String? storeName;
  final String? slug;
  final String? description;
  final bool isCompact;
  final VoidCallback? onShared;

  const ShareButton({
    super.key,
    required this.type,
    required this.id,
    required this.name,
    this.storeName,
    this.slug,
    this.description,
    this.isCompact = false,
    this.onShared,
  });

  @override
  Widget build(BuildContext context) {
    final shareService = ShareService();

    return PopupMenuButton<String>(
      icon: Icon(
        Icons.share,
        color: isCompact ? AppColors.textMuted : AppColors.primary,
        size: isCompact ? 20 : 24,
      ),
      onSelected: (value) => _handleShareAction(
        context,
        shareService,
        value,
      ),
      itemBuilder: (BuildContext context) => [
        PopupMenuItem<String>(
          value: 'share',
          child: Row(
            children: [
              const Icon(Icons.share, color: AppColors.primary),
              const SizedBox(width: AppSizes.space2),
              Text('Partager'),
            ],
          ),
        ),
        PopupMenuItem<String>(
          value: 'copy',
          child: Row(
            children: [
              const Icon(Icons.copy, color: AppColors.primary),
              const SizedBox(width: AppSizes.space2),
              Text('Copier le lien'),
            ],
          ),
        ),
      ],
    );
  }

  Future<void> _handleShareAction(
    BuildContext context,
    ShareService shareService,
    String action,
  ) async {
    try {
      switch (action) {
        case 'share':
          if (type == 'product') {
            await shareService.shareProduct(
              productId: id,
              productName: name,
              storeName: storeName,
            );
          } else if (type == 'store') {
            await shareService.shareStore(
              storeId: id,
              storeName: name,
            );
          }
          break;
          
        case 'copy':
          if (type == 'product') {
            await shareService.copyProductShareLink(
              productId: id,
              productName: name,
            );
            _showSnackBar(context, 'Lien du produit copié !');
          } else if (type == 'store') {
            await shareService.copyStoreShareLink(
              storeId: id,
              storeName: name,
            );
            _showSnackBar(context, 'Lien de la boutique copié !');
          }
          break;
      }
      
      onShared?.call();
    } catch (e) {
      _showSnackBar(context, 'Erreur lors du partage: $e', isError: true);
    }
  }

  void _showSnackBar(BuildContext context, String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppColors.error : AppColors.success,
        duration: const Duration(seconds: 2),
      ),
    );
  }
}

class SimpleShareButton extends StatelessWidget {
  final String type;
  final String slug;
  final String title;
  final String? description;
  final bool isCompact;

  const SimpleShareButton({
    super.key,
    required this.type,
    required this.slug,
    required this.title,
    this.description,
    this.isCompact = false,
  });

  @override
  Widget build(BuildContext context) {
    final shareService = ShareService();

    return PopupMenuButton<String>(
      icon: Icon(
        Icons.share,
        color: isCompact ? AppColors.textMuted : AppColors.primary,
        size: isCompact ? 20 : 24,
      ),
      onSelected: (value) => _handleShareAction(context, shareService, value),
      itemBuilder: (BuildContext context) => [
        PopupMenuItem<String>(
          value: 'share',
          child: Row(
            children: [
              const Icon(Icons.share, color: AppColors.primary),
              const SizedBox(width: AppSizes.space2),
              Text('Partager'),
            ],
          ),
        ),
        PopupMenuItem<String>(
          value: 'copy',
          child: Row(
            children: [
              const Icon(Icons.copy, color: AppColors.primary),
              const SizedBox(width: AppSizes.space2),
              Text('Copier le lien'),
            ],
          ),
        ),
      ],
    );
  }

  Future<void> _handleShareAction(
    BuildContext context,
    ShareService shareService,
    String action,
  ) async {
    try {
      switch (action) {
        case 'share':
          await shareService.shareWithSimpleLink(
            type: type,
            slug: slug,
            title: title,
            description: description,
          );
          break;
          
        case 'copy':
          final shareUrl = shareService.generateSimpleShareLink(
            type: type,
            slug: slug,
          );
          await Clipboard.setData(ClipboardData(text: shareUrl));
          _showSnackBar(context, 'Lien copié !');
          break;
      }
    } catch (e) {
      _showSnackBar(context, 'Erreur lors du partage: $e', isError: true);
    }
  }

  void _showSnackBar(BuildContext context, String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppColors.error : AppColors.success,
        duration: const Duration(seconds: 2),
      ),
    );
  }
}
