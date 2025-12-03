import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import 'wishlist_alert_history_screen.dart';
import 'wishlist_notification_preferences_screen.dart';

class WishlistAlertsScreen extends StatefulWidget {
  const WishlistAlertsScreen({super.key});

  @override
  State<WishlistAlertsScreen> createState() => _WishlistAlertsScreenState();
}

class _WishlistAlertsScreenState extends State<WishlistAlertsScreen> {
  final Set<int> _loadingItems = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<WishlistProvider>().loadAlerts();
        context.read<WishlistProvider>().loadAlertHistory();
        context.read<WishlistProvider>().loadNotificationPreferences();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Alertes Wishlist'),
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            tooltip: 'Historique',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const WishlistAlertHistoryScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.settings_outlined),
            tooltip: 'Préférences',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const WishlistNotificationPreferencesScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => context.read<WishlistProvider>().loadAlerts(),
          ),
        ],
      ),
      body: Consumer<WishlistProvider>(
        builder: (context, provider, _) {
          if (provider.alertsLoading && provider.alertItems.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.alertsError != null && provider.alertItems.isEmpty) {
            return _buildError(provider.alertsError!);
          }

          if (provider.alertItems.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadAlerts(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.alertItems.length,
              itemBuilder: (context, index) {
                final item = provider.alertItems[index];
                return _buildAlertCard(item);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildAlertCard(Map<String, dynamic> item) {
    final product = item['product'] as Map<String, dynamic>? ?? {};
    final wishlist = item['wishlist'] as Map<String, dynamic>? ?? {};
    final priceAlertEnabled = item['price_alert_enabled'] == true;
    final stockAlertEnabled = item['stock_alert_enabled'] == true;
    final targetPrice = item['target_price'];
    final currentPrice = product['price'];
    final discount = item['discount_percentage'];

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        product['name'] ?? 'Produit #${product['id'] ?? item['product_id']}',
                        style: AppTextStyles.h4,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Liste : ${wishlist['name'] ?? '---'}',
                        style: AppTextStyles.caption.copyWith(color: AppColors.textLight),
                      ),
                    ],
                  ),
                ),
                if (_loadingItems.contains(item['id']))
                  const SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                _buildStatChip('Prix actuel', Helpers.formatPrice(currentPrice)),
                const SizedBox(width: 8),
                _buildStatChip(
                  'Prix cible',
                  targetPrice != null ? Helpers.formatPrice(targetPrice) : 'Aucun',
                  color: targetPrice != null ? AppColors.primary.withOpacity(0.1) : AppColors.grey100,
                ),
                if (discount != null)
                  Padding(
                    padding: const EdgeInsets.only(left: 8),
                    child: _buildStatChip('-${discount.toString()}%', 'Promo'),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                color: AppColors.grey50,
              ),
              child: Column(
                children: [
                  SwitchListTile.adaptive(
                    value: priceAlertEnabled,
                    title: const Text('Alerte de prix'),
                    subtitle: Text(
                      targetPrice != null
                          ? 'Notifie quand le prix atteint ${Helpers.formatPrice(targetPrice)}'
                          : 'Définissez un prix cible',
                    ),
                    onChanged: (value) => _onTogglePriceAlert(item, value),
                  ),
                  const Divider(height: 1),
                  SwitchListTile.adaptive(
                    value: stockAlertEnabled,
                    title: const Text('Alerte de stock'),
                    subtitle: const Text('Notifie lorsque le produit revient en stock'),
                    onChanged: (value) => _onToggleStockAlert(item, value),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                TextButton.icon(
                  onPressed: () => _editTargetPrice(item, targetPrice?.toString()),
                  icon: const Icon(Icons.edit),
                  label: const Text('Prix cible'),
                ),
                const SizedBox(width: 8),
                TextButton.icon(
                  onPressed: () => _editNote(item, item['note'] as String? ?? ''),
                  icon: const Icon(Icons.note_alt_outlined),
                  label: Text(item['note'] != null && (item['note'] as String).isNotEmpty
                      ? 'Modifier la note'
                      : 'Ajouter une note'),
                ),
              ],
            ),
            if (item['note'] != null && (item['note'] as String).isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.05),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    item['note'],
                    style: AppTextStyles.body,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Future<void> _onTogglePriceAlert(Map<String, dynamic> item, bool value) async {
    final provider = context.read<WishlistProvider>();
    final targetPrice = item['target_price'];
    final itemId = item['id'] as int;

    if (value && (targetPrice == null || (targetPrice is num && targetPrice <= 0))) {
      final newPrice = await _showTargetPriceDialog(item);
      if (newPrice == null) {
        return;
      }
      await _updateItem(itemId, () => provider.updateItem(
            itemId,
            targetPrice: newPrice,
            sendTargetPrice: true,
            priceAlertEnabled: true,
          ));
    } else {
      await _updateItem(itemId, () => provider.updateItem(
            itemId,
            priceAlertEnabled: value,
            sendTargetPrice: !value,
            targetPrice: value ? targetPrice : null,
          ));
    }
  }

  Future<void> _onToggleStockAlert(Map<String, dynamic> item, bool value) async {
    final provider = context.read<WishlistProvider>();
    final itemId = item['id'] as int;
    await _updateItem(itemId, () => provider.updateItem(
          itemId,
          stockAlertEnabled: value,
        ));
  }

  Future<void> _editTargetPrice(Map<String, dynamic> item, String? current) async {
    final newPrice = await _showTargetPriceDialog(item, initial: current);
    if (newPrice == null) return;

    final provider = context.read<WishlistProvider>();
    final itemId = item['id'] as int;
    await _updateItem(itemId, () => provider.updateItem(
          itemId,
          targetPrice: newPrice,
          sendTargetPrice: true,
          priceAlertEnabled: true,
        ));
  }

  Future<void> _editNote(Map<String, dynamic> item, String initial) async {
    final controller = TextEditingController(text: initial);
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Note personnelle'),
        content: TextField(
          controller: controller,
          maxLines: 4,
          decoration: const InputDecoration(
            hintText: 'Ajoutez un commentaire pour ce produit',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, controller.text.trim()),
            child: const Text('Enregistrer'),
          ),
        ],
      ),
    );

    if (result == null) return;

    final provider = context.read<WishlistProvider>();
    final itemId = item['id'] as int;
    await _updateItem(itemId, () => provider.updateItem(
          itemId,
          note: result,
        ));
  }

  Future<double?> _showTargetPriceDialog(Map<String, dynamic> item, {String? initial}) async {
    final controller = TextEditingController(text: initial ?? '');
    final result = await showDialog<double>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Définir un prix cible'),
        content: TextField(
          controller: controller,
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
          decoration: const InputDecoration(
            hintText: 'Entrez un montant (ex: 45000)',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              final value = double.tryParse(controller.text.replaceAll(',', '.'));
              if (value == null || value <= 0) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Veuillez entrer un montant valide.'),
                    backgroundColor: AppColors.error,
                  ),
                );
                return;
              }
              Navigator.pop(context, value);
            },
            child: const Text('Enregistrer'),
          ),
        ],
      ),
    );

    return result;
  }

  Future<void> _updateItem(int itemId, Future<Map<String, dynamic>> Function() action) async {
    setState(() => _loadingItems.add(itemId));
    final response = await action();
    if (mounted) {
      setState(() => _loadingItems.remove(itemId));

      if (response['success'] != true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'Erreur inconnue'),
            backgroundColor: AppColors.error,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Alerte mise à jour'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    }
  }

  Widget _buildStatChip(String label, String value, {Color? color}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color ?? AppColors.primary.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            label,
            style: AppTextStyles.caption.copyWith(fontSize: 11, color: AppColors.textLight),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          ),
        ],
      ),
    );
  }

  Widget _buildError(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.warning_amber, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text('Impossible de charger les alertes', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Text(
            message,
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => context.read<WishlistProvider>().loadAlerts(),
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.notifications_none,
              size: 100,
              color: AppColors.textLight.withOpacity(0.2),
            ),
            const SizedBox(height: 24),
            const Text(
              'Aucune alerte active',
              style: AppTextStyles.h3,
            ),
            const SizedBox(height: 8),
            Text(
              'Activez une alerte de prix ou de stock sur vos produits favoris pour les voir ici.',
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}


