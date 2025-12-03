import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../utils/constants.dart';

class HelpScreen extends StatefulWidget {
  const HelpScreen({super.key});

  @override
  State<HelpScreen> createState() => _HelpScreenState();
}

class _HelpScreenState extends State<HelpScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _subjectController = TextEditingController();
  final _messageController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _subjectController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _submitContactForm() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSubmitting = true;
    });

    try {
      // TODO: Implémenter l'envoi du formulaire de contact
      await Future.delayed(const Duration(seconds: 2)); // Simulation
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Message envoyé avec succès !'),
            backgroundColor: AppColors.success,
          ),
        );
        
        _formKey.currentState!.reset();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  Future<void> _launchUrl(String url) async {
    final Uri uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Impossible d\'ouvrir le lien'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  void _copyToClipboard(String text) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Copié dans le presse-papiers'),
        duration: Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Aide et Support'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // FAQ
            const Text(
              'Questions fréquentes',
              style: AppTextStyles.h3,
            ),
            const SizedBox(height: 16),
            
            _buildFAQItem(
              'Comment passer une commande ?',
              'Ajoutez des produits à votre panier, puis procédez au checkout. Remplissez vos informations de livraison et confirmez votre commande.',
            ),
            _buildFAQItem(
              'Quels sont les modes de paiement acceptés ?',
              'Nous acceptons le paiement à la livraison, Mobile Money et les cartes bancaires.',
            ),
            _buildFAQItem(
              'Combien de temps prend la livraison ?',
              'La livraison prend généralement 2-5 jours ouvrables selon votre localisation.',
            ),
            _buildFAQItem(
              'Puis-je annuler ma commande ?',
              'Oui, vous pouvez annuler votre commande tant qu\'elle n\'a pas été expédiée.',
            ),
            _buildFAQItem(
              'Comment contacter le support ?',
              'Vous pouvez nous contacter via le formulaire ci-dessous ou par téléphone au +225 XX XX XX XX XX.',
            ),
            
            const SizedBox(height: 32),
            
            // Contact
            const Text(
              'Nous contacter',
              style: AppTextStyles.h3,
            ),
            const SizedBox(height: 16),
            
            Card(
              elevation: 2,
              child: Padding(
                padding: const EdgeInsets.all(AppSizes.paddingLarge),
                child: Form(
                  key: _formKey,
                  child: Column(
                    children: [
                      TextFormField(
                        controller: _nameController,
                        decoration: const InputDecoration(
                          labelText: 'Nom complet *',
                          border: OutlineInputBorder(),
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Le nom est requis';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      
                      TextFormField(
                        controller: _emailController,
                        decoration: const InputDecoration(
                          labelText: 'Email *',
                          border: OutlineInputBorder(),
                        ),
                        keyboardType: TextInputType.emailAddress,
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'L\'email est requis';
                          }
                          if (!value.contains('@')) {
                            return 'Email invalide';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      
                      TextFormField(
                        controller: _subjectController,
                        decoration: const InputDecoration(
                          labelText: 'Sujet *',
                          border: OutlineInputBorder(),
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Le sujet est requis';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      
                      TextFormField(
                        controller: _messageController,
                        decoration: const InputDecoration(
                          labelText: 'Message *',
                          border: OutlineInputBorder(),
                          alignLabelWithHint: true,
                        ),
                        maxLines: 5,
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Le message est requis';
                          }
                          if (value.trim().length < 10) {
                            return 'Le message doit contenir au moins 10 caractères';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 24),
                      
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _isSubmitting ? null : _submitContactForm,
                          child: _isSubmitting
                              ? const Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    ),
                                    SizedBox(width: 12),
                                    Text('Envoi en cours...'),
                                  ],
                                )
                              : const Text('Envoyer le message'),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            
            const SizedBox(height: 32),
            
            // Informations de contact
            const Text(
              'Informations de contact',
              style: AppTextStyles.h3,
            ),
            const SizedBox(height: 16),
            
            _buildContactInfo(
              'Téléphone',
              '+225 XX XX XX XX XX',
              Icons.phone,
              () => _launchUrl('tel:+225XXXXXXXXX'),
            ),
            _buildContactInfo(
              'Email',
              'support@kazaria.com',
              Icons.email,
              () => _launchUrl('mailto:support@kazaria.com'),
            ),
            _buildContactInfo(
              'Adresse',
              'Abidjan, Côte d\'Ivoire',
              Icons.location_on,
              () => _launchUrl('https://maps.google.com'),
            ),
            _buildContactInfo(
              'Horaires',
              'Lun-Ven: 8h-18h\nSam: 9h-17h',
              Icons.access_time,
              null,
            ),
            
            const SizedBox(height: 32),
            
            // Réseaux sociaux
            const Text(
              'Suivez-nous',
              style: AppTextStyles.h3,
            ),
            const SizedBox(height: 16),
            
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _buildSocialButton(
                  Icons.facebook,
                  'Facebook',
                  () => _launchUrl('https://facebook.com/kazaria'),
                ),
                const SizedBox(width: 16),
                _buildSocialButton(
                  Icons.camera_alt,
                  'Instagram',
                  () => _launchUrl('https://instagram.com/kazaria'),
                ),
                const SizedBox(width: 16),
                _buildSocialButton(
                  Icons.alternate_email,
                  'Twitter',
                  () => _launchUrl('https://twitter.com/kazaria'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFAQItem(String question, String answer) {
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
              style: AppTextStyles.body.copyWith(
                color: AppColors.textLight,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContactInfo(String title, String value, IconData icon, VoidCallback? onTap) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Icon(icon, color: AppColors.primary),
        title: Text(
          title,
          style: AppTextStyles.caption.copyWith(
            color: AppColors.textLight,
          ),
        ),
        subtitle: Text(
          value,
          style: AppTextStyles.body,
        ),
        trailing: onTap != null
            ? const Icon(Icons.open_in_new, size: 16)
            : IconButton(
                icon: const Icon(Icons.copy, size: 16),
                onPressed: () => _copyToClipboard(value),
              ),
        onTap: onTap,
      ),
    );
  }

  Widget _buildSocialButton(IconData icon, String label, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.primary.withOpacity(0.1),
          borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
          border: Border.all(
            color: AppColors.primary.withOpacity(0.3),
          ),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: AppColors.primary,
              size: 24,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: AppTextStyles.caption.copyWith(
                color: AppColors.primary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
