import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';

class RecentActivityScreen extends StatefulWidget {
  const RecentActivityScreen({super.key});

  @override
  State<RecentActivityScreen> createState() => _RecentActivityScreenState();
}

class _RecentActivityScreenState extends State<RecentActivityScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<Map<String, dynamic>> _activities = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadActivities();
  }

  Future<void> _loadActivities() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await _apiService.get(
        ApiConfig.recentActivity,
        requiresAuth: true,
      );

      print('📊 [ACTIVITY] Réponse API: ${response['success']}');
      print('📊 [ACTIVITY] Données reçues: ${response.keys}');
      
      if (response['success'] == true) {
        final activitiesData = response['activities'] ?? [];
        print('📊 [ACTIVITY] Nombre d\'activités reçues: ${activitiesData.length}');
        
        setState(() {
          _activities = List<Map<String, dynamic>>.from(activitiesData);
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'Erreur lors du chargement des activités';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Erreur de connexion: $e';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Activité récente'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadActivities,
            tooltip: 'Rafraîchir',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(),
            )
          : _error != null
              ? _buildErrorState()
              : _activities.isEmpty
                  ? _buildEmptyState()
                  : _buildActivitiesList(),
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
              onPressed: _loadActivities,
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

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.history,
                color: AppColors.primary,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Aucune activité récente',
              style: AppTextStyles.headlineMedium.copyWith(
                color: AppColors.textDark,
              ),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              'Commencez à explorer nos produits !',
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActivitiesList() {
    return RefreshIndicator(
      onRefresh: _loadActivities,
      child: ListView.builder(
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        itemCount: _activities.length,
        itemBuilder: (context, index) {
          final activity = _activities[index];
          return _buildActivityCard(activity);
        },
      ),
    );
  }

  Widget _buildActivityCard(Map<String, dynamic> activity) {
    final type = activity['type'] as String? ?? '';
    final title = activity['title'] as String? ?? '';
    final description = activity['description'] as String? ?? '';
    final date = activity['date'] as String? ?? '';

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
      default:
        icon = Icons.info;
        iconColor = AppColors.textLight;
        badgeColor = AppColors.textLight;
        badgeLabel = 'Autre';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space3),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        child: Row(
          children: [
            // Icône
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: iconColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
              ),
              child: Icon(
                icon,
                color: iconColor,
                size: 24,
              ),
            ),
            const SizedBox(width: AppSizes.space3),
            // Contenu
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: AppTextStyles.bodyLarge.copyWith(
                      fontWeight: FontWeight.w600,
                      color: AppColors.textDark,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    description,
                    style: AppTextStyles.bodySmall.copyWith(
                      color: AppColors.textMedium,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    date,
                    style: AppTextStyles.caption.copyWith(
                      color: AppColors.textLight,
                    ),
                  ),
                ],
              ),
            ),
            // Badge
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 12,
                vertical: 6,
              ),
              decoration: BoxDecoration(
                color: badgeColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: badgeColor.withOpacity(0.3),
                ),
              ),
              child: Text(
                badgeLabel,
                style: AppTextStyles.caption.copyWith(
                  color: badgeColor,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
