import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../utils/constants.dart';
import '../../providers/store_provider.dart';
import '../../models/store_model.dart';
import '../../screens/store/store_details_screen.dart';
import '../../config/api_config.dart';

/// Section Boutiques Officielles - Grid de logos
class BrandsSection extends StatefulWidget {
  const BrandsSection({super.key});

  @override
  State<BrandsSection> createState() => _BrandsSectionState();
}

class _BrandsSectionState extends State<BrandsSection> {
  @override
  void initState() {
    super.initState();
    // Charger les boutiques officielles au démarrage
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<StoreProvider>(context, listen: false).loadOfficialStores();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<StoreProvider>(
      builder: (context, storeProvider, _) {
        final officialStores = storeProvider.officialStores;
        
        // Ne rien afficher si aucune boutique officielle
        if (!storeProvider.isLoadingOfficial && officialStores.isEmpty) {
          return const SizedBox.shrink();
        }

        return Container(
          margin: const EdgeInsets.symmetric(vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // En-tête
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.verified,
                        color: AppColors.primary,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 10),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '✓ Boutiques Officielles',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textDark,
                            ),
                          ),
                          Text(
                            'Découvrez nos boutiques certifiées',
                            style: TextStyle(
                              fontSize: 12,
                              color: AppColors.textMedium,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 16),
              
              // Grid de boutiques ou chargement
              storeProvider.isLoadingOfficial
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(20),
                        child: CircularProgressIndicator(),
                      ),
                    )
                  : Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 4,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          childAspectRatio: 1,
                        ),
                        itemCount: officialStores.length > 8 ? 8 : officialStores.length,
                        itemBuilder: (context, index) {
                          final store = officialStores[index];
                          return _OfficialStoreCard(
                            store: store,
                          );
                        },
                      ),
                    ),
            ],
          ),
        );
      },
    );
  }
}

/// Card individuelle de boutique officielle
class _OfficialStoreCard extends StatelessWidget {
  final StoreModel store;

  const _OfficialStoreCard({
    required this.store,
  });

  /// Obtenir l'URL complète du logo
  String? _getLogoUrl() {
    if (store.logoUrl != null && store.logoUrl!.isNotEmpty) {
      // Si l'URL est déjà complète, la retourner telle quelle
      if (store.logoUrl!.startsWith('http://') || store.logoUrl!.startsWith('https://')) {
        return store.logoUrl;
      }
      // Corriger les URLs mal formées (http: au lieu de http://)
      if (store.logoUrl!.startsWith('http:') && !store.logoUrl!.startsWith('http://')) {
        return store.logoUrl!.replaceFirst('http:', 'http://');
      }
      // Sinon, construire l'URL complète
      return '${ApiConfig.imageBaseUrl}/${store.logoUrl}';
    }
    
    // Vérifier le champ logo (sans Url)
    if (store.logo != null && store.logo!.isNotEmpty) {
      if (store.logo!.startsWith('http://') || store.logo!.startsWith('https://')) {
        return store.logo;
      }
      // Corriger les URLs mal formées
      if (store.logo!.startsWith('http:') && !store.logo!.startsWith('http://')) {
        return store.logo!.replaceFirst('http:', 'http://');
      }
      return '${ApiConfig.imageBaseUrl}/${store.logo}';
    }
    
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final logoUrl = _getLogoUrl();
    
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => StoreDetailsScreen(store: store),
          ),
        );
      },
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: AppColors.grey200,
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Logo de la boutique
            logoUrl != null
              ? ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: CachedNetworkImage(
                    imageUrl: logoUrl,
                    width: 50,
                    height: 50,
                    fit: BoxFit.contain,
                    placeholder: (context, url) => Container(
                      width: 50,
                      height: 50,
                      decoration: BoxDecoration(
                        color: AppColors.grey100,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Center(
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(AppColors.primary),
                        ),
                      ),
                    ),
                    errorWidget: (context, url, error) {
                      print('❌ [BRANDS] Erreur chargement logo ${store.name}: $logoUrl');
                      return _buildFallbackIcon();
                    },
                  ),
                )
              : _buildFallbackIcon(),
            
            const SizedBox(height: 8),
            
            // Nom de la boutique
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: Text(
                store.name,
                style: const TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textDark,
                ),
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Widget de fallback si pas de logo
  Widget _buildFallbackIcon() {
    return Container(
      width: 50,
      height: 50,
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: const Icon(
        Icons.storefront,
        color: AppColors.primary,
        size: 28,
      ),
    );
  }
}

