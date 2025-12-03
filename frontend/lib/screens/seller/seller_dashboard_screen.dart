import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/seller_provider.dart';
import '../../providers/auth_provider.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../services/auth_service.dart';
import '../../services/seller_service.dart';
import 'seller_products_screen.dart';
import 'seller_orders_screen.dart';
import 'seller_store_settings_complete_screen.dart';
import 'seller_register_screen.dart';

class SellerDashboardScreen extends StatefulWidget {
  const SellerDashboardScreen({super.key});

  @override
  State<SellerDashboardScreen> createState() => _SellerDashboardScreenState();
}

class _SellerDashboardScreenState extends State<SellerDashboardScreen> {
  bool _isCheckingStore = true;
  bool _hasStore = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkSellerStatus();
    });
  }

  Future<void> _checkSellerStatus() async {
    setState(() => _isCheckingStore = true);
    
    try {
      // Vérifier le statut vendeur et si la boutique existe
      final authService = AuthService();
      final statusResponse = await authService.checkSellerStatus();
      
      if (statusResponse['success'] == true) {
        final isSeller = statusResponse['is_seller'] ?? false;
        final hasStore = statusResponse['has_store'] ?? false;
        
        print('🔍 [SELLER_DASHBOARD] Statut vendeur: isSeller=$isSeller, hasStore=$hasStore');
        
        if (!mounted) return;
        
        if (!isSeller) {
          // L'utilisateur n'est pas vendeur, rediriger vers l'inscription vendeur
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (_) => const SellerRegisterScreen(),
            ),
          );
          return;
        }
        
        if (!hasStore) {
          // L'utilisateur est vendeur mais n'a pas de boutique, rediriger vers la création
          setState(() {
            _isCheckingStore = false;
            _hasStore = false;
          });
          
          // Afficher un message et rediriger
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: const Text('Vous devez créer une boutique pour accéder au dashboard'),
                backgroundColor: AppColors.warning,
                action: SnackBarAction(
                  label: 'Créer',
                  textColor: Colors.white,
                  onPressed: () {
                    Navigator.of(context).pushReplacement(
                      MaterialPageRoute(
                        builder: (_) => const SellerRegisterScreen(),
                      ),
                    );
                  },
                ),
              ),
            );
            
            // Rediriger automatiquement après un court délai
            Future.delayed(const Duration(seconds: 2), () {
              if (mounted) {
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(
                    builder: (_) => const SellerRegisterScreen(),
                  ),
                );
              }
            });
          }
          return;
        }
        
        // L'utilisateur a une boutique, charger les données
        setState(() {
          _hasStore = true;
          _isCheckingStore = false;
        });
        await _loadData();
      }
    } catch (e) {
      print('❌ [SELLER_DASHBOARD] Erreur vérification statut: $e');
      if (mounted) {
        setState(() => _isCheckingStore = false);
      }
    }
  }

  Future<void> _loadData() async {
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    await sellerProvider.loadStats();
    await sellerProvider.loadStoreInfo();
    await sellerProvider.loadRecentOrders(); // Charger les commandes récentes
  }

  Future<void> _refresh() async {
    await _loadData();
  }

  @override
  Widget build(BuildContext context) {
    // Afficher un loader pendant la vérification du statut
    if (_isCheckingStore) {
      return Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: const Text(
            'Dashboard Vendeur',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black,
            ),
          ),
          centerTitle: true,
          elevation: 0,
          backgroundColor: Colors.white,
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }
    
    // Si l'utilisateur n'a pas de boutique, afficher un message
    if (!_hasStore) {
      return Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: const Text(
            'Dashboard Vendeur',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black,
            ),
          ),
          centerTitle: true,
          elevation: 0,
          backgroundColor: Colors.white,
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(AppSizes.paddingLarge),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.store_outlined,
                  size: 80,
                  color: AppColors.textMuted,
                ),
                const SizedBox(height: AppSizes.space6),
                Text(
                  'Aucune boutique',
                  style: AppTextStyles.h2.copyWith(
                    color: AppColors.textDark,
                  ),
                ),
                const SizedBox(height: AppSizes.space3),
                Text(
                  'Vous devez créer une boutique pour accéder au dashboard',
                  textAlign: TextAlign.center,
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.textMuted,
                  ),
                ),
                const SizedBox(height: AppSizes.space6),
                ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).pushReplacement(
                      MaterialPageRoute(
                        builder: (_) => const SellerRegisterScreen(),
                      ),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSizes.paddingLarge,
                      vertical: AppSizes.space4,
                    ),
                  ),
                  child: const Text('Créer ma boutique'),
                ),
              ],
            ),
          ),
        ),
      );
    }
    
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text(
          'Dashboard Vendeur',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: Colors.black,
          ),
        ),
        centerTitle: true,
        elevation: 0,
        backgroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.black),
            onPressed: () async {
              final authProvider = Provider.of<AuthProvider>(context, listen: false);
              await authProvider.logout();
              if (!mounted) return;
              Navigator.of(context).pushReplacementNamed('/login');
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: Consumer<SellerProvider>(
          builder: (context, sellerProvider, child) {
            if (sellerProvider.isLoading && sellerProvider.stats == null) {
              return const Center(child: CircularProgressIndicator());
            }

            return SingleChildScrollView(
              padding: const EdgeInsets.all(AppSizes.paddingMedium),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // En-tête boutique
                  _buildStoreHeader(sellerProvider),
                  
                  const SizedBox(height: AppSizes.space6),
                  
                  // Statistiques
                  _buildStats(sellerProvider),
                  
                  const SizedBox(height: AppSizes.space6),
                  
                  // Actions rapides
                  _buildQuickActions(),
                  
                  const SizedBox(height: AppSizes.space6),
                  
                  // Commandes récentes
                  _buildRecentOrders(),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildStoreHeader(SellerProvider sellerProvider) {
    final storeInfo = sellerProvider.storeInfo;
    final isLoading = sellerProvider.isLoading && storeInfo == null;
    
    return Container(
      padding: const EdgeInsets.all(AppSizes.paddingMedium),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppColors.primary,
            AppColors.primaryLight,
          ],
        ),
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        children: [
          Row(
            children: [
              // Logo de la boutique
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  boxShadow: AppShadows.shadowSM,
                ),
                child: isLoading
                    ? _buildSkeletonBox(60, 60)
                    : storeInfo?['logo'] != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                            child: Image.network(
                              '${ApiConfig.imageBaseUrl}/${storeInfo!['logo']}',
                              fit: BoxFit.cover,
                            ),
                          )
                        : const Icon(
                            Icons.store,
                            size: 30,
                            color: AppColors.primary,
                          ),
              ),
              
              const SizedBox(width: AppSizes.space3),
              
              // Infos boutique
              Expanded(
                child: isLoading
                    ? Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildSkeletonBox(150, 20),
                          const SizedBox(height: 8),
                          _buildSkeletonBox(100, 14),
                        ],
                      )
                    : Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            storeInfo?['name'] ?? 'Ma Boutique',
                            style: AppTextStyles.h3.copyWith(
                              color: AppColors.white,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              if (storeInfo?['is_verified'] == true) ...[
                                const Icon(
                                  Icons.verified,
                                  size: 16,
                                  color: AppColors.white,
                                ),
                                const SizedBox(width: 4),
                              ],
                              Text(
                                storeInfo?['is_verified'] == true ? 'Vérifié' : 'Non vérifié',
                                style: AppTextStyles.caption.copyWith(
                                  color: AppColors.white.withOpacity(0.9),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
              ),
              
              // Bouton paramètres
              IconButton(
                icon: const Icon(
                  Icons.settings,
                  color: AppColors.white,
                ),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const SellerStoreSettingsCompleteScreen(),
                    ),
                  );
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStats(SellerProvider sellerProvider) {
    final stats = sellerProvider.stats;
    final isLoading = sellerProvider.isLoading && stats == null;
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Statistiques',
          style: AppTextStyles.h3.copyWith(
            color: AppColors.textDark,
            fontWeight: FontWeight.w600,
          ),
        ),
        
        const SizedBox(height: AppSizes.space3),
        
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                title: 'Produits',
                value: stats?['total_products']?.toString() ?? '',
                icon: Icons.inventory_2_outlined,
                color: AppColors.primary,
                isLoading: isLoading,
              ),
            ),
            const SizedBox(width: AppSizes.space3),
            Expanded(
              child: _buildStatCard(
                title: 'Commandes',
                value: stats?['total_orders']?.toString() ?? '',
                icon: Icons.shopping_bag_outlined,
                color: AppColors.success,
                isLoading: isLoading,
              ),
            ),
          ],
        ),
        
        const SizedBox(height: AppSizes.space3),
        
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                title: 'Revenus',
                value: stats != null ? '${stats['total_sales'] ?? '0'} CFA' : '',
                icon: Icons.attach_money,
                color: AppColors.warning,
                isLarge: true,
                isLoading: isLoading,
              ),
            ),
            const SizedBox(width: AppSizes.space3),
            Expanded(
              child: _buildStatCard(
                title: 'En attente',
                value: stats?['pending_orders']?.toString() ?? '',
                icon: Icons.pending_outlined,
                color: AppColors.info,
                isLoading: isLoading,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
    bool isLarge = false,
    bool isLoading = false,
  }) {
    return Container(
      padding: const EdgeInsets.all(AppSizes.paddingMedium),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
        boxShadow: AppShadows.shadowSM,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                ),
                child: Icon(
                  icon,
                  color: color,
                  size: 20,
                ),
              ),
            ],
          ),
          
          const SizedBox(height: AppSizes.space2),
          
          isLoading
              ? _buildSkeletonBox(80, 24)
              : Text(
                  value,
                  style: TextStyle(
                    fontSize: isLarge ? 20 : 24,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textDark,
                  ),
                ),
          
          const SizedBox(height: 4),
          
          Text(
            title,
            style: AppTextStyles.caption.copyWith(
              color: AppColors.textMuted,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActions() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Actions rapides',
          style: AppTextStyles.h3.copyWith(
            color: AppColors.textDark,
            fontWeight: FontWeight.w600,
          ),
        ),
        
        const SizedBox(height: AppSizes.space3),
        
        Row(
          children: [
            Expanded(
              child: _buildActionCard(
                title: 'Produits',
                icon: Icons.inventory_2_outlined,
                color: AppColors.primary,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const SellerProductsScreen(),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(width: AppSizes.space3),
            Expanded(
              child: _buildActionCard(
                title: 'Commandes',
                icon: Icons.shopping_bag_outlined,
                color: AppColors.success,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const SellerOrdersScreen(),
                    ),
                  ).then((_) {
                    // Rafraîchir le dashboard au retour
                    _loadData();
                  });
                },
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionCard({
    required String title,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppSizes.radiusLG),
      child: Container(
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          boxShadow: AppShadows.shadowSM,
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingMedium),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                icon,
                color: color,
                size: 30,
              ),
            ),
            
            const SizedBox(height: AppSizes.space2),
            
            Text(
              title,
              style: AppTextStyles.bodyMedium.copyWith(
                fontWeight: FontWeight.w600,
                color: AppColors.textDark,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentOrders() {
    return Consumer<SellerProvider>(
      builder: (context, sellerProvider, _) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Commandes récentes',
                  style: AppTextStyles.h3.copyWith(
                    color: AppColors.textDark,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const SellerOrdersScreen(),
                      ),
                    ).then((_) {
                      // Rafraîchir le dashboard au retour
                      _loadData();
                    });
                  },
                  child: Text(
                    'Voir tout',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppSizes.space3),
            
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingMedium),
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                boxShadow: AppShadows.shadowSM,
              ),
              child: sellerProvider.recentOrders.isEmpty
                  ? Center(
                      child: Padding(
                        padding: const EdgeInsets.all(AppSizes.paddingLarge),
                        child: Text(
                          'Aucune commande récente',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.textMuted,
                          ),
                        ),
                      ),
                    )
                  : Column(
                      children: sellerProvider.recentOrders.map((order) {
                        return _buildOrderCard(order);
                      }).toList(),
                    ),
            ),
          ],
        );
      },
    );
  }

  Future<void> _showOrderDetails(dynamic order) async {
    // Afficher un indicateur de chargement
    if (!mounted) return;
    
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(
        child: CircularProgressIndicator(),
      ),
    );

    try {
      final sellerService = SellerService();
      final orderNumber = order['order_number'] ?? order['id'].toString();
      final response = await sellerService.getOrderDetails(orderNumber);

      if (!mounted) return;
      Navigator.pop(context); // Fermer le dialog de chargement

      if (response['success'] && response['order'] != null) {
        // Naviguer vers SellerOrdersScreen qui affichera les détails
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => SellerOrdersScreen(initialOrderNumber: orderNumber),
          ),
        ).then((_) {
          // Rafraîchir le dashboard au retour
          _loadData();
        });
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'Erreur lors du chargement des détails'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context); // Fermer le dialog de chargement
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur: $e'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Widget _buildOrderCard(dynamic order) {
    return GestureDetector(
      onTap: () {
        // Charger et afficher les détails de la commande
        _showOrderDetails(order);
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        decoration: BoxDecoration(
          color: AppColors.background,
          borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
          border: Border.all(color: AppColors.grey200),
        ),
        child: Row(
          children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: _getOrderStatusColor(order['status']).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(AppSizes.radiusSmall),
                  ),
                  child: Icon(
                    _getOrderStatusIcon(order['status']),
                    color: _getOrderStatusColor(order['status']),
                    size: 24,
                  ),
                ),
            const SizedBox(width: AppSizes.space3),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '#${order['order_number']}',
                    style: AppTextStyles.bodyLarge.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    order['customer_name'] ?? 'Client',
                    style: AppTextStyles.bodySmall.copyWith(
                      color: AppColors.textMedium,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${order['items_count'] ?? 0} article(s)',
                    style: AppTextStyles.caption.copyWith(
                      color: AppColors.textLight,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    _getPaymentMethodLabel(order['payment_method']),
                    style: AppTextStyles.caption.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '${_formatPrice(order['total'] ?? 0)} FCFA',
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
                    color: _getOrderStatusColor(order['status']).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    _getOrderStatusLabel(order['status']),
                    style: AppTextStyles.caption.copyWith(
                      color: _getOrderStatusColor(order['status']),
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
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

  IconData _getOrderStatusIcon(String? status) {
    switch (status) {
      case 'pending':
        return Icons.schedule;
      case 'processing':
        return Icons.inventory_2_outlined;
      case 'delivered':
        return Icons.check_circle;
      case 'cancelled':
        return Icons.cancel;
      default:
        return Icons.help_outline;
    }
  }

  String _getPaymentMethodLabel(String? paymentMethod) {
    switch (paymentMethod) {
      case 'cash_on_delivery':
        return 'À la livraison';
      case 'mobile_money':
        return 'Mobile Money';
      case 'card':
        return 'Carte bancaire';
      case 'bank_transfer':
        return 'Virement bancaire';
      default:
        return 'Non spécifié';
    }
  }

  String _formatPrice(dynamic price) {
    if (price == null) return '0';
    
    if (price is String) {
      // Si c'est déjà une String, essayer de la convertir en double puis formater
      try {
        final doubleValue = double.parse(price);
        return doubleValue.toStringAsFixed(0);
      } catch (e) {
        // Si la conversion échoue, retourner la string telle quelle
        return price;
      }
    } else if (price is double || price is int) {
      return price.toStringAsFixed(0);
    }
    
    return '0';
  }

  /// Widget skeleton loader pour l'état de chargement
  Widget _buildSkeletonBox(double width, double height) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: AppColors.white.withOpacity(0.3),
        borderRadius: BorderRadius.circular(AppSizes.radiusSM),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(AppSizes.radiusSM),
        child: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
              colors: [
                AppColors.white.withOpacity(0.3),
                AppColors.white.withOpacity(0.5),
                AppColors.white.withOpacity(0.3),
              ],
              stops: const [0.0, 0.5, 1.0],
            ),
          ),
        ),
      ),
    );
  }
}