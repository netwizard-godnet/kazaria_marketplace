import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import 'contact_screen.dart';

class HelpScreen extends StatelessWidget {
  const HelpScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Aide & FAQ'),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [AppColors.primary, AppColors.accent],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: const Column(
                children: [
                  Icon(
                    Icons.help_outline,
                    size: 60,
                    color: AppColors.white,
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Comment pouvons-nous vous aider ?',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: AppColors.white,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
            // Contact button
            Padding(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              child: Card(
                child: ListTile(
                  leading: const Icon(
                    Icons.mail_outline,
                    color: AppColors.primary,
                  ),
                  title: const Text('Nous contacter'),
                  subtitle: const Text('Envoyez-nous un message'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const ContactScreen(),
                      ),
                    );
                  },
                ),
              ),
            ),
            // FAQ
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingLarge,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Questions fréquentes',
                    style: AppTextStyles.h3,
                  ),
                  const SizedBox(height: 16),
                  _buildFaqItem(
                    'Comment passer une commande ?',
                    'Parcourez nos produits, ajoutez-les au panier et suivez le processus de commande.',
                  ),
                  _buildFaqItem(
                    'Quels sont les modes de paiement acceptés ?',
                    'Nous acceptons le paiement à la livraison, Mobile Money et les cartes bancaires.',
                  ),
                  _buildFaqItem(
                    'Combien de temps prend la livraison ?',
                    'La livraison standard prend 2-5 jours ouvrables selon votre localisation.',
                  ),
                  _buildFaqItem(
                    'Puis-je retourner un produit ?',
                    'Oui, vous pouvez retourner un produit dans les 14 jours suivant la réception.',
                  ),
                  _buildFaqItem(
                    'Comment devenir vendeur ?',
                    'Créez un compte, puis demandez à devenir vendeur depuis votre profil.',
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFaqItem(String question, String answer) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ExpansionTile(
        title: Text(
          question,
          style: AppTextStyles.body.copyWith(
            fontWeight: FontWeight.w600,
          ),
        ),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              answer,
              style: AppTextStyles.body,
            ),
          ),
        ],
      ),
    );
  }
}

