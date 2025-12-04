import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import '../../providers/auth_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../services/order_service.dart';
import '../../services/api_service.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import 'edit_profile_screen.dart';
import 'order_history_screen.dart';
import 'track_order_screen.dart';
import 'invoice_history_screen.dart';
import 'favorites_screen.dart';
import 'addresses_screen.dart';
import 'change_password_screen.dart';
import 'payments_screen.dart';
import 'notifications_screen.dart';
import 'language_screen.dart';
import 'help_screen.dart';
import '../auth/login_screen.dart';
import '../seller/seller_dashboard_screen.dart';
import '../seller/seller_register_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen>
    with SingleTickerProviderStateMixin {
  final ImagePicker _picker = ImagePicker();
  final OrderService _orderService = OrderService();
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  int _ordersCount = 0;
  int _favoritesCount = 0;
  int _reviewsCount = 0;
  bool _isLoadingStats = true;
  List<Map<String, dynamic>> _recentActivities = [];
  bool _isLoadingActivities = false;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: AppAnimations.normal,
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
    );
    _slideAnimation =
        Tween<Offset>(begin: const Offset(0, 0.1), end: Offset.zero).animate(
          CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
        );
    _animationController.forward();

    // Charger les statistiques réelles
    _loadStats();
    _loadRecentActivity();
  }

  Future<void> _loadStats() async {
    if (!mounted) return;

    setState(() => _isLoadingStats = true);

    try {
      print('📊 [PROFILE] Chargement des statistiques...');

      // Charger les commandes
      final ordersResponse = await _orderService.getMyOrders();
      print('📦 [PROFILE] Réponse API commandes:');
      print('   - Success: ${ordersResponse['success']}');
      print('   - Orders présent: ${ordersResponse['orders'] != null}');

      if (ordersResponse['success'] && ordersResponse['orders'] != null) {
        final ordersList = ordersResponse['orders'] as List;
        _ordersCount = ordersList.length;
        print('✅ [PROFILE] Nombre de commandes: $_ordersCount');

        // Log détaillé de chaque commande
        for (var order in ordersList) {
          if (order is Map) {
            print(
              '   📦 ${order['order_number']}: status=${order['status']}, payment=${order['payment_status']}',
            );
          }
        }
      } else {
        print(
          '⚠️ [PROFILE] Pas de commandes trouvées ou erreur: ${ordersResponse['message']}',
        );
        _ordersCount = 0;
      }

      // Charger les favoris
      final favoritesProvider = Provider.of<FavoritesProvider>(
        context,
        listen: false,
      );
      await favoritesProvider.loadFavorites();
      _favoritesCount = favoritesProvider.favoritesCount;
      print('❤️ [PROFILE] Nombre de favoris: $_favoritesCount');

      // Charger le nombre d'avis donnés par l'utilisateur
      await _loadReviewsCount();
      print('⭐ [PROFILE] Nombre d\'avis: $_reviewsCount');

      if (mounted) {
        setState(() => _isLoadingStats = false);
      }

      print('✅ [PROFILE] Statistiques chargées avec succès');
    } catch (e) {
      print('❌ [PROFILE] Exception lors du chargement des stats: $e');
      if (mounted) {
        setState(() {
          _isLoadingStats = false;
          _ordersCount = 0;
          _favoritesCount = 0;
          _reviewsCount = 0;
        });
      }
    }
  }

  /// Charger le nombre d'avis donnés par l'utilisateur
  Future<void> _loadReviewsCount() async {
    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final user = authProvider.user;

      if (user == null) {
        _reviewsCount = 0;
        return;
      }

      // Appeler l'API pour obtenir le nombre d'avis de l'utilisateur
      final apiService = ApiService();
      final response = await apiService.get(
        ApiConfig.myReviewsCount,
        requiresAuth: true,
      );

      if (response['success'] == true) {
        _reviewsCount = response['count'] ?? 0;
        print('⭐ [PROFILE] Nombre d\'avis chargés: $_reviewsCount');
      } else {
        _reviewsCount = 0;
        print(
          '⚠️ [PROFILE] Impossible de charger les avis: ${response['message']}',
        );
      }
    } catch (e) {
      print('❌ [PROFILE] Erreur chargement avis: $e');
      _reviewsCount = 0;
    }
  }

  /// Charger l'activité récente de l'utilisateur
  Future<void> _loadRecentActivity() async {
    if (!mounted) return;

    setState(() => _isLoadingActivities = true);

    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final user = authProvider.user;

      if (user == null) {
        _recentActivities = [];
        setState(() => _isLoadingActivities = false);
        return;
      }

      final apiService = ApiService();
      final response = await apiService.get(
        ApiConfig.recentActivity,
        requiresAuth: true,
      );

      if (response['success'] == true) {
        _recentActivities = List<Map<String, dynamic>>.from(
          response['activities'] ?? [],
        );
        print('📊 [PROFILE] ${_recentActivities.length} activités chargées');
      } else {
        _recentActivities = [];
        print(
          '⚠️ [PROFILE] Impossible de charger les activités: ${response['message']}',
        );
      }
    } catch (e) {
      print('❌ [PROFILE] Erreur chargement activités: $e');
      _recentActivities = [];
    }

    if (mounted) {
      setState(() => _isLoadingActivities = false);
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _pickAndUpdatePhoto() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 85,
      );

      if (image != null) {
        final authProvider = Provider.of<AuthProvider>(context, listen: false);
        final response = await authProvider.updateProfilePhoto(
          File(image.path),
        );

        if (!mounted) return;

        if (response['success']) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Row(
                children: [
                  Icon(Icons.check_circle, color: Colors.white),
                  SizedBox(width: 12),
                  Text('Photo de profil mise à jour'),
                ],
              ),
              backgroundColor: AppColors.success,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
              ),
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                response['message'] ?? 'Erreur lors de la mise à jour',
              ),
              backgroundColor: AppColors.error,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
              ),
            ),
          );
        }
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur: $e'),
          backgroundColor: AppColors.error,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          ),
        ),
      );
    }
  }

  void _showLogoutDialog() {
    final parentContext = context;
    showDialog(
      context: parentContext,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppSizes.radius2XL),
          ),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(AppSizes.space2),
                decoration: BoxDecoration(
                  color: AppColors.errorLight,
                  borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                ),
                child: const Icon(Icons.logout, color: AppColors.error),
              ),
              const SizedBox(width: AppSizes.space3),
              const Text('Déconnexion', style: AppTextStyles.headlineSmall),
            ],
          ),
          content: const Text(
            'Êtes-vous sûr de vouloir vous déconnecter ?',
            style: AppTextStyles.bodyMedium,
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              child: const Text('Annuler'),
            ),
            Container(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppColors.error, Color(0xFFDC2626)],
                ),
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
              ),
              child: ElevatedButton(
                onPressed: () async {
                  Navigator.of(dialogContext).pop();
                  final authProvider = Provider.of<AuthProvider>(
                    parentContext,
                    listen: false,
                  );
                  await authProvider.logout();
                  if (mounted) {
                    Navigator.of(parentContext).pushAndRemoveUntil(
                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                      (route) => false,
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                ),
                child: const Text('Déconnexion'),
              ),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: user == null
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadStats,
              child: FadeTransition(
                opacity: _fadeAnimation,
                child: SlideTransition(
                  position: _slideAnimation,
                  child: CustomScrollView(
                    slivers: [
                      // AppBar moderne avec profil
                      _buildModernAppBar(user),

                      // Contenu
                      SliverToBoxAdapter(
                        child: Padding(
                          padding: const EdgeInsets.all(AppSizes.space4),
                          child: Column(
                            children: [
                              // Stats Cards
                              _buildStatsSection(),

                              const SizedBox(height: AppSizes.space6),

                              // Bouton "Ma Boutique" pour les vendeurs
                              _buildSellerStoreButton(context, user),

                              // Activité récente
                              _buildRecentActivitySection(),

                              const SizedBox(height: AppSizes.space6),

                              // Menu Principal
                              _buildMenuSection(context),

                              const SizedBox(height: AppSizes.space6),

                              // Paramètres
                              _buildSettingsSection(context),

                              const SizedBox(height: AppSizes.space6),

                              // Bouton Déconnexion
                              _buildLogoutButton(),

                              const SizedBox(height: AppSizes.space8),
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

  Widget _buildModernAppBar(user) {
    return SliverAppBar(
      expandedHeight: 200,
      floating: false,
      pinned: true,
      elevation: 0,
      backgroundColor: Colors.transparent,
      flexibleSpace: FlexibleSpaceBar(
        background: Stack(
          children: [
            // Gradient Background
            Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [
                    AppColors.primary,
                    AppColors.primaryLight,
                    AppColors.secondary,
                  ],
                ),
              ),
            ),

            // Pattern décoratif
            Positioned.fill(
              child: CustomPaint(painter: CirclePatternPainter()),
            ),

            // Contenu
            SafeArea(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSizes.paddingMedium,
                ),
                child: Row(
                  children: [
                    // Photo de profil
                    _buildProfilePhoto(user),

                    const SizedBox(width: AppSizes.space3),

                    // Informations utilisateur
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Nom
                          Text(
                            user.fullName,
                            style: AppTextStyles.headlineMedium.copyWith(
                              color: AppColors.white,
                              fontWeight: FontWeight.bold,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),

                          const SizedBox(height: AppSizes.space1),

                          // Email
                          Text(
                            user.email,
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.white.withOpacity(0.9),
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),

                          const SizedBox(height: AppSizes.space3),

                          // Badge
                          _buildUserBadge(user),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfilePhoto(user) {
    return Stack(
      children: [
        // Photo avec bordure animée
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: const LinearGradient(
              colors: [AppColors.white, AppColors.secondaryLight],
            ),
            boxShadow: AppShadows.shadow2XL,
          ),
          child: Container(
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(color: AppColors.white, width: 4),
            ),
            child: CircleAvatar(
              radius: 30,
              backgroundColor: AppColors.grey200,
              backgroundImage: user.profilePicUrl != null
                  ? NetworkImage(
                      '${ApiConfig.imageBaseUrl}/${user.profilePicUrl}',
                    )
                  : null,
              child: user.profilePicUrl == null
                  ? Text(
                      user.fullName.substring(0, 2).toUpperCase(),
                      style: AppTextStyles.headlineMedium.copyWith(
                        color: AppColors.primary,
                      ),
                    )
                  : null,
            ),
          ),
        ),

        // Bouton Edit
        Positioned(
          bottom: 0,
          right: 0,
          child: GestureDetector(
            onTap: _pickAndUpdatePhoto,
            child: Container(
              padding: const EdgeInsets.all(AppSizes.space2),
              decoration: BoxDecoration(
                gradient: AppColors.accentGradient,
                shape: BoxShape.circle,
                boxShadow: AppShadows.shadowLG,
              ),
              child: const Icon(
                Icons.camera_alt,
                color: AppColors.white,
                size: AppSizes.iconSM,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildUserBadge(user) {
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: AppSizes.space4,
        vertical: AppSizes.space2,
      ),
      decoration: BoxDecoration(
        color: AppColors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        border: Border.all(color: AppColors.white.withOpacity(0.3), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            user.isSeller ? Icons.store : Icons.person,
            color: AppColors.white,
            size: AppSizes.iconSM,
          ),
          const SizedBox(width: AppSizes.space2),
          Text(
            user.isSeller ? 'Vendeur' : 'Client',
            style: AppTextStyles.labelLarge.copyWith(
              color: AppColors.white,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsSection() {
    return Row(
      children: [
        Expanded(
          child: _buildStatCard(
            'Commandes',
            _isLoadingStats ? '...' : '$_ordersCount',
            Icons.shopping_bag,
            AppColors.primary,
          ),
        ),
        const SizedBox(width: AppSizes.space3),
        Expanded(
          child: _buildStatCard(
            'Favoris',
            _isLoadingStats ? '...' : '$_favoritesCount',
            Icons.favorite,
            AppColors.error,
          ),
        ),
        const SizedBox(width: AppSizes.space3),
        Expanded(
          child: _buildStatCard(
            'Avis',
            _isLoadingStats ? '...' : '$_reviewsCount',
            Icons.stars,
            AppColors.warning,
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String label,
    String value,
    IconData icon,
    Color color,
  ) {
    return Container(
      padding: const EdgeInsets.all(AppSizes.space4),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.white, color.withOpacity(0.05)],
        ),
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(AppSizes.space2),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(AppSizes.radiusLG),
            ),
            child: Icon(icon, color: color, size: AppSizes.iconLG),
          ),
          const SizedBox(height: AppSizes.space2),
          Text(
            value,
            style: AppTextStyles.headlineSmall.copyWith(
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: AppSizes.space1),
          Text(
            label,
            style: AppTextStyles.labelSmall.copyWith(
              color: AppColors.textMuted,
            ),
          ),
        ],
      ),
    );
  }

  /// Section Activité récente
  Widget _buildRecentActivitySection() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: AppSizes.space4),
      padding: const EdgeInsets.all(AppSizes.space4),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(Icons.history, color: AppColors.primary, size: 20),
                  const SizedBox(width: AppSizes.space2),
                  Text(
                    'Activité récente',
                    style: AppTextStyles.h3.copyWith(color: AppColors.textDark),
                  ),
                ],
              ),
              IconButton(
                icon: _isLoadingActivities
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.refresh, size: 20),
                onPressed: _isLoadingActivities ? null : _loadRecentActivity,
                color: AppColors.primary,
              ),
            ],
          ),

          const SizedBox(height: AppSizes.space4),

          // Liste des activités
          if (_isLoadingActivities && _recentActivities.isEmpty)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(AppSizes.space6),
                child: CircularProgressIndicator(),
              ),
            )
          else if (_recentActivities.isEmpty)
            _buildEmptyActivityState()
          else
            ..._recentActivities
                .take(5)
                .map((activity) => _buildActivityItem(activity))
                .toList(),
        ],
      ),
    );
  }

  /// Item d'activité
  Widget _buildActivityItem(Map<String, dynamic> activity) {
    final type = activity['type'] as String;
    final title = activity['title'] as String;
    final description = activity['description'] as String;
    final date = activity['date'] as String;

    IconData icon;
    Color iconColor;
    Color badgeColor;
    String badgeLabel;

    switch (type) {
      case 'order':
        icon = Icons.shopping_bag;
        iconColor = AppColors.primary;
        badgeColor = AppColors.primary;
        badgeLabel = 'Commande';
        break;
      case 'favorite':
        icon = Icons.favorite;
        iconColor = AppColors.error;
        badgeColor = AppColors.success;
        badgeLabel = 'Favori';
        break;
      case 'cart':
        icon = Icons.shopping_cart;
        iconColor = AppColors.info;
        badgeColor = AppColors.info;
        badgeLabel = 'Panier';
        break;
      case 'view':
        icon = Icons.visibility;
        iconColor = AppColors.grey500;
        badgeColor = AppColors.grey500;
        badgeLabel = 'Consulté';
        break;
      case 'review':
        icon = Icons.star;
        iconColor = AppColors.warning;
        badgeColor = AppColors.warning;
        badgeLabel = 'Avis';
        break;
      default:
        icon = Icons.circle;
        iconColor = AppColors.grey500;
        badgeColor = AppColors.grey500;
        badgeLabel = type;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space3),
      padding: const EdgeInsets.all(AppSizes.space3),
      decoration: BoxDecoration(
        color: AppColors.grey50,
        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Icône
          Container(
            padding: const EdgeInsets.all(AppSizes.space2),
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 20, color: iconColor),
          ),

          const SizedBox(width: AppSizes.space3),

          // Contenu
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: AppTextStyles.h4.copyWith(fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: AppSizes.space1),
                Text(
                  description,
                  style: AppTextStyles.body.copyWith(
                    color: AppColors.textLight,
                    fontSize: 13,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: AppSizes.space2),
                Row(
                  children: [
                    Icon(
                      Icons.access_time,
                      size: 14,
                      color: AppColors.textLight,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      date,
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(width: AppSizes.space2),

          // Badge
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: AppSizes.space2,
              vertical: AppSizes.space1,
            ),
            decoration: BoxDecoration(
              color: badgeColor,
              borderRadius: BorderRadius.circular(AppSizes.radiusMD),
            ),
            child: Text(
              badgeLabel,
              style: const TextStyle(
                color: AppColors.white,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// État vide pour les activités
  Widget _buildEmptyActivityState() {
    return Padding(
      padding: const EdgeInsets.all(AppSizes.space6),
      child: Column(
        children: [
          Icon(
            Icons.history,
            size: 64,
            color: AppColors.textLight.withOpacity(0.3),
          ),
          const SizedBox(height: AppSizes.space3),
          Text(
            'Aucune activité récente',
            style: AppTextStyles.h4.copyWith(color: AppColors.textLight),
          ),
          const SizedBox(height: AppSizes.space2),
          Text(
            'Commencez à explorer nos produits !',
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildMenuSection(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        boxShadow: AppShadows.shadowLG,
      ),
      child: Column(
        children: [
          _buildModernMenuItem(
            icon: Icons.shopping_bag_outlined,
            title: 'Mes commandes',
            subtitle: 'Historique et suivi',
            color: AppColors.primary,
            badge: _isLoadingStats ? null : _ordersCount.toString(),
            onTap: () async {
              await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const OrderHistoryScreen()),
              );
              // Recharger les stats au retour
              _loadStats();
            },
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.local_shipping_outlined,
            title: 'Suivre ma commande',
            subtitle: 'Trouver votre commande',
            color: AppColors.secondary,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const TrackOrderScreen()),
              );
            },
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.receipt_long_outlined,
            title: 'Mes factures',
            subtitle: 'Factures téléchargées',
            color: AppColors.warning,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const InvoiceHistoryScreen()),
              );
            },
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.favorite_outline,
            title: 'Mes favoris',
            subtitle: 'Produits sauvegardés',
            color: AppColors.error,
            badge: _isLoadingStats ? null : _favoritesCount.toString(),
            onTap: () async {
              await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const FavoritesScreen()),
              );
              // Recharger les stats au retour
              _loadStats();
            },
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.location_on_outlined,
            title: 'Adresses',
            subtitle: 'Gérer mes adresses',
            color: AppColors.accent,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const AddressesScreen()),
            ),
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.payment,
            title: 'Paiements',
            subtitle: 'Moyens de paiement',
            color: AppColors.warning,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const PaymentsScreen()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSettingsSection(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        boxShadow: AppShadows.shadowLG,
      ),
      child: Column(
        children: [
          _buildModernMenuItem(
            icon: Icons.person_outline,
            title: 'Modifier le profil',
            subtitle: 'Informations personnelles',
            color: AppColors.secondary,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const EditProfileScreen()),
            ),
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.lock_outline,
            title: 'Modifier le mot de passe',
            subtitle: 'Sécurité du compte',
            color: AppColors.warning,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
            ),
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.notifications_outlined,
            title: 'Notifications',
            subtitle: 'Préférences de notification',
            color: AppColors.info,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
            ),
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.language,
            title: 'Langue',
            subtitle: 'Français',
            color: AppColors.primary,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const LanguageScreen()),
            ),
          ),
          _buildDivider(),
          _buildModernMenuItem(
            icon: Icons.help_outline,
            title: 'Aide & Support',
            subtitle: 'Contactez-nous',
            color: AppColors.accent,
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const HelpScreen()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildModernMenuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
    String? badge,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        child: Padding(
          padding: const EdgeInsets.all(AppSizes.space4),
          child: Row(
            children: [
              // Icône avec badge
              Stack(
                clipBehavior: Clip.none,
                children: [
                  Container(
                    padding: const EdgeInsets.all(AppSizes.space3),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          color.withOpacity(0.1),
                          color.withOpacity(0.05),
                        ],
                      ),
                      borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                    ),
                    child: Icon(icon, color: color, size: AppSizes.iconLG),
                  ),
                  if (badge != null && badge != '0')
                    Positioned(
                      right: -6,
                      top: -6,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: color,
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: [
                            BoxShadow(
                              color: color.withOpacity(0.4),
                              blurRadius: 4,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Text(
                          badge,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                ],
              ),

              const SizedBox(width: AppSizes.space4),

              // Texte
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: AppTextStyles.titleLarge),
                    const SizedBox(height: AppSizes.space1),
                    Text(
                      subtitle,
                      style: AppTextStyles.bodySmall.copyWith(
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ),

              // Flèche
              Icon(
                Icons.chevron_right,
                color: AppColors.textMuted,
                size: AppSizes.iconLG,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDivider() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSizes.space4),
      child: Divider(height: 1, color: AppColors.grey200),
    );
  }

  Widget _buildLogoutButton() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.error, Color(0xFFDC2626)],
        ),
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: AppShadows.shadowLG,
      ),
      child: ElevatedButton(
        onPressed: _showLogoutDialog,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          shadowColor: Colors.transparent,
          padding: const EdgeInsets.symmetric(vertical: AppSizes.space4),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppSizes.radiusXL),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.logout, size: AppSizes.iconMD),
            const SizedBox(width: AppSizes.space2),
            Text(
              'Déconnexion',
              style: AppTextStyles.button.copyWith(fontSize: 16),
            ),
          ],
        ),
      ),
    );
  }
}

// Custom Painter pour le pattern de fond
class CirclePatternPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withOpacity(0.1)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2;

    // Dessiner des cercles décoratifs
    canvas.drawCircle(Offset(size.width * 0.2, size.height * 0.3), 40, paint);

    canvas.drawCircle(Offset(size.width * 0.8, size.height * 0.2), 60, paint);

    canvas.drawCircle(Offset(size.width * 0.9, size.height * 0.7), 30, paint);

    canvas.drawCircle(Offset(size.width * 0.1, size.height * 0.8), 50, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

extension _ProfileScreenExtension on _ProfileScreenState {
  /// Bouton "Ma Boutique" pour les vendeurs ou "Devenir Vendeur"
  Widget _buildSellerStoreButton(BuildContext context, user) {
    // Debug: Afficher les informations de l'utilisateur
    if (user != null) {
      print('🔍 [PROFILE] User connecté: ${user.email}');
      print('🔍 [PROFILE] User isSeller: ${user.isSeller}');
      print('🔍 [PROFILE] User hasStore: ${user.hasStore}');
    } else {
      print('🔍 [PROFILE] Aucun utilisateur connecté');
    }

    // Ne rien afficher si pas d'utilisateur
    if (user == null) {
      return const SizedBox.shrink();
    }

    // Si l'utilisateur n'est pas vendeur, afficher le bouton "Devenir Vendeur"
    if (!user.isSeller) {
      return Container(
        margin: const EdgeInsets.only(bottom: AppSizes.space6),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFFFF6B00), Color(0xFFFF8C42)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(AppSizes.radius2XL),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFFFF6B00).withOpacity(0.3),
              blurRadius: 15,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const SellerRegisterScreen()),
              );
            },
            borderRadius: BorderRadius.circular(AppSizes.radius2XL),
            child: Padding(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              child: Row(
                children: [
                  // Icône avec effet de glow
                  Container(
                    padding: const EdgeInsets.all(AppSizes.space3),
                    decoration: BoxDecoration(
                      color: AppColors.white.withOpacity(0.25),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.white.withOpacity(0.3),
                          blurRadius: 10,
                          spreadRadius: 2,
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.storefront,
                      color: AppColors.white,
                      size: 32,
                    ),
                  ),

                  const SizedBox(width: AppSizes.space4),

                  // Texte
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Devenir Vendeur',
                          style: AppTextStyles.h3.copyWith(
                            color: AppColors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Créez votre boutique et vendez vos produits',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.white.withOpacity(0.95),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Flèche avec effet
                  Container(
                    padding: const EdgeInsets.all(AppSizes.space2),
                    decoration: BoxDecoration(
                      color: AppColors.white.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.arrow_forward_rounded,
                      color: AppColors.white,
                      size: 20,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    // Si l'utilisateur est vendeur mais n'a pas de boutique, afficher le bouton "Créer ma boutique"
    if (user.isSeller && !user.hasStore) {
      return Container(
        margin: const EdgeInsets.only(bottom: AppSizes.space6),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFFFF6B00), Color(0xFFFF8C42)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(AppSizes.radius2XL),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFFFF6B00).withOpacity(0.3),
              blurRadius: 15,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const SellerRegisterScreen()),
              );
            },
            borderRadius: BorderRadius.circular(AppSizes.radius2XL),
            child: Padding(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              child: Row(
                children: [
                  // Icône avec effet de glow
                  Container(
                    padding: const EdgeInsets.all(AppSizes.space3),
                    decoration: BoxDecoration(
                      color: AppColors.white.withOpacity(0.25),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.white.withOpacity(0.3),
                          blurRadius: 10,
                          spreadRadius: 2,
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.storefront,
                      color: AppColors.white,
                      size: 32,
                    ),
                  ),

                  const SizedBox(width: AppSizes.space4),

                  // Texte
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Créer ma boutique',
                          style: AppTextStyles.h3.copyWith(
                            color: AppColors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Configurez votre boutique et commencez à vendre',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.white.withOpacity(0.95),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Flèche avec effet
                  Container(
                    padding: const EdgeInsets.all(AppSizes.space2),
                    decoration: BoxDecoration(
                      color: AppColors.white.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.arrow_forward_rounded,
                      color: AppColors.white,
                      size: 20,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    // Si l'utilisateur est vendeur et a une boutique, afficher le bouton "Ma Boutique"
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space6),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.primary, AppColors.secondary],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withOpacity(0.3),
            blurRadius: 15,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const SellerDashboardScreen()),
            );
          },
          borderRadius: BorderRadius.circular(AppSizes.radius2XL),
          child: Padding(
            padding: const EdgeInsets.all(AppSizes.paddingLarge),
            child: Row(
              children: [
                // Icône avec effet de glow
                Container(
                  padding: const EdgeInsets.all(AppSizes.space3),
                  decoration: BoxDecoration(
                    color: AppColors.white.withOpacity(0.25),
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.white.withOpacity(0.3),
                        blurRadius: 10,
                        spreadRadius: 2,
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.store_rounded,
                    color: AppColors.white,
                    size: 32,
                  ),
                ),

                const SizedBox(width: AppSizes.space4),

                // Texte
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Ma Boutique',
                        style: AppTextStyles.h3.copyWith(
                          color: AppColors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Gérer mes produits et commandes',
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: AppColors.white.withOpacity(0.95),
                        ),
                      ),
                    ],
                  ),
                ),

                // Flèche avec effet
                Container(
                  padding: const EdgeInsets.all(AppSizes.space2),
                  decoration: BoxDecoration(
                    color: AppColors.white.withOpacity(0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.arrow_forward_rounded,
                    color: AppColors.white,
                    size: 24,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
