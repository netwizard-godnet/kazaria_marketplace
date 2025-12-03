import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/seller_provider.dart';
import '../../services/seller_service.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../models/order_model.dart';

class SellerOrdersScreen extends StatefulWidget {
  final String? initialOrderNumber;
  
  const SellerOrdersScreen({super.key, this.initialOrderNumber});

  @override
  State<SellerOrdersScreen> createState() => _SellerOrdersScreenState();
}

class _SellerOrdersScreenState extends State<SellerOrdersScreen> {
  String? _selectedStatus;


  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadOrders();
      
      // Si un orderNumber initial est fourni, charger et afficher ses détails
      if (widget.initialOrderNumber != null) {
        Future.delayed(const Duration(milliseconds: 500), () {
          _loadAndShowOrderDetails(widget.initialOrderNumber!);
        });
      }
    });
  }

  Future<void> _loadAndShowOrderDetails(String orderNumber) async {
    try {
      final sellerService = SellerService();
      final response = await sellerService.getOrderDetails(orderNumber);

      if (response['success'] && response['order'] != null) {
        final orderDetails = response['order'];
        if (mounted) {
          _showOrderDetails(orderDetails);
        }
      }
    } catch (e) {
      print('❌ [SELLER_ORDERS] Erreur chargement détails: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur lors du chargement des détails: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _loadOrders() async {
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    await sellerProvider.loadOrders(refresh: true, status: _selectedStatus);
  }

  void _filterByStatus(String? status) {
    setState(() {
      _selectedStatus = status;
    });
    _loadOrders();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text(
          'Mes Commandes',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: Colors.black,
          ),
        ),
        centerTitle: true,
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Column(
        children: [
          // Filtres
          Container(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            color: AppColors.white,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('Toutes', null),
                  const SizedBox(width: 8),
                  _buildFilterChip('En attente', 'pending'),
                  const SizedBox(width: 8),
                  _buildFilterChip('En préparation', 'processing'),
                  const SizedBox(width: 8),
                  _buildFilterChip('Livrée', 'delivered'),
                  const SizedBox(width: 8),
                  _buildFilterChip('Annulée', 'cancelled'),
                ],
              ),
            ),
          ),
          
          Expanded(
            child: Consumer<SellerProvider>(
              builder: (context, provider, _) {
                if (provider.isLoading && provider.orders.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.orders.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.shopping_bag_outlined,
                          size: 64,
                          color: AppColors.grey400,
                        ),
                        const SizedBox(height: AppSizes.space4),
                        Text(
                          'Aucune commande',
                          style: AppTextStyles.h3.copyWith(
                            color: AppColors.textMuted,
                          ),
                        ),
                        const SizedBox(height: AppSizes.space2),
                        Text(
                          'Les commandes de vos produits apparaîtront ici',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.textLight,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: _loadOrders,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppSizes.paddingMedium),
                    itemCount: provider.orders.length,
                    itemBuilder: (context, index) {
                      final order = provider.orders[index];
                      return _buildOrderCard(order, provider);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String? value) {
    final isSelected = _selectedStatus == value;
    return GestureDetector(
      onTap: () => _filterByStatus(value),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : AppColors.grey200,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? AppColors.white : AppColors.textDark,
            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
          ),
        ),
      ),
    );
  }

  Widget _buildOrderCard(dynamic order, SellerProvider provider) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space3),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
        boxShadow: AppShadows.shadowSM,
      ),
      child: Column(
        children: [
          // En-tête de la commande
          Container(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: _getStatusColor(order.status).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(AppSizes.radiusSmall),
                  ),
                  child: Icon(
                    _getStatusIcon(order.status),
                    color: _getStatusColor(order.status),
                    size: 20,
                  ),
                ),
                const SizedBox(width: AppSizes.space3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Commande #${order.orderNumber ?? order.id}',
                        style: AppTextStyles.bodyLarge.copyWith(
                          fontWeight: FontWeight.w600,
                          color: AppColors.textDark,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Client: ${order.shippingName ?? 'Non spécifié'}',
                        style: AppTextStyles.bodySmall.copyWith(
                          color: AppColors.textMedium,
                        ),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      _formatPrice(order.total),
                      style: AppTextStyles.bodyLarge.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppColors.primary,
                      ),
                    ),
                    const SizedBox(height: 4),
                  Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: _getOrderStatusColor(order.status).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        _getOrderStatusLabel(order.status),
                        style: AppTextStyles.caption.copyWith(
                          color: _getOrderStatusColor(order.status),
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          
          // Détails de la commande
          Container(
            padding: const EdgeInsets.fromLTRB(
              AppSizes.paddingMedium,
              0,
              AppSizes.paddingMedium,
              AppSizes.paddingMedium,
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.shopping_bag_outlined,
                      size: 16,
                      color: AppColors.textLight,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      '${order.items?.length ?? 0 ?? 0} article(s)',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                    const Spacer(),
                    Icon(
                      _getPaymentMethodIcon(order.paymentMethod),
                      size: 16,
                      color: AppColors.primary,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      _getPaymentMethodLabel(order.paymentMethod),
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.primary,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppSizes.space3),
                
                // Actions
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _showOrderDetails(order),
                        icon: const Icon(Icons.visibility_outlined, size: 16),
                        label: const Text('Détails'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.primary,
                          side: const BorderSide(color: AppColors.primary),
                          padding: const EdgeInsets.symmetric(vertical: 8),
                        ),
                      ),
                    ),
                    const SizedBox(width: AppSizes.space2),
                    if (order.status == 'pending' || order.status == 'paid')
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () => _updateOrderStatus(order, provider),
                          icon: const Icon(Icons.check_circle_outline, size: 16),
                          label: const Text('Traiter'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.success,
                            foregroundColor: AppColors.white,
                            padding: const EdgeInsets.symmetric(vertical: 8),
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

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'pending': return AppColors.warning;
      case 'paid': return AppColors.success;
      case 'shipped': return AppColors.info;
      case 'delivered': return AppColors.success;
      case 'cancelled': return AppColors.error;
      default: return AppColors.warning;
    }
  }

  IconData _getStatusIcon(String? status) {
    switch (status) {
      case 'pending': return Icons.pending_outlined;
      case 'paid': return Icons.check_circle_outline;
      case 'shipped': return Icons.local_shipping_outlined;
      case 'delivered': return Icons.done_all;
      case 'cancelled': return Icons.cancel_outlined;
      default: return Icons.pending_outlined;
    }
  }

  String _getPaymentStatusLabel(String? status) {
    switch (status) {
      case 'pending': return 'En attente de paiement';
      case 'paid': return 'Payée';
      case 'failed': return 'Échec du paiement';
      case 'refunded': return 'Remboursée';
      default: return 'En attente de paiement';
    }
  }

  Color _getPaymentStatusColor(String? status) {
    switch (status) {
      case 'pending': return AppColors.warning;
      case 'paid': return AppColors.success;
      case 'failed': return AppColors.error;
      case 'refunded': return AppColors.info;
      default: return AppColors.warning;
    }
  }

  String _getPaymentMethodLabel(String? paymentMethod) {
    switch (paymentMethod) {
      case 'cash_on_delivery': return 'À la livraison';
      case 'mobile_money': return 'Mobile Money';
      case 'card': return 'Carte bancaire';
      case 'bank_transfer': return 'Virement bancaire';
      default: return 'Non spécifié';
    }
  }

  IconData _getPaymentMethodIcon(String? paymentMethod) {
    switch (paymentMethod) {
      case 'cash_on_delivery': return Icons.local_shipping;
      case 'mobile_money': return Icons.phone_android;
      case 'card': return Icons.credit_card;
      case 'bank_transfer': return Icons.account_balance;
      default: return Icons.payment;
    }
  }

  String _formatPrice(dynamic price) {
    if (price == null) return '0 FCFA';
    
    if (price is String) {
      try {
        final doubleValue = double.parse(price);
        return '${doubleValue.toStringAsFixed(0)} FCFA';
      } catch (e) {
        return '$price FCFA';
      }
    } else if (price is double || price is int) {
      return '${price.toStringAsFixed(0)} FCFA';
    }
    
    return '0 FCFA';
  }

  // Helper function pour obtenir une valeur depuis un Map ou OrderModel
  dynamic _getOrderValue(dynamic order, String key) {
    if (order is Map) {
      return order[key] ?? order[key.replaceAll(RegExp(r'(.)([A-Z])'), r'$1_$2').toLowerCase()];
    }
    // Si c'est un OrderModel, utiliser la réflexion ou les getters
    try {
      switch (key) {
        case 'order_number': return order.orderNumber;
        case 'id': return order.id;
        case 'status': return order.status;
        case 'payment_status': return order.paymentStatus;
        case 'shipping_name': return order.shippingName;
        case 'shipping_phone': return order.shippingPhone;
        case 'shipping_email': return order.shippingEmail;
        case 'shipping_address': return order.shippingAddress;
        case 'payment_method': return order.paymentMethod;
        case 'total': return order.total;
        case 'items': return order.items;
        default: return null;
      }
    } catch (e) {
      return null;
    }
  }

  void _showOrderDetails(dynamic order) {
    // Normaliser l'accès aux données - order peut être un Map ou OrderModel
    final orderNumber = _getOrderValue(order, 'order_number') ?? _getOrderValue(order, 'id');
    final status = _getOrderValue(order, 'status') ?? 'pending';
    final paymentStatus = _getOrderValue(order, 'payment_status') ?? status;
    final shippingName = _getOrderValue(order, 'shipping_name');
    final shippingPhone = _getOrderValue(order, 'shipping_phone');
    final shippingEmail = _getOrderValue(order, 'shipping_email');
    final shippingAddress = _getOrderValue(order, 'shipping_address');
    final paymentMethod = _getOrderValue(order, 'payment_method');
    final total = _getOrderValue(order, 'total');
    final items = _getOrderValue(order, 'items');
    
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (_, controller) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              // Handle
              Container(
                margin: const EdgeInsets.only(top: 12),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.grey300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              
              // Header
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.05),
                  border: Border(
                    bottom: BorderSide(color: AppColors.grey200),
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.receipt_long,
                        color: Colors.white,
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Commande #${orderNumber ?? 'N/A'}',
                            style: AppTextStyles.h4.copyWith(
                              fontWeight: FontWeight.bold,
                              color: AppColors.textDark,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: _getPaymentStatusColor(paymentStatus),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              _getPaymentStatusLabel(paymentStatus),
                              style: AppTextStyles.caption.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
              ),
              
              // Content
              Expanded(
                child: SingleChildScrollView(
                  controller: controller,
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Suivi de livraison
                      _buildDeliveryTrackingFromMap({'status': status}),
                      
                      const SizedBox(height: 24),
                      const Divider(),
                      const SizedBox(height: 24),
                      
                      // Informations client
                      _buildSectionTitle('Informations de livraison', Icons.local_shipping),
                      const SizedBox(height: 16),
                      _buildInfoCard([
                        _buildInfoRow(Icons.person, 'Nom complet', shippingName?.toString() ?? 'Non spécifié'),
                        _buildInfoRow(Icons.phone, 'Téléphone', shippingPhone?.toString() ?? 'Non spécifié'),
                        if (shippingEmail != null)
                          _buildInfoRow(Icons.email, 'Email', shippingEmail.toString()),
                        _buildInfoRow(Icons.location_on, 'Adresse de livraison', 
                          shippingAddress?.toString() ?? 'Non spécifiée'),
                      ]),
                      
                      const SizedBox(height: 24),
                      const Divider(),
                      const SizedBox(height: 24),
                      
                      // Informations de paiement
                      _buildSectionTitle('Informations de paiement', Icons.payment),
                      const SizedBox(height: 16),
                      _buildInfoCard([
                        _buildInfoRow(
                          _getPaymentMethodIcon(paymentMethod?.toString()),
                          'Mode de paiement',
                          _getPaymentMethodLabel(paymentMethod?.toString()),
                        ),
                        _buildInfoRow(Icons.attach_money, 'Montant total', _formatPrice(total)),
                      ]),
                      
                      const SizedBox(height: 24),
                      const Divider(),
                      const SizedBox(height: 24),
                      
                      // Articles commandés
                      _buildSectionTitle('Articles commandés', Icons.shopping_bag),
                      const SizedBox(height: 16),
                      
                      if (items != null && items is List && items.isNotEmpty)
                        ...items.map<Widget>((item) => _buildModernOrderItem(item)).toList()
                      else
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: AppColors.grey100,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              Icon(Icons.info_outline, color: AppColors.grey600, size: 20),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Text(
                                  'Aucun détail de produit disponible',
                                  style: AppTextStyles.bodyMedium.copyWith(
                                    color: AppColors.grey600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      
                      const SizedBox(height: 32),
                      
                      // Actions de gestion
                      _buildManagementActionsFromMap({
                        'order_number': orderNumber,
                        'status': status,
                      }),
                      
                      const SizedBox(height: 20),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // Nouveaux widgets pour le design moderne
  
  Widget _buildSectionTitle(String title, IconData icon) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: AppColors.primary.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 20, color: AppColors.primary),
        ),
        const SizedBox(width: 12),
        Text(
          title,
          style: AppTextStyles.h4.copyWith(
            fontWeight: FontWeight.bold,
            color: AppColors.textDark,
          ),
        ),
      ],
    );
  }

  Widget _buildInfoCard(List<Widget> children) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.grey50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.grey200),
      ),
      child: Column(
        children: children,
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: AppColors.primary),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: AppTextStyles.caption.copyWith(
                    color: AppColors.textLight,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.textDark,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDeliveryTrackingFromMap(Map<String, dynamic> orderData) {
    final String currentStatus = orderData['status'] ?? 'pending';

    String step;
    if (currentStatus == 'delivered') {
      step = 'delivered';
    } else if (currentStatus == 'processing') {
      step = 'processing';
    } else {
      step = 'pending';
    }

    final steps = ['pending', 'processing', 'delivered'];
    final currentIndex = steps.indexOf(step);
    
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppColors.primary.withOpacity(0.05),
            AppColors.primary.withOpacity(0.02),
          ],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.primary.withOpacity(0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Suivi de livraison',
            style: AppTextStyles.h4.copyWith(
              fontWeight: FontWeight.bold,
              color: AppColors.textDark,
            ),
          ),
          const SizedBox(height: 20),
          
          Row(
            children: [
              _buildTrackingStep(
                icon: Icons.schedule,
                label: 'En attente',
                isActive: currentIndex >= 0,
                isCompleted: currentIndex > 0,
              ),
              _buildTrackingLine(isCompleted: currentIndex > 0),
              _buildTrackingStep(
                icon: Icons.inventory_2_outlined,
                label: 'En préparation',
                isActive: currentIndex >= 1,
                isCompleted: currentIndex > 1,
              ),
              _buildTrackingLine(isCompleted: currentIndex > 1),
              _buildTrackingStep(
                icon: Icons.check_circle,
                label: 'Livrée & Payée',
                isActive: currentIndex >= 2,
                isCompleted: false,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTrackingStep({
    required IconData icon,
    required String label,
    required bool isActive,
    required bool isCompleted,
  }) {
    return Column(
      children: [
        Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: isActive ? AppColors.primary : AppColors.grey300,
            shape: BoxShape.circle,
            boxShadow: isActive ? [
              BoxShadow(
                color: AppColors.primary.withOpacity(0.3),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ] : null,
          ),
          child: Icon(
            isCompleted ? Icons.check : icon,
            color: Colors.white,
            size: 20,
          ),
        ),
        const SizedBox(height: 8),
        SizedBox(
          width: 50,
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: AppTextStyles.caption.copyWith(
              color: isActive ? AppColors.primary : AppColors.textLight,
              fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
              fontSize: 10,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildTrackingLine({required bool isCompleted}) {
    return Expanded(
      child: Container(
        height: 2,
        margin: const EdgeInsets.only(bottom: 30),
        color: isCompleted ? AppColors.primary : AppColors.grey300,
      ),
    );
  }

  Widget _buildModernOrderItem(dynamic item) {
    // Helper pour extraire les valeurs d'un item (Map ou OrderItemModel)
    final productName = item is Map ? (item['product_name'] ?? '') : item.productName;
    final productImage = item is Map ? item['product_image'] : item.productImage;
    final price = item is Map ? (double.tryParse(item['price'].toString()) ?? 0.0) : item.price;
    final quantity = item is Map ? (item['quantity'] ?? 0) : item.quantity;
    final total = item is Map ? (double.tryParse(item['total'].toString()) ?? 0.0) : item.total;
    final attributes = item is Map ? (item['attributes'] is Map ? item['attributes'] as Map<String, dynamic> : null) : item.attributes;
    
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.grey200),
        boxShadow: [
          BoxShadow(
            color: AppColors.grey200.withOpacity(0.5),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Image du produit
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(8),
              color: AppColors.grey100,
            ),
            child: productImage != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      productImage.toString().startsWith('http') ? productImage.toString() : '${ApiConfig.imageBaseUrl}/${productImage}',
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return const Icon(
                          Icons.image_not_supported,
                          color: AppColors.grey400,
                          size: 24,
                        );
                      },
                    ),
                  )
                : const Icon(
                    Icons.image_not_supported,
                    color: AppColors.grey400,
                    size: 24,
                  ),
          ),
          const SizedBox(width: 12),
          
          // Détails du produit
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  productName.toString(),
                  style: AppTextStyles.bodyMedium.copyWith(
                    fontWeight: FontWeight.w600,
                    color: AppColors.textDark,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                // ✅ Afficher les spécifications/attributs pour le vendeur
                if (attributes != null && attributes.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: attributes.entries.where((entry) {
                      // Filtrer les attributs vides ou avec seulement des IDs numériques
                      final key = entry.key.toString();
                      final value = entry.value.toString();
                      // Ne pas afficher si c'est juste des chiffres (anciens attributs avec IDs)
                      if (int.tryParse(key) != null && int.tryParse(value) != null) {
                        return false;
                      }
                      return key.isNotEmpty && value.isNotEmpty;
                    }).map((entry) {
                      return Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              AppColors.success.withOpacity(0.15),
                              AppColors.success.withOpacity(0.08),
                            ],
                          ),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: AppColors.success.withOpacity(0.4),
                            width: 1.5,
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.verified,
                              size: 13,
                              color: AppColors.success,
                            ),
                            const SizedBox(width: 5),
                            Text(
                              '${entry.key}: ${entry.value}',
                              style: AppTextStyles.caption.copyWith(
                                color: AppColors.success,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ],
                const SizedBox(height: 4),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        'x$quantity',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.primary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _formatPrice(price),
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          
          // Total pour cet article
          Text(
            _formatPrice(total),
            style: AppTextStyles.bodyLarge.copyWith(
              fontWeight: FontWeight.bold,
              color: AppColors.primary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildManagementActionsFromMap(Map<String, dynamic> orderData) {
    final currentStatus = orderData['status'] ?? 'pending';
    final orderNumber = orderData['order_number'] ?? orderData['id'];
    
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.grey50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.grey200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.settings, size: 20, color: AppColors.primary),
              const SizedBox(width: 8),
              Text(
                'Gestion de la commande',
                style: AppTextStyles.h4.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppColors.textDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          
          if (currentStatus == 'pending')
            _buildActionButton(
              label: 'Marquer comme en préparation',
              icon: Icons.inventory_2_outlined,
              color: AppColors.info,
              onPressed: () {
                Navigator.pop(context);
                _performStatusUpdate(orderNumber.toString(), 'processing');
              },
            ),
          
          if (currentStatus == 'processing')
            _buildActionButton(
              label: 'Marquer comme livrée (Payée)',
              icon: Icons.done_all,
              color: AppColors.success,
              onPressed: () {
                Navigator.pop(context);
                _performStatusUpdate(orderNumber.toString(), 'delivered');
              },
            ),
          
          if (currentStatus != 'cancelled' && currentStatus != 'delivered')
            Column(
              children: [
                const SizedBox(height: 12),
                _buildActionButton(
                  label: 'Annuler la commande',
                  icon: Icons.cancel,
                  color: AppColors.error,
                  onPressed: () {
                    Navigator.pop(context);
                    _showCancelConfirmationFromMap(orderNumber.toString());
                  },
                ),
              ],
            ),
          
          if (currentStatus == 'delivered')
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.success.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  Icon(Icons.check_circle, color: AppColors.success),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Commande livrée avec succès',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: AppColors.success,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          
          if (currentStatus == 'cancelled')
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.error.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  Icon(Icons.cancel, color: AppColors.error),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Commande annulée',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: AppColors.error,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildActionButton({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onPressed,
  }) {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton.icon(
        onPressed: onPressed,
        icon: Icon(icon, size: 20),
        label: Text(label),
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: 0,
        ),
      ),
    );
  }

  void _showCancelConfirmationFromMap(String orderNumber) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Annuler la commande'),
        content: const Text(
          'Êtes-vous sûr de vouloir annuler cette commande ? Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Non, garder'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _performStatusUpdate(orderNumber, 'cancelled');
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
            ),
            child: const Text('Oui, annuler'),
          ),
        ],
      ),
    );
  }

  void _updateOrderStatus(dynamic order, SellerProvider provider) {
    final currentStatus = order.status;
    String? nextStatus;
    String actionText;

    if (currentStatus == 'pending') {
      nextStatus = 'paid';
      actionText = 'Marquer comme payée';
    } else if (currentStatus == 'paid') {
      nextStatus = 'shipped';
      actionText = 'Marquer comme expédiée';
    } else {
      return;
    }

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Mettre à jour le statut'),
        content: Text('Voulez-vous $actionText ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(context).pop();
              if (nextStatus != null) {
                await _performStatusUpdate(order, nextStatus);
              }
            },
            child: const Text('Confirmer'),
          ),
        ],
      ),
    );
  }

  Future<void> _performStatusUpdate(String orderNumber, String newStatus) async {
    final provider = Provider.of<SellerProvider>(context, listen: false);
    
    // Afficher un indicateur de chargement
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              ),
              SizedBox(width: 16),
              Text('Mise à jour en cours...'),
            ],
          ),
          duration: Duration(seconds: 2),
        ),
      );
    }
    
    // Utiliser orderNumber (String)
    final success = await provider.updateOrderStatus(orderNumber, newStatus);
    
    if (success) {
      if (mounted) {
        ScaffoldMessenger.of(context).hideCurrentSnackBar();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Row(
              children: [
                Icon(Icons.check_circle, color: Colors.white),
                SizedBox(width: 16),
                Text('Statut mis à jour avec succès'),
              ],
            ),
            backgroundColor: AppColors.success,
            duration: Duration(seconds: 2),
          ),
        );
        
        // Forcer le rechargement
        await _loadOrders();
        
        // Fermer le dialog s'il est ouvert
        if (Navigator.canPop(context)) {
          Navigator.pop(context);
        }
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).hideCurrentSnackBar();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                Icon(Icons.error, color: Colors.white),
                SizedBox(width: 16),
                Expanded(
                  child: Text(provider.error ?? 'Erreur lors de la mise à jour'),
                ),
              ],
            ),
            backgroundColor: AppColors.error,
            duration: Duration(seconds: 3),
          ),
        );
      }
    }
  }

  Color _getOrderStatusColor(String? status) {
    switch (status) {
      case 'pending':
        return AppColors.warning;
      case 'processing':
        return AppColors.info;
      case 'delivered':
        return AppColors.success;
      case 'cancelled':
        return AppColors.error;
      default:
        return AppColors.textLight;
    }
  }

  String _getOrderStatusLabel(String? status) {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'processing':
        return 'En préparation';
      case 'delivered':
        return 'Livrée & Payée';
      case 'cancelled':
        return 'Annulée';
      default:
        return 'Inconnu';
    }
  }
}

