import 'package:flutter/material.dart';
import '../utils/constants.dart';

/// 🔔 Dialog personnalisé pour demander la permission des notifications
/// Affiche un design moderne et attrayant avant la demande système
class NotificationPermissionDialog extends StatelessWidget {
  final VoidCallback onAllow;
  final VoidCallback onDeny;

  const NotificationPermissionDialog({
    super.key,
    required this.onAllow,
    required this.onDeny,
  });

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
      ),
      backgroundColor: Colors.transparent,
      elevation: 0,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppSizes.radius2XL),
          boxShadow: AppShadows.shadowXL,
        ),
        padding: const EdgeInsets.all(AppSizes.space6),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Icône de notification animée
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                gradient: AppColors.primaryGradient,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: AppColors.primary.withOpacity(0.3),
                    blurRadius: 20,
                    spreadRadius: 5,
                  ),
                ],
              ),
              child: const Icon(
                Icons.notifications_active_rounded,
                color: AppColors.white,
                size: 40,
              ),
            ),

            const SizedBox(height: AppSizes.space6),

            // Titre
            Text(
              'Ne manquez rien !',
              style: AppTextStyles.headlineSmall.copyWith(
                color: AppColors.textDark,
                fontWeight: FontWeight.bold,
              ),
              textAlign: TextAlign.center,
            ),

            const SizedBox(height: AppSizes.space3),

            // Description
            Text(
              'Activez les notifications pour recevoir :',
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),

            const SizedBox(height: AppSizes.space4),

            // Liste des avantages
            _buildBenefitItem(
              icon: Icons.local_offer_rounded,
              text: 'Des offres exclusives et promotions',
              color: AppColors.accent,
            ),
            const SizedBox(height: AppSizes.space2),
            _buildBenefitItem(
              icon: Icons.shopping_bag_rounded,
              text: 'Le suivi de vos commandes en temps réel',
              color: AppColors.secondary,
            ),
            const SizedBox(height: AppSizes.space2),
            _buildBenefitItem(
              icon: Icons.favorite_rounded,
              text: 'Des alertes sur vos produits favoris',
              color: AppColors.error,
            ),
            const SizedBox(height: AppSizes.space2),
            _buildBenefitItem(
              icon: Icons.inventory_2_rounded,
              text: 'Les nouveautés et restocks',
              color: AppColors.primary,
            ),

            const SizedBox(height: AppSizes.space6),

            // Boutons
            Row(
              children: [
                // Bouton "Plus tard"
                Expanded(
                  child: OutlinedButton(
                    onPressed: () {
                      onDeny();
                    },
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(color: AppColors.grey300),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                      ),
                      padding: const EdgeInsets.symmetric(
                        vertical: AppSizes.space3,
                      ),
                    ),
                    child: Text(
                      'Plus tard',
                      style: AppTextStyles.button.copyWith(
                        color: AppColors.textMuted,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),

                const SizedBox(width: AppSizes.space3),

                // Bouton "Autoriser"
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: () {
                      onAllow();
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                      ),
                      padding: const EdgeInsets.symmetric(
                        vertical: AppSizes.space3,
                      ),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.check_circle_rounded,
                          size: 20,
                        ),
                        const SizedBox(width: AppSizes.space2),
                        Text(
                          'Autoriser',
                          style: AppTextStyles.button.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBenefitItem({
    required IconData icon,
    required String text,
    required Color color,
  }) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(AppSizes.space2),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(AppSizes.radiusMD),
          ),
          child: Icon(
            icon,
            color: color,
            size: 20,
          ),
        ),
        const SizedBox(width: AppSizes.space3),
        Expanded(
          child: Text(
            text,
            style: AppTextStyles.bodySmall.copyWith(
              color: AppColors.textDark,
            ),
          ),
        ),
      ],
    );
  }

  /// Afficher le dialog
  static Future<bool?> show(BuildContext context) {
    return showDialog<bool>(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black54,
      builder: (dialogContext) => NotificationPermissionDialog(
        onAllow: () {
          Navigator.of(dialogContext).pop(true);
        },
        onDeny: () {
          Navigator.of(dialogContext).pop(false);
        },
      ),
    );
  }
}

