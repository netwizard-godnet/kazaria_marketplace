import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';

class WishlistNotificationPreferencesScreen extends StatefulWidget {
  const WishlistNotificationPreferencesScreen({super.key});

  @override
  State<WishlistNotificationPreferencesScreen> createState() => _WishlistNotificationPreferencesScreenState();
}

class _WishlistNotificationPreferencesScreenState extends State<WishlistNotificationPreferencesScreen> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      context.read<WishlistProvider>().loadNotificationPreferences();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Préférences d’alertes'),
        actions: [
          Consumer<WishlistProvider>(
            builder: (context, provider, _) {
              final prefs = provider.notificationPreferences;
              return IconButton(
                icon: _isSaving
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.save_outlined),
                onPressed: prefs == null || _isSaving ? null : () => _save(provider),
                tooltip: 'Enregistrer',
              );
            },
          ),
        ],
      ),
      body: Consumer<WishlistProvider>(
        builder: (context, provider, _) {
          if (provider.preferencesLoading && provider.notificationPreferences == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.preferencesError != null && provider.notificationPreferences == null) {
            return _buildError(provider.preferencesError!, provider);
          }

          final prefs = provider.notificationPreferences ?? {};

          return RefreshIndicator(
            onRefresh: () => provider.loadNotificationPreferences(),
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Notifications reçues',
                      style: AppTextStyles.h3,
                    ),
                    const SizedBox(height: 12),
                    _buildToggle(
                      label: 'Alertes de prix',
                      description: 'Soyez averti lorsqu’un produit passe sous votre prix cible.',
                      value: (prefs['price_alerts_enabled'] ?? true) as bool,
                      onChanged: (value) => _updatePref(provider, 'price_alerts_enabled', value),
                    ),
                    _buildToggle(
                      label: 'Alertes de stock',
                      description: 'Recevez une notification quand un produit est de nouveau disponible.',
                      value: (prefs['stock_alerts_enabled'] ?? true) as bool,
                      onChanged: (value) => _updatePref(provider, 'stock_alerts_enabled', value),
                    ),
                    const SizedBox(height: 24),
                    const Text('Canaux de notification', style: AppTextStyles.h3),
                    const SizedBox(height: 12),
                    _buildToggle(
                      label: 'Push mobile',
                      description: 'Notification instantanée sur votre appareil.',
                      value: (prefs['push_enabled'] ?? true) as bool,
                      onChanged: (value) => _updatePref(provider, 'push_enabled', value),
                    ),
                    _buildToggle(
                      label: 'E-mail',
                      description: 'Résumé envoyé par mail selon la fréquence choisie.',
                      value: (prefs['email_enabled'] ?? true) as bool,
                      onChanged: (value) => _updatePref(provider, 'email_enabled', value),
                    ),
                    const SizedBox(height: 24),
                    const Text('Fréquence des rappels', style: AppTextStyles.h3),
                    const SizedBox(height: 12),
                    _buildFrequencyDropdown(provider, prefs['reminder_frequency'] as String? ?? 'instant'),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildError(String message, WishlistProvider provider) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text('Impossible de charger les préférences', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24.0),
            child: Text(
              message,
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => provider.loadNotificationPreferences(),
            child: const Text('Réessayer'),
          ),
        ],
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
        subtitle: Text(description, style: AppTextStyles.caption.copyWith(color: AppColors.textLight)),
        activeColor: AppColors.primary,
      ),
    );
  }

  Widget _buildFrequencyDropdown(WishlistProvider provider, String currentValue) {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: DropdownButtonFormField<String>(
          value: currentValue,
          decoration: const InputDecoration(
            border: InputBorder.none,
            labelText: 'Choisir une fréquence',
          ),
          items: const [
            DropdownMenuItem(value: 'instant', child: Text('Notifications instantanées')),
            DropdownMenuItem(value: 'daily', child: Text('Résumé quotidien')),
            DropdownMenuItem(value: 'weekly', child: Text('Résumé hebdomadaire')),
            DropdownMenuItem(value: 'monthly', child: Text('Résumé mensuel')),
          ],
          onChanged: (value) {
            if (value == null) return;
            _updatePref(provider, 'reminder_frequency', value);
          },
        ),
      ),
    );
  }

  void _updatePref(WishlistProvider provider, String key, dynamic value) {
    provider.setNotificationPreferenceValue(key, value);
  }

  Future<void> _save(WishlistProvider provider) async {
    if (_isSaving) return;

    final prefs = provider.notificationPreferences;
    if (prefs == null) return;

    setState(() => _isSaving = true);

    final response = await provider.updateNotificationPreferences(prefs);

    if (!mounted) return;

    setState(() => _isSaving = false);

    if (response['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Préférences sauvegardées'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'Erreur lors de la sauvegarde'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }
}


