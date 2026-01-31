import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:http/http.dart' as http;
import 'package:path_provider/path_provider.dart';
import 'package:open_file/open_file.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:share_plus/share_plus.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../models/order_model.dart';
import '../../config/api_config.dart';
import '../../widgets/order/order_timeline.dart';
import '../../services/invoice_service.dart';
import '../../services/storage_service.dart';
import '../../services/order_service.dart';

class OrderDetailsScreen extends StatefulWidget {
  final OrderModel order;

  const OrderDetailsScreen({super.key, required this.order});

  @override
  State<OrderDetailsScreen> createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends State<OrderDetailsScreen>
    with SingleTickerProviderStateMixin {
  OrderModel? _order;
  bool _isLoading = true;
  String? _error;
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  Timer? _refreshTimer;
  final InvoiceService _invoiceService = InvoiceService();
  final OrderService _orderService = OrderService(); // ✅ Service pour annulation

  String _effectivePaymentStatus(OrderModel o) {
    if (o.paymentStatus == 'paid') return 'paid';
    if ((o.status == 'delivered') && (o.paymentMethod == 'cash_on_delivery')) {
      return 'paid';
    }
    return o.paymentStatus;
  }

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    _fadeAnimation = CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    );
    _loadOrderDetails();
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadOrderDetails() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      // Charger les détails à jour depuis l'API
      final response = await _orderService.getOrderDetails(widget.order.orderNumber);
      
      if (response['success'] == true && response['data'] != null) {
        // Créer un nouveau modèle avec les données mises à jour
        final updatedOrder = OrderModel.fromJson(response['data']);
        setState(() {
          _order = updatedOrder;
      _isLoading = false;
        });
      } else {
        // Si l'API échoue, utiliser les données initiales
        setState(() {
      _order = widget.order;
          _isLoading = false;
        });
      }
    } catch (e) {
      // En cas d'erreur, utiliser les données initiales
      print('⚠️ [ORDER_DETAILS] Erreur lors du chargement: $e');
      setState(() {
        _order = widget.order;
        _isLoading = false;
      });
    }
    
    _animationController.forward();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Détails de la commande'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        elevation: 0,
        actions: [
          // 📄 Bouton télécharger la facture
          if (_order?.invoicePath != null)
            IconButton(
              icon: const Icon(Icons.download),
              onPressed: () => _downloadInvoice(),
              tooltip: 'Télécharger la facture',
            ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              // Recharger les détails de la commande depuis l'API
              _loadOrderDetails();
            },
            tooltip: 'Rafraîchir',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? _buildErrorState()
          : RefreshIndicator(
              onRefresh: () async {
                // Retourner à la liste des commandes pour recharger
                Navigator.pop(context, true);
              },
              child: _buildOrderDetails(),
            ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                color: AppColors.errorLight,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.error_outline,
                color: AppColors.error,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Erreur de chargement',
              style: AppTextStyles.headlineMedium.copyWith(
                color: AppColors.textDark,
              ),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              _error!,
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppSizes.space4),
            ElevatedButton(
              onPressed: _loadOrderDetails,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 32,
                  vertical: 16,
                ),
              ),
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOrderDetails() {
    if (_order == null) return const SizedBox.shrink();

    return FadeTransition(
      opacity: _fadeAnimation,
      child: RefreshIndicator(
        onRefresh: _loadOrderDetails,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header avec numéro et statut
              _buildOrderHeader(),

              const SizedBox(height: AppSizes.space2),

              // Suivi de commande (Timeline)
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSizes.paddingMedium,
                ),
                child: OrderTimeline(order: _order!),
              ),

              const SizedBox(height: AppSizes.space2),

              // Informations de livraison
              _buildDeliveryInfo(),

              const SizedBox(height: AppSizes.space2),

              // ✅ Bouton d'annulation (si commande en attente)
              if (_order!.status == 'pending')
                _buildCancelButton(),

              const SizedBox(height: AppSizes.space2),

              // Articles commandés
              _buildOrderItems(),

              const SizedBox(height: AppSizes.space2),

              // Résumé de paiement
              _buildPaymentSummary(),

              const SizedBox(height: AppSizes.space6),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildOrderHeader() {
    return Container(
      margin: const EdgeInsets.all(AppSizes.paddingMedium),
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.primary, AppColors.primaryLight],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withOpacity(0.3),
            blurRadius: 15,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                ),
                child: const Icon(
                  Icons.shopping_bag,
                  color: AppColors.white,
                  size: 28,
                ),
              ),
              const SizedBox(width: AppSizes.space3),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Commande',
                      style: AppTextStyles.labelLarge.copyWith(
                        color: AppColors.white.withOpacity(0.9),
                      ),
                    ),
                    Text(
                      '#${_order!.orderNumber}',
                      style: AppTextStyles.headlineMedium.copyWith(
                        color: AppColors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
              _buildStatusBadge(),
            ],
          ),
          const SizedBox(height: AppSizes.space3),
          Divider(color: AppColors.white.withOpacity(0.3)),
          const SizedBox(height: AppSizes.space3),
          Row(
            children: [
              Icon(
                Icons.calendar_today,
                color: AppColors.white.withOpacity(0.9),
                size: 18,
              ),
              const SizedBox(width: 8),
              Text(
                Helpers.formatDateTime(_order!.createdAt ?? DateTime.now()),
                style: AppTextStyles.bodyMedium.copyWith(
                  color: AppColors.white.withOpacity(0.9),
                ),
              ),
              const Spacer(),
              Icon(
                Icons.account_balance_wallet,
                color: AppColors.white.withOpacity(0.9),
                size: 18,
              ),
              const SizedBox(width: 8),
              Text(
                _getPaymentMethodLabel(_order!.paymentMethod),
                style: AppTextStyles.bodyMedium.copyWith(
                  color: AppColors.white.withOpacity(0.9),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge() {
    final statusColor = _getStatusColor(_order!.status);
    final statusIcon = _getStatusIcon(_order!.status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(statusIcon, color: statusColor, size: 16),
          const SizedBox(width: 4),
          Text(
            _order!.statusLabel,
            style: AppTextStyles.labelMedium.copyWith(
              color: statusColor,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  // Méthode supprimée - Remplacée par le widget OrderTimeline

  Widget _buildDeliveryInfo() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: AppSizes.paddingMedium),
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                ),
                child: const Icon(
                  Icons.location_on,
                  color: AppColors.primary,
                  size: 24,
                ),
              ),
              const SizedBox(width: AppSizes.space3),
              Text(
                'Adresse de livraison',
                style: AppTextStyles.headlineSmall.copyWith(
                  color: AppColors.textDark,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSizes.space3),
          Container(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            decoration: BoxDecoration(
              color: AppColors.background,
              borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(
                      Icons.person,
                      color: AppColors.textMedium,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _order!.shippingName.isNotEmpty
                          ? _order!.shippingName
                          : 'Non spécifié',
                      style: AppTextStyles.bodyLarge.copyWith(
                        fontWeight: FontWeight.w600,
                        color: AppColors.textDark,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(
                      Icons.phone,
                      color: AppColors.textMedium,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _order!.shippingPhone.isNotEmpty
                          ? _order!.shippingPhone
                          : 'Non spécifié',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: AppColors.textMedium,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(
                      Icons.home,
                      color: AppColors.textMedium,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        '${_order!.shippingAddress.isNotEmpty ? _order!.shippingAddress : 'Non spécifié'}, ${_order!.shippingCity}',
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: AppColors.textMedium,
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
    );
  }

  Widget _buildOrderItems() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: AppSizes.paddingMedium),
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.secondary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                ),
                child: const Icon(
                  Icons.inventory_2,
                  color: AppColors.secondary,
                  size: 24,
                ),
              ),
              const SizedBox(width: AppSizes.space3),
              Text(
                'Articles (${_order!.items?.length ?? 0})',
                style: AppTextStyles.headlineSmall.copyWith(
                  color: AppColors.textDark,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSizes.space4),
          ...(_order!.items ?? [])
              .map((item) => _buildOrderItem(item))
              .toList(),
        ],
      ),
    );
  }

  Widget _buildOrderItem(OrderItemModel item) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
      padding: const EdgeInsets.all(AppSizes.paddingMedium),
      decoration: BoxDecoration(
        color: AppColors.background,
        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
        border: Border.all(color: AppColors.grey200),
      ),
      child: Row(
        children: [
          // Image du produit
          ClipRRect(
            borderRadius: BorderRadius.circular(AppSizes.radiusSmall),
            child: CachedNetworkImage(
              imageUrl:
                  item.productImage ??
                  '', // ✅ product_image contient déjà l'URL complète
              width: 60,
              height: 60,
              fit: BoxFit.contain, // ✅ Contain au lieu de cover
              memCacheWidth: 120,
              memCacheHeight: 120,
              placeholder: (context, url) => Container(
                width: 60,
                height: 60,
                color: AppColors.grey200,
                child: const Icon(Icons.image, color: AppColors.grey400),
              ),
              errorWidget: (context, url, error) => Container(
                width: 60,
                height: 60,
                color: AppColors.grey200,
                child: const Icon(Icons.broken_image, color: AppColors.grey400),
              ),
            ),
          ),
          const SizedBox(width: AppSizes.space3),
          // Informations du produit
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.productName,
                  style: AppTextStyles.bodyLarge.copyWith(
                    fontWeight: FontWeight.w600,
                    color: AppColors.textDark,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                // ✅ Afficher les attributs sélectionnés
                if (item.attributes != null && item.attributes!.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: item.attributes!.entries
                        .where((entry) {
                          // Filtrer les attributs vides ou avec seulement des IDs numériques
                          final key = entry.key.toString();
                          final value = entry.value.toString();
                          // Ne pas afficher si c'est juste des chiffres (anciens attributs avec IDs)
                          if (int.tryParse(key) != null &&
                              int.tryParse(value) != null) {
                            return false;
                          }
                          return key.isNotEmpty && value.isNotEmpty;
                        })
                        .map((entry) {
                          return Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  AppColors.primary.withOpacity(0.15),
                                  AppColors.primary.withOpacity(0.08),
                                ],
                              ),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(
                                color: AppColors.primary.withOpacity(0.4),
                                width: 1,
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.check_circle_outline,
                                  size: 12,
                                  color: AppColors.primary,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  '${entry.key}: ${entry.value}',
                                  style: AppTextStyles.caption.copyWith(
                                    color: AppColors.primary,
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          );
                        })
                        .toList(),
                  ),
                ],
                const SizedBox(height: 4),
                Row(
                  children: [
                    Text(
                      'Qté: ${item.quantity}',
                      style: AppTextStyles.bodySmall.copyWith(
                        color: AppColors.textMedium,
                      ),
                    ),
                    const SizedBox(width: 8), // ✅ Réduit de 12 à 8
                    Text(
                      '×',
                      style: AppTextStyles.bodySmall.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                    const SizedBox(width: 8), // ✅ Réduit de 12 à 8
                    Flexible( // ✅ Utiliser Flexible pour éviter l'overflow
                      child: FittedBox( // ✅ Utiliser FittedBox pour réduire automatiquement si nécessaire
                        fit: BoxFit.scaleDown,
                        alignment: Alignment.centerLeft,
                        child: Text(
                          Helpers.formatPrice(item.price),
                          style: AppTextStyles.bodySmall.copyWith(
                            color: AppColors.textMedium,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          // Prix total
          Text(
            Helpers.formatPrice(item.price * item.quantity),
            style: AppTextStyles.titleMedium.copyWith(
              color: AppColors.primary,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentSummary() {
    final subtotal = _order!.total;
    const shipping = 0.0; // À adapter selon vos besoins
    final total = _order!.total;
    final paymentStatus = _effectivePaymentStatus(_order!);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: AppSizes.paddingMedium),
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.accent.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                ),
                child: const Icon(
                  Icons.receipt_long,
                  color: AppColors.accent,
                  size: 24,
                ),
              ),
              const SizedBox(width: AppSizes.space3),
              Text(
                'Résumé du paiement',
                style: AppTextStyles.headlineSmall.copyWith(
                  color: AppColors.textDark,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSizes.space4),
          _buildPriceRow('Sous-total', subtotal),
          const SizedBox(height: AppSizes.space2),
          _buildPriceRow('Frais de livraison', shipping),
          const SizedBox(height: AppSizes.space3),
          Divider(color: AppColors.grey300),
          const SizedBox(height: AppSizes.space3),
          Row(
            children: [
              Text(
                'Total',
                style: AppTextStyles.headlineMedium.copyWith(
                  color: AppColors.textDark,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const Spacer(),
              Text(
                Helpers.formatPrice(total),
                style: AppTextStyles.headlineMedium.copyWith(
                  color: AppColors.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSizes.space3),
          Container(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            decoration: BoxDecoration(
              color: _getPaymentStatusColor(paymentStatus).withOpacity(0.1),
              borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
              border: Border.all(
                color: _getPaymentStatusColor(paymentStatus).withOpacity(0.3),
              ),
            ),
            child: Row(
              children: [
                Icon(
                  _getPaymentStatusIcon(paymentStatus),
                  color: _getPaymentStatusColor(paymentStatus),
                  size: 20,
                ),
                const SizedBox(width: 8),
                Text(
                  'Statut: ${_getPaymentStatusLabel(paymentStatus)}',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: _getPaymentStatusColor(paymentStatus),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPriceRow(String label, double price) {
    return Row(
      children: [
        Text(
          label,
          style: AppTextStyles.bodyLarge.copyWith(color: AppColors.textMedium),
        ),
        const Spacer(),
        Text(
          Helpers.formatPrice(price),
          style: AppTextStyles.bodyLarge.copyWith(
            color: AppColors.textDark,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }

  // Helper methods
  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return AppColors.warning;
      case 'processing':
        return AppColors.info;
      case 'shipped':
        return AppColors.secondary;
      case 'delivered':
        return AppColors.success;
      case 'cancelled':
        return AppColors.error;
      default:
        return AppColors.textLight;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'pending':
        return Icons.schedule;
      case 'processing':
        return Icons.autorenew;
      case 'shipped':
        return Icons.local_shipping;
      case 'delivered':
        return Icons.check_circle;
      case 'cancelled':
        return Icons.cancel;
      default:
        return Icons.info;
    }
  }

  Color _getPaymentStatusColor(String? status) {
    switch (status) {
      case 'paid':
        return AppColors.success;
      case 'pending':
        return AppColors.warning;
      case 'failed':
        return AppColors.error;
      default:
        return AppColors.textLight;
    }
  }

  IconData _getPaymentStatusIcon(String? status) {
    switch (status) {
      case 'paid':
        return Icons.check_circle;
      case 'pending':
        return Icons.schedule;
      case 'failed':
        return Icons.error;
      default:
        return Icons.help_outline;
    }
  }

  String _getPaymentStatusLabel(String? status) {
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

  String _getPaymentMethodLabel(String? method) {
    switch (method) {
      case 'mobile_money':
        return 'Mobile Money';
      case 'cash_on_delivery':
        return 'À la livraison';
      case 'card':
        return 'Carte bancaire';
      default:
        return 'Non spécifié';
    }
  }

  /// 📄 Télécharger la facture PDF avec cache et partage
  Future<void> _downloadInvoice() async {
    if (_order == null) return;

    try {
      // Obtenir le répertoire de téléchargement
      Directory? directory;

      if (Platform.isAndroid) {
        var status = await Permission.storage.status;
        if (!status.isGranted) {
          status = await Permission.storage.request();
        }

        if (status.isGranted) {
          directory = await getExternalStorageDirectory();
        } else {
          directory = await getApplicationDocumentsDirectory();
        }
      } else {
        directory = await getApplicationDocumentsDirectory();
      }

      if (directory == null) {
        throw 'Impossible d\'accéder au stockage';
      }

      final fileName = 'facture-${_order!.orderNumber}.pdf';
      final filePath = '${directory.path}/$fileName';
      final file = File(filePath);

      // 💾 Vérifier le cache via le service
      final cachedPath = await _invoiceService.getCachedInvoice(
        _order!.orderNumber,
      );

      if (cachedPath != null && await File(cachedPath).exists()) {
        print('💾 [INVOICE] Facture trouvée dans le cache');

        // Demander à l'utilisateur ce qu'il veut faire
        final action = await showDialog<String>(
          context: context,
          builder: (context) => AlertDialog(
            title: Row(
              children: [
                Icon(Icons.file_present, color: AppColors.success),
                const SizedBox(width: 12),
                const Expanded(child: Text('Facture disponible')),
              ],
            ),
            content: const Text(
              'Cette facture a déjà été téléchargée. Que voulez-vous faire ?',
            ),
            actions: [
              TextButton.icon(
                icon: const Icon(Icons.refresh),
                label: const Text('Re-télécharger'),
                onPressed: () => Navigator.pop(context, 'redownload'),
              ),
              TextButton.icon(
                icon: const Icon(Icons.share),
                label: const Text('Partager'),
                onPressed: () => Navigator.pop(context, 'share'),
              ),
              ElevatedButton.icon(
                icon: const Icon(Icons.open_in_new),
                label: const Text('Ouvrir'),
                onPressed: () => Navigator.pop(context, 'open'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ),
        );

        if (action == 'open') {
          await OpenFile.open(cachedPath);
          return;
        } else if (action == 'share') {
          await _shareInvoice(cachedPath);
          return;
        } else if (action != 'redownload') {
          return; // Annulé
        }

        // Si re-téléchargement, supprimer l'ancien fichier
        await File(cachedPath).delete();
      }

      // 📥 Afficher un dialog de progression
      if (mounted) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => WillPopScope(
            onWillPop: () async => false,
            child: AlertDialog(
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const CircularProgressIndicator(),
                  const SizedBox(height: 16),
                  const Text(
                    'Téléchargement de la facture...',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Veuillez patienter',
                    style: TextStyle(fontSize: 12, color: AppColors.textLight),
                  ),
                ],
              ),
            ),
          ),
        );
      }

      final invoiceUrl =
          '${ApiConfig.baseUrl}/orders/${_order!.orderNumber}/invoice/download';

      print('📄 [INVOICE] Téléchargement depuis: $invoiceUrl');

      final storageService = StorageService();
      final token = await storageService.getToken();

      final headers = {
        'Accept': 'application/pdf',
      };

      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.get(
        Uri.parse(invoiceUrl),
        headers: headers,
      );

      if (response.statusCode == 200) {
        // Sauvegarder le fichier
        await file.writeAsBytes(response.bodyBytes);
        print('✅ [INVOICE] Facture sauvegardée: $filePath');

        // 📝 Enregistrer dans l'historique
        await _invoiceService.recordDownload(_order!.orderNumber, filePath);

        if (mounted) {
          // Fermer le dialog de progression
          Navigator.of(context, rootNavigator: true).pop();

          // Demander quoi faire avec la facture
          final result = await showDialog<String>(
            context: context,
            builder: (context) => AlertDialog(
              title: Row(
                children: [
                  Icon(Icons.check_circle, color: AppColors.success),
                  const SizedBox(width: 12),
                  const Text('Facture téléchargée !'),
                ],
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Que voulez-vous faire ?'),
                  const SizedBox(height: 12),
                  Text(
                    'Emplacement: ${directory!.path}',
                    style: TextStyle(fontSize: 11, color: AppColors.textLight),
                  ),
                ],
              ),
              actions: [
                TextButton.icon(
                  icon: const Icon(Icons.share),
                  label: const Text('Partager'),
                  onPressed: () => Navigator.pop(context, 'share'),
                ),
                ElevatedButton.icon(
                  icon: const Icon(Icons.open_in_new),
                  label: const Text('Ouvrir'),
                  onPressed: () => Navigator.pop(context, 'open'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                  ),
                ),
              ],
            ),
          );

          if (result == 'open') {
            await OpenFile.open(filePath);
          } else if (result == 'share') {
            await _shareInvoice(filePath);
          }
        }
      } else if (response.statusCode == 401) {
        throw 'Email requis pour télécharger la facture';
      } else if (response.statusCode == 403) {
        throw 'Email incorrect';
      } else if (response.statusCode == 404) {
        throw 'Commande non trouvée';
      } else if (response.statusCode == 429) {
        throw 'Trop de tentatives. Attendez quelques instants.';
      } else {
        throw 'Erreur serveur: ${response.statusCode}';
      }
    } catch (e) {
      print('❌ [INVOICE] Erreur: $e');

      if (mounted) {
        // Fermer le dialog de progression si ouvert
        Navigator.of(context, rootNavigator: true).pop();

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error_outline, color: Colors.white),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Erreur: $e',
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
              ],
            ),
            backgroundColor: AppColors.error,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    }
  }

  /// 📤 Partager la facture PDF
  Future<void> _shareInvoice(String filePath) async {
    try {
      final file = XFile(filePath);

      await Share.shareXFiles(
        [file],
        subject: 'Facture ${_order!.orderNumber}',
        text: 'Voici la facture de ma commande ${_order!.orderNumber}',
      );

      print('✅ [INVOICE] Facture partagée');

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white),
                const SizedBox(width: 12),
                const Text('Facture prête à être partagée'),
              ],
            ),
            backgroundColor: AppColors.success,
            duration: const Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      print('❌ [INVOICE] Erreur partage: $e');

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur lors du partage: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  /// 🚫 Annuler une commande
  Future<void> _cancelOrder() async {
    // Confirmer l'annulation
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: AppColors.error),
            const SizedBox(width: 12),
            const Text('Annuler la commande ?'),
          ],
        ),
        content: const Text(
          'Êtes-vous sûr de vouloir annuler cette commande ? '
          'Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Non'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
            ),
            child: const Text('Oui, annuler'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    // Afficher un loader
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(
        child: CircularProgressIndicator(),
      ),
    );

    try {
      final response = await _orderService.cancelOrder(_order!.orderNumber);

      if (mounted) {
        Navigator.pop(context); // Fermer le loader

        if (response['success']) {
          // Succès
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: const [
                  Icon(Icons.check_circle, color: Colors.white),
                  SizedBox(width: 12),
                  Expanded(
                    child: Text('Commande annulée avec succès'),
                  ),
                ],
              ),
              backgroundColor: AppColors.success,
              duration: const Duration(seconds: 3),
            ),
          );

          // Retourner à la liste des commandes (avec refresh)
          Navigator.pop(context, true);
        } else {
          // Erreur
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                response['message'] ?? 'Impossible d\'annuler cette commande',
              ),
              backgroundColor: AppColors.error,
              duration: const Duration(seconds: 4),
            ),
          );
        }
      }
    } catch (e) {
      print('❌ [ORDER_CANCEL] Erreur: $e');
      
      if (mounted) {
        Navigator.pop(context); // Fermer le loader
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: AppColors.error,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    }
  }

  /// 🚫 Bouton d'annulation de commande
  Widget _buildCancelButton() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSizes.paddingMedium),
      child: Container(
        width: double.infinity,
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          boxShadow: AppShadows.shadowSM,
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: _cancelOrder,
            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
            child: Padding(
              padding: const EdgeInsets.all(AppSizes.paddingMedium),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.error.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                    ),
                    child: Icon(
                      Icons.cancel_outlined,
                      color: AppColors.error,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: AppSizes.space3),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Annuler la commande',
                          style: AppTextStyles.bodyLarge.copyWith(
                            fontWeight: FontWeight.w600,
                            color: AppColors.error,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Vous pouvez annuler tant que la commande n\'est pas validée',
                          style: AppTextStyles.bodySmall.copyWith(
                            color: AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Icon(
                    Icons.arrow_forward_ios,
                    color: AppColors.error,
                    size: 16,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
