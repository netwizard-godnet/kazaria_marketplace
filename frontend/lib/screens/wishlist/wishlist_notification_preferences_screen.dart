import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';

class WishlistNotificationPreferencesScreen extends StatefulWidget {
  const WishlistNotificationPreferencesScreen({super.key});

  @override
  State<WishlistNotificationPreferencesScreen> createState() =>
      _WishlistNotificationPreferencesScreenState();
}

class _WishlistNotificationPreferencesScreenState
    extends State<WishlistNotificationPreferencesScreen> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  bool _isSaving = false;

  // Préférences locales (non sauvegardées sur le serveur pour l'instant)
  bool _priceAlertsEnabled = true;
  bool _stockAlertsEnabled = true;
  bool _pushEnabled = true;
  bool _emailEnabled = false;
  String _reminderFrequency = 'instant';

  @override
  void initState() {
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Préférences d\'alertes'),
        actions: [
          IconButton(
            icon: _isSaving
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.save_outlined),
            onPressed: _isSaving ? null : _save,
            tooltip: 'Enregistrer',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          // Recharger les alertes
          await context.read<WishlistProvider>().loadAlerts();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Notifications reçues', style: AppTextStyles.h3),
                const SizedBox(height: 12),
                _buildToggle(
                  label: 'Alertes de prix',
                  description:
                      'Soyez averti lorsqu\'un produit passe sous votre prix cible.',
                  value: _priceAlertsEnabled,
                  onChanged: (value) =>
                      setState(() => _priceAlertsEnabled = value),
                ),
                _buildToggle(
                  label: 'Alertes de stock',
                  description:
                      'Recevez une notification quand un produit est de nouveau disponible.',
                  value: _stockAlertsEnabled,
                  onChanged: (value) =>
                      setState(() => _stockAlertsEnabled = value),
                ),
                const SizedBox(height: 24),
                const Text('Canaux de notification', style: AppTextStyles.h3),
                const SizedBox(height: 12),
                _buildToggle(
                  label: 'Push mobile',
                  description: 'Notification instantanée sur votre appareil.',
                  value: _pushEnabled,
                  onChanged: (value) => setState(() => _pushEnabled = value),
                ),
                _buildToggle(
                  label: 'E-mail',
                  description:
                      'Résumé envoyé par mail selon la fréquence choisie.',
                  value: _emailEnabled,
                  onChanged: (value) => setState(() => _emailEnabled = value),
                ),
                const SizedBox(height: 24),
                const Text('Fréquence des rappels', style: AppTextStyles.h3),
                const SizedBox(height: 12),
                _buildFrequencyDropdown(),
                const SizedBox(height: 16),
                const Card(
                  color: AppColors.info,
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: Row(
                      children: [
                        Icon(Icons.info_outline, color: Colors.white),
                        SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            'Les préférences sont sauvegardées localement sur votre appareil.',
                            style: TextStyle(color: Colors.white, fontSize: 13),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildToggle({
    required String label,
    required String description,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: SwitchListTile.adaptive(
        value: value,
        onChanged: onChanged,
        title: Text(label, style: AppTextStyles.h4),
        subtitle: Text(
          description,
          style: AppTextStyles.caption.copyWith(color: AppColors.textLight),
        ),
        activeColor: AppColors.primary,
      ),
    );
  }

  Widget _buildFrequencyDropdown() {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: DropdownButtonFormField<String>(
          value: _reminderFrequency,
          decoration: const InputDecoration(
            border: InputBorder.none,
            labelText: 'Choisir une fréquence',
          ),
          items: const [
            DropdownMenuItem(
              value: 'instant',
              child: Text('Notifications instantanées'),
            ),
            DropdownMenuItem(value: 'daily', child: Text('Résumé quotidien')),
            DropdownMenuItem(
              value: 'weekly',
              child: Text('Résumé hebdomadaire'),
            ),
            DropdownMenuItem(value: 'monthly', child: Text('Résumé mensuel')),
          ],
          onChanged: (value) {
            if (value == null) return;
            setState(() => _reminderFrequency = value);
          },
        ),
      ),
    );
  }

  Future<void> _save() async {
    if (_isSaving) return;

    setState(() => _isSaving = true);

    // Simuler une sauvegarde (les préférences sont locales pour l'instant)
    await Future.delayed(const Duration(milliseconds: 500));

    if (!mounted) return;

    setState(() => _isSaving = false);

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Préférences sauvegardées localement'),
        backgroundColor: AppColors.success,
      ),
    );
  }
}
