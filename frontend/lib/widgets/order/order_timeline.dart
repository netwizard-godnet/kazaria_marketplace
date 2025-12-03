import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../models/order_model.dart';

/// 📅 Widget de timeline pour afficher l'état d'une commande
class OrderTimeline extends StatelessWidget {
  final OrderModel order;

  const OrderTimeline({
    super.key,
    required this.order,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        boxShadow: AppShadows.shadowLG,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.timeline,
                color: AppColors.primary,
                size: 24,
              ),
              const SizedBox(width: 12),
              Text(
                'Suivi de commande',
                style: AppTextStyles.h3.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppColors.textDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          
          // Timeline steps
          _buildTimelineStep(
            icon: Icons.check_circle,
            title: 'Commande confirmée',
            date: order.createdAt,
            isCompleted: true,
            isFirst: true,
          ),
          
          _buildTimelineStep(
            icon: Icons.payment,
            title: 'Paiement',
            subtitle: _getPaymentStatusLabel(order.paymentStatus),
            date: order.paidAt,
            isCompleted: order.paymentStatus == 'paid',
          ),
          
          _buildTimelineStep(
            icon: Icons.inventory_2,
            title: 'En préparation',
            date: null,
            isCompleted: _isProcessingOrLater(order.status),
          ),
          
          _buildTimelineStep(
            icon: Icons.local_shipping,
            title: 'Expédiée',
            date: order.shippedAt,
            isCompleted: _isShippedOrLater(order.status),
          ),
          
          _buildTimelineStep(
            icon: Icons.home,
            title: 'Livrée',
            date: order.deliveredAt,
            isCompleted: order.status == 'delivered',
            isLast: true,
          ),
        ],
      ),
    );
  }

  Widget _buildTimelineStep({
    required IconData icon,
    required String title,
    String? subtitle,
    DateTime? date,
    required bool isCompleted,
    bool isFirst = false,
    bool isLast = false,
  }) {
    final bool isCurrent = isCompleted && !_isNextStepCompleted(title);
    
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Indicateur vertical
          SizedBox(
            width: 40,
            child: Column(
              children: [
                // Ligne du haut (invisible si premier)
                if (!isFirst)
                  Expanded(
                    child: Container(
                      width: 2,
                      color: isCompleted 
                          ? AppColors.success 
                          : AppColors.grey300,
                    ),
                  ),
                
                // Icône
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: isCompleted 
                        ? (isCurrent ? AppColors.primary : AppColors.success)
                        : AppColors.grey200,
                    border: Border.all(
                      color: isCompleted 
                          ? (isCurrent ? AppColors.primary : AppColors.success)
                          : AppColors.grey300,
                      width: 2,
                    ),
                  ),
                  child: Icon(
                    icon,
                    color: isCompleted ? AppColors.white : AppColors.grey400,
                    size: 20,
                  ),
                ),
                
                // Ligne du bas (invisible si dernier)
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      color: isCompleted 
                          ? AppColors.success 
                          : AppColors.grey300,
                    ),
                  ),
              ],
            ),
          ),
          
          const SizedBox(width: 16),
          
          // Contenu
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: AppTextStyles.bodyLarge.copyWith(
                      fontWeight: isCurrent 
                          ? FontWeight.bold 
                          : (isCompleted ? FontWeight.w600 : FontWeight.normal),
                      color: isCompleted 
                          ? (isCurrent ? AppColors.primary : AppColors.textDark)
                          : AppColors.textMedium,
                    ),
                  ),
                  
                  if (subtitle != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textMedium,
                      ),
                    ),
                  ],
                  
                  if (date != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      _formatDate(date),
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                  ],
                  
                  // Badge "En cours" si c'est l'étape actuelle
                  if (isCurrent) ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(
                          color: AppColors.primary.withOpacity(0.3),
                        ),
                      ),
                      child: Text(
                        'En cours',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.primary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  bool _isProcessingOrLater(String status) {
    return ['processing', 'shipped', 'delivered'].contains(status);
  }

  bool _isShippedOrLater(String status) {
    return ['shipped', 'delivered'].contains(status);
  }

  bool _isNextStepCompleted(String currentTitle) {
    // Logique pour déterminer si l'étape suivante est complétée
    if (currentTitle == 'Commande confirmée') {
      return order.paymentStatus == 'paid';
    }
    if (currentTitle == 'Paiement') {
      return _isProcessingOrLater(order.status);
    }
    if (currentTitle == 'En préparation') {
      return _isShippedOrLater(order.status);
    }
    if (currentTitle == 'Expédiée') {
      return order.status == 'delivered';
    }
    return false;
  }

  String _getPaymentStatusLabel(String status) {
    switch (status) {
      case 'paid':
        return 'Payé';
      case 'pending':
        return 'En attente';
      case 'failed':
        return 'Échoué';
      default:
        return 'Non payé';
    }
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final difference = now.difference(date);

    if (difference.inDays == 0) {
      if (difference.inHours == 0) {
        return 'Il y a ${difference.inMinutes} minute${difference.inMinutes > 1 ? 's' : ''}';
      }
      return 'Il y a ${difference.inHours} heure${difference.inHours > 1 ? 's' : ''}';
    } else if (difference.inDays == 1) {
      return 'Hier à ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
    } else if (difference.inDays < 7) {
      return 'Il y a ${difference.inDays} jours';
    } else {
      return '${date.day}/${date.month}/${date.year} à ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
    }
  }
}

