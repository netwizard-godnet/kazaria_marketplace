import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../utils/constants.dart';
import '../../firebasemsg.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  // Préférences de notifications
  bool _orderUpdates = true;
  bool _promotions = true;
  bool _newArrivals = true;
  bool _priceDrops = true;
  bool _pushNotifications = true;
  bool _isLoading = true;
  bool _isSaving = false;

  final ApiService _apiService = ApiService();
  final FirebaseMsg _firebaseMsg = FirebaseMsg();

  @override
  void initState() {
    super.initState();
    _loadPreferences();
  }

  /// Charger les préférences sauvegardées
  Future<void> _loadPreferences() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      setState(() {
        _orderUpdates = prefs.getBool('notif_order_updates') ?? true;
        _promotions = prefs.getBool('notif_promotions') ?? true;
        _newArrivals = prefs.getBool('notif_new_arrivals') ?? true;
        _priceDrops = prefs.getBool('notif_price_drops') ?? true;
        _pushNotifications = prefs.getBool('notif_push') ?? true;
        _isLoading = false;
      });
    } catch (e) {
      print('❌ Erreur chargement préférences: $e');
      setState(() => _isLoading = false);
    }
  }

  /// Sauvegarder les préférences
  Future<void> _savePreferences() async {
    if (_isSaving) return;

    setState(() => _isSaving = true);

    try {
      final prefs = await SharedPreferences.getInstance();
      
      // ✅ Sauvegarder localement
      await prefs.setBool('notif_order_updates', _orderUpdates);
      await prefs.setBool('notif_promotions', _promotions);
      await prefs.setBool('notif_new_arrivals', _newArrivals);
      await prefs.setBool('notif_price_drops', _priceDrops);
      await prefs.setBool('notif_push', _pushNotifications);

      // ✅ Sauvegarder dans le backend (optionnel - pour futurs filtres côté serveur)
      // await _savePreferencesInBackend();

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.check_circle, color: Colors.white),
                SizedBox(width: 12),
                Text('✅ Préférences enregistrées'),
              ],
            ),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppSizes.radiusLG),
            ),
            duration: const Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      print('❌ Erreur sauvegarde préférences: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('❌ Erreur: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      setState(() => _isSaving = false);
    }
  }

  /// Activer/Désactiver les notifications Push (FCM)
  Future<void> _togglePushNotifications(bool value) async {
    setState(() {
      _isSaving = true;
      _pushNotifications = value;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      
      if (value) {
        // ✅ ACTIVER : Réenregistrer le token FCM
        print('🔔 [NOTIFICATIONS_SCREEN] Activation des notifications...');
        
        // Réinitialiser Firebase et obtenir un nouveau token
        await _firebaseMsg.initFCM();
        
        await prefs.setBool('notif_push', true);
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Row(
                children: [
                  Icon(Icons.notifications_active, color: Colors.white),
                  SizedBox(width: 12),
                  Text('🔔 Notifications activées'),
                ],
              ),
              backgroundColor: AppColors.success,
              behavior: SnackBarBehavior.floating,
              duration: Duration(seconds: 2),
            ),
          );
        }
      } else {
        // ❌ DÉSACTIVER : Désenregistrer le token FCM du backend
        print('🔕 [NOTIFICATIONS_SCREEN] Désactivation des notifications...');
        
        final authToken = prefs.getString('token');
        if (authToken != null) {
          final fcmToken = prefs.getString('pending_fcm_token');
          
          if (fcmToken != null) {
            // Appeler l'API pour désenregistrer le token
            try {
              await _apiService.post(
                '${ApiConfig.baseUrl}/notifications/unregister-token',
                {'token': fcmToken},
                requiresAuth: true,
              );
              print('✅ [NOTIFICATIONS_SCREEN] Token FCM désenregistré du backend');
            } catch (e) {
              print('⚠️ [NOTIFICATIONS_SCREEN] Erreur désenregistrement: $e');
            }
          }
        }
        
        await prefs.setBool('notif_push', false);
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Row(
                children: [
                  Icon(Icons.notifications_off, color: Colors.white),
                  SizedBox(width: 12),
                  Text('🔕 Notifications désactivées'),
                ],
              ),
              backgroundColor: AppColors.textLight,
              behavior: SnackBarBehavior.floating,
              duration: Duration(seconds: 2),
            ),
          );
        }
      }
    } catch (e) {
      print('❌ [NOTIFICATIONS_SCREEN] Erreur toggle: $e');
      // Revenir à l'état précédent en cas d'erreur
      setState(() => _pushNotifications = !value);
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('❌ Erreur: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: const Text('Notifications'),
          elevation: 0,
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Notifications'),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSizes.space4),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Section: Types de notifications
            _buildSection(
              'Préférences de notifications',
              'Gérez vos notifications et alertes',
              [
                _buildNotificationTile(
                  icon: Icons.shopping_bag_outlined,
                  title: 'Mises à jour de commandes',
                  subtitle: 'Statut des commandes et livraisons',
                  value: _orderUpdates,
                  color: AppColors.primary,
                  onChanged: (value) {
                    setState(() => _orderUpdates = value);
                    _savePreferences();
                  },
                ),
                _buildNotificationTile(
                  icon: Icons.local_offer_outlined,
                  title: 'Promotions et offres',
                  subtitle: 'Offres spéciales et ventes flash',
                  value: _promotions,
                  color: AppColors.warning,
                  onChanged: (value) {
                    setState(() => _promotions = value);
                    _savePreferences();
                  },
                ),
                _buildNotificationTile(
                  icon: Icons.new_releases_outlined,
                  title: 'Nouveaux produits',
                  subtitle: 'Alertes sur les nouvelles arrivées',
                  value: _newArrivals,
                  color: AppColors.accent,
                  onChanged: (value) {
                    setState(() => _newArrivals = value);
                    _savePreferences();
                  },
                ),
                _buildNotificationTile(
                  icon: Icons.trending_down,
                  title: 'Baisse de prix',
                  subtitle: 'Alerte quand le prix baisse',
                  value: _priceDrops,
                  color: AppColors.error,
                  onChanged: (value) {
                    setState(() => _priceDrops = value);
                    _savePreferences();
                  },
                ),
                _buildNotificationTile(
                  icon: Icons.notifications_active_outlined,
                  title: 'Notifications Push',
                  subtitle: _isSaving 
                      ? 'Mise à jour en cours...' 
                      : 'Recevoir les notifications',
                  value: _pushNotifications,
                  color: AppColors.info,
                  onChanged: _isSaving ? null : _togglePushNotifications,
                  isLoading: _isSaving,
                ),
              ],
            ),
            
            const SizedBox(height: AppSizes.space4),
            
            // Info auto-save
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.info.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppColors.info.withOpacity(0.3),
                ),
              ),
              child: Row(
                children: [
                  Icon(Icons.info_outline, color: AppColors.info, size: 20),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Vos préférences sont enregistrées automatiquement',
                      style: TextStyle(
                        color: AppColors.info,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSection(String title, String subtitle, List<Widget> children) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: AppTextStyles.headlineSmall),
        const SizedBox(height: AppSizes.space2),
        Text(
          subtitle,
          style: AppTextStyles.bodySmall.copyWith(
            color: AppColors.textMuted,
          ),
        ),
        const SizedBox(height: AppSizes.space4),
        Container(
          decoration: BoxDecoration(
            color: AppColors.white,
            borderRadius: BorderRadius.circular(AppSizes.radiusXL),
            boxShadow: AppShadows.shadowMD,
          ),
          child: Column(
            children: children,
          ),
        ),
      ],
    );
  }

  Widget _buildNotificationTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required Color color,
    required ValueChanged<bool>? onChanged,
    bool isLoading = false,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onChanged != null && !isLoading ? () => onChanged(!value) : null,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        child: Opacity(
          opacity: isLoading ? 0.6 : 1.0,
          child: Padding(
            padding: const EdgeInsets.all(AppSizes.space4),
            child: Row(
              children: [
                // Icône
                Container(
                  padding: const EdgeInsets.all(AppSizes.space2),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        color.withOpacity(0.1),
                        color.withOpacity(0.05),
                      ],
                    ),
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  ),
                  child: Icon(icon, color: color, size: AppSizes.iconMD),
                ),
                
                const SizedBox(width: AppSizes.space3),
                
                // Texte
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title, style: AppTextStyles.titleMedium),
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
                
                // Switch ou Loading
                if (isLoading)
                  const SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                else
                  Switch(
                    value: value,
                    onChanged: onChanged,
                    activeColor: color,
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

}

