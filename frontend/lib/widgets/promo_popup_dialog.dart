import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/banner_model.dart';
import '../services/banner_service.dart';
import '../utils/constants.dart';

class PromoPopupDialog extends StatelessWidget {
  final BannerModel banner;
  final VoidCallback? onClose;
  final Function(BannerModel)? onAction;

  const PromoPopupDialog({
    Key? key,
    required this.banner,
    this.onClose,
    this.onAction,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
      ),
      child: Container(
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.9,
          maxHeight: MediaQuery.of(context).size.height * 0.7,
        ),
        child: Stack(
          children: [
            // Contenu
            Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Image
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(
                    top: Radius.circular(20),
                  ),
                  child: CachedNetworkImage(
                    imageUrl: banner.image,
                    width: double.infinity,
                    height: 250,
                    fit: BoxFit.cover,
                    placeholder: (context, url) => Container(
                      height: 250,
                      color: AppColors.grey100,
                      child: const Center(
                        child: CircularProgressIndicator(),
                      ),
                    ),
                    errorWidget: (context, url, error) => Container(
                      height: 250,
                      color: AppColors.grey100,
                      child: const Icon(Icons.image, size: 64),
                    ),
                  ),
                ),
                
                // Contenu texte
                Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Titre
                      Text(
                        banner.title,
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textDark,
                        ),
                      ),
                      
                      if (banner.description != null) ...[
                        const SizedBox(height: 12),
                        Text(
                          banner.description!,
                          style: const TextStyle(
                            fontSize: 14,
                            color: AppColors.textLight,
                          ),
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                      
                      const SizedBox(height: 20),
                      
                      // Boutons d'action
                      Row(
                        children: [
                          // Bouton Fermer
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () {
                                Navigator.pop(context);
                                onClose?.call();
                              },
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              child: const Text('Fermer'),
                            ),
                          ),
                          
                          const SizedBox(width: 12),
                          
                          // Bouton Action
                          if (banner.actionType != 'none')
                            Expanded(
                              flex: 2,
                              child: ElevatedButton(
                                onPressed: () {
                                  BannerService().trackClick(banner.id);
                                  Navigator.pop(context);
                                  onAction?.call(banner);
                                },
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.primary,
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                                child: Text(
                                  _getActionLabel(banner.actionType),
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            
            // Bouton de fermeture (X)
            Positioned(
              top: 8,
              right: 8,
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.black54,
                  shape: BoxShape.circle,
                ),
                child: IconButton(
                  icon: const Icon(Icons.close, color: Colors.white),
                  onPressed: () {
                    Navigator.pop(context);
                    onClose?.call();
                  },
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getActionLabel(String actionType) {
    switch (actionType) {
      case 'product':
        return 'Voir le produit';
      case 'category':
        return 'Explorer';
      case 'url':
        return 'En savoir plus';
      case 'screen':
        return 'Voir';
      default:
        return 'Découvrir';
    }
  }

  /// Afficher un popup de manière statique
  static Future<void> show(
    BuildContext context,
    BannerModel banner, {
    VoidCallback? onClose,
    Function(BannerModel)? onAction,
  }) async {
    // Marquer comme vu
    await BannerService().markAsShown(banner.id);
    
    if (!context.mounted) return;
    
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) => PromoPopupDialog(
        banner: banner,
        onClose: onClose,
        onAction: onAction,
      ),
    );
  }
}

