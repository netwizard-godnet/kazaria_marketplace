import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:timeago/timeago.dart' as timeago;
import '../../providers/notification_provider.dart';
import '../../utils/constants.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    // Charger les notifications
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<NotificationProvider>().loadNotifications();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          // Bouton pour tout marquer comme lu
          Consumer<NotificationProvider>(
            builder: (context, provider, _) {
              if (provider.unreadCount > 0) {
                return TextButton(
                  onPressed: () async {
                    await provider.markAllAsRead();
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Toutes les notifications marquées comme lues'),
                          duration: Duration(seconds: 2),
                        ),
                      );
                    }
                  },
                  child: const Text(
                    'Tout marquer',
                    style: TextStyle(color: AppColors.primary),
                  ),
                );
              }
              return const SizedBox.shrink();
            },
          ),
        ],
      ),
      body: Consumer<NotificationProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.notifications.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 64, color: AppColors.error),
                  const SizedBox(height: 16),
                  Text(
                    'Erreur de chargement',
                    style: AppTextStyles.h3,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    provider.error!,
                    style: AppTextStyles.body.copyWith(color: AppColors.textLight),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: () => provider.loadNotifications(),
                    child: const Text('Réessayer'),
                  ),
                ],
              ),
            );
          }

          if (provider.notifications.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.notifications_none,
                    size: 80,
                    color: AppColors.textLight.withOpacity(0.5),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Aucune notification',
                    style: AppTextStyles.h3.copyWith(color: AppColors.textLight),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Vous n\'avez pas encore de notifications',
                    style: AppTextStyles.body.copyWith(color: AppColors.textLight),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadNotifications(),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: provider.notifications.length,
              separatorBuilder: (context, index) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final notification = provider.notifications[index];
                return _buildNotificationItem(notification, provider);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildNotificationItem(
    Map<String, dynamic> notification,
    NotificationProvider provider,
  ) {
    final isRead = notification['is_read'] == true || notification['is_read'] == 1;
    final type = notification['type'] as String? ?? 'general';
    final title = notification['title'] as String? ?? '';
    final body = notification['body'] as String? ?? '';
    final createdAt = notification['created_at'] as String?;

    // Calculer le temps écoulé
    String timeAgo = '';
    if (createdAt != null) {
      try {
        final dateTime = DateTime.parse(createdAt);
        timeAgo = timeago.format(dateTime, locale: 'fr');
      } catch (e) {
        timeAgo = '';
      }
    }

    return Container(
      decoration: BoxDecoration(
        color: isRead ? Colors.transparent : AppColors.primary.withOpacity(0.05),
        borderRadius: BorderRadius.circular(8),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        leading: _getNotificationIcon(type, isRead),
        title: Text(
          title,
          style: AppTextStyles.body.copyWith(
            fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
          ),
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              body,
              style: AppTextStyles.caption.copyWith(
                color: AppColors.textLight,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            if (timeAgo.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                timeAgo,
                style: AppTextStyles.caption.copyWith(
                  color: AppColors.textLight,
                  fontSize: 11,
                ),
              ),
            ],
          ],
        ),
        onTap: () async {
          // Marquer comme lue
          if (!isRead) {
            await provider.markAsRead(notification['id']);
          }

          // Navigator vers l'écran approprié
          _handleNotificationTap(notification);
        },
      ),
    );
  }

  Widget _getNotificationIcon(String type, bool isRead) {
    IconData icon;
    Color color;

    switch (type) {
      case 'price_drop':
        icon = Icons.trending_down;
        color = AppColors.success;
        break;
      case 'back_in_stock':
        icon = Icons.inventory_2;
        color = AppColors.primary;
        break;
      case 'order_shipped':
        icon = Icons.local_shipping;
        color = Colors.orange;
        break;
      case 'order_delivered':
        icon = Icons.check_circle;
        color = AppColors.success;
        break;
      case 'cart_reminder':
        icon = Icons.shopping_cart;
        color = Colors.deepOrange;
        break;
      case 'flash_offer':
        icon = Icons.flash_on;
        color = AppColors.warning;
        break;
      default:
        icon = Icons.notifications;
        color = AppColors.primary;
    }

    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        shape: BoxShape.circle,
      ),
      child: Icon(
        icon,
        color: color,
        size: 24,
      ),
    );
  }

  void _handleNotificationTap(Map<String, dynamic> notification) {
    final data = notification['data'] as Map<String, dynamic>?;
    if (data == null) return;

    final type = data['type'] as String?;

    switch (type) {
      case 'price_drop':
      case 'back_in_stock':
      case 'flash_offer':
        // Navigator vers le produit
        final productId = data['product_id'];
        if (productId != null) {
          // TODO: Navigator vers ProductDetailsScreen
          print('🔔 Navigation vers produit #$productId');
        }
        break;

      case 'order_shipped':
      case 'order_delivered':
        // Navigator vers la commande
        final orderId = data['order_id'];
        if (orderId != null) {
          // TODO: Navigator vers OrderDetailsScreen
          print('🔔 Navigation vers commande #$orderId');
        }
        break;

      case 'cart_reminder':
        // Navigator vers le panier
        Navigator.pushNamed(context, '/cart');
        break;
    }
  }
}

