import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../screens/webview/webview_screen.dart';

/// Section Politiques et Garanties - Inspire confiance
class PoliciesSection extends StatelessWidget {
  const PoliciesSection({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          // Première ligne
          Row(
            children: [
              Expanded(
                child: _PolicyCard(
                  icon: Icons.local_shipping_outlined,
                  title: 'Livraison possible',
                  subtitle: '1500 FCFA',
                  color: AppColors.success,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _PolicyCard(
                  icon: Icons.lock_outline,
                  title: 'Paiement Sécurisé',
                  subtitle: '100% Protégé',
                  color: AppColors.primary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          // Deuxième ligne
          Row(
            children: [
              Expanded(
                child: _PolicyCard(
                  icon: Icons.replay_outlined,
                  title: 'Retour Gratuit',
                  subtitle: 'Sous 7 jours',
                  color: AppColors.warning,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _PolicyCard(
                  icon: Icons.support_agent_outlined,
                  title: 'Support 24/7',
                  subtitle: 'Assistance rapide',
                  color: AppColors.info,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          // Troisième ligne
          Row(
            children: [
              Expanded(
                child: _PolicyCard(
                  icon: Icons.verified_user_outlined,
                  title: 'Produits Certifiés',
                  subtitle: '100% Authentiques',
                  color: AppColors.secondary,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _PolicyCard(
                  icon: Icons.workspace_premium_outlined,
                  title: 'Garantie Qualité',
                  subtitle: 'Satisfaction garantie',
                  color: Color(0xFFFF6B6B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          // Lien vers la politique de confidentialité
          InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => WebViewScreen(
                    url: 'https://kazaria-ci.com/politique-de-confidentialite',
                    title: 'Politique de confidentialité',
                  ),
                ),
              );
            },
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.05),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: AppColors.primary.withOpacity(0.2),
                  width: 1,
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.privacy_tip_outlined,
                    size: 18,
                    color: AppColors.primary,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Politique de confidentialité',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: AppColors.primary,
                    ),
                  ),
                  const SizedBox(width: 4),
                  Icon(
                    Icons.arrow_forward_ios,
                    size: 12,
                    color: AppColors.primary,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Card individuelle de politique
class _PolicyCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;

  const _PolicyCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.05),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withOpacity(0.2), width: 1),
      ),
      child: Column(
        children: [
          // Icône
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 28),
          ),
          const SizedBox(height: 8),
          // Titre
          Text(
            title,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.bold,
              color: AppColors.textDark,
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 4),
          // Sous-titre
          Text(
            subtitle,
            style: TextStyle(fontSize: 11, color: AppColors.textMedium),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
