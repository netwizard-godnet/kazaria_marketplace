import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'dart:async';
import '../../providers/store_provider.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../models/banner_model.dart';
import '../../models/product_model.dart';
import '../../models/store_model.dart';
import '../../services/product_service.dart';
import '../../widgets/product_card.dart';
import '../search/search_screen.dart';
import '../store/store_details_screen.dart';
import '../products/product_details_screen.dart';
import '../products/best_offers_list_screen.dart';

class StoresScreen extends StatefulWidget {
  const StoresScreen({super.key});

  @override
  State<StoresScreen> createState() => _StoresScreenState();
}

class _StoresScreenState extends State<StoresScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);

    // Charger les données initiales
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final storeProvider = Provider.of<StoreProvider>(context, listen: false);
      print('🔄 [STORES_SCREEN] Initialisation - Chargement des données');
      storeProvider.refreshAll();
      print('🔄 [STORES_SCREEN] Chargement du carousel boutique...');
      storeProvider.loadStoreCarousel().then((_) {
        print('✅ [STORES_SCREEN] Carousel chargé: ${storeProvider.storeCarouselBanners.length} bannières');
      });
      storeProvider.loadBestOffers();
      storeProvider.loadOfficialNewProducts();
      storeProvider.loadStoreAds();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Boutiques', style: TextStyle(color: Colors.white)),
        elevation: 0,
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            color: Colors.white,
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const SearchScreen()),
              );
            },
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.white,
          labelColor: AppColors.white,
          unselectedLabelColor: AppColors.white.withValues(alpha: 0.7),
          tabs: const [
            Tab(text: 'Toutes'),
            Tab(text: 'Officielles'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [_buildAllStoresTab(), _buildVerifiedStoresTab()],
      ),
    );
  }

  Widget _buildAllStoresTab() {
    return Consumer<StoreProvider>(
      builder: (context, storeProvider, _) {
        final filteredStores = storeProvider.stores
            .where((store) => store.isOfficial != true)
            .toList();
        const int staticSections = 5;
        final bool hasStandardStores = filteredStores.isNotEmpty;
        final bool showLoadMore =
            hasStandardStores && storeProvider.hasMoreData;
        final bool showNoStoreMessage = !hasStandardStores;
        final int dynamicRows =
            filteredStores.length +
            (showLoadMore ? 1 : 0) +
            (showNoStoreMessage ? 1 : 0);
        final int totalItems = staticSections + dynamicRows;

        if (storeProvider.isLoading && storeProvider.stores.isEmpty) {
          return const Center(child: CircularProgressIndicator());
        }

        if (storeProvider.error != null && storeProvider.stores.isEmpty) {
          return _buildErrorState(storeProvider.error!, () {
            storeProvider.loadStores(refresh: true);
          });
        }

        if (storeProvider.stores.isEmpty) {
          return _buildEmptyState();
        }

        return RefreshIndicator(
          onRefresh: () async {
            await storeProvider.loadStores(refresh: true);
            await storeProvider.loadStoreCarousel();
            await storeProvider.loadBestOffers(forceRefresh: true);
            await storeProvider.loadOfficialNewProducts(forceRefresh: true);
            await storeProvider.loadStoreAds();
          },
          child: ListView.builder(
            padding: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
            itemCount: totalItems,
            itemBuilder: (context, index) {
              // Carousel en premier
              if (index == 0) {
                return _buildStoreCarousel(storeProvider);
              }

              // Section Meilleures offres en deuxième
              if (index == 1) {
                return _buildBestOffersSection(storeProvider);
              }

              // Section Bannières publicitaires en troisième
              if (index == 2) {
                return _buildStoreAdsSection(storeProvider);
              }

              // Section Nouveautés officielles en quatrième
              if (index == 3) {
                return _buildOfficialNewProductsSection(storeProvider);
              }
              if (index == 4) {
                return const _OfficialStoreProductsSection();
              }

              // Ajuster l'index pour les boutiques
              int dynamicIndex = index - staticSections;

              if (!hasStandardStores) {
                // Afficher un message d'information puis arrêter
                if (dynamicIndex == 0) {
                  return _buildNoStandardStoresMessage();
                }
                return const SizedBox.shrink();
              }

              if (dynamicIndex < filteredStores.length) {
                final store = filteredStores[dynamicIndex];
                return Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppSizes.paddingMedium,
                    vertical: AppSizes.space2,
                  ),
                  child: _buildStoreCard(store),
                );
              }

              dynamicIndex -= filteredStores.length;

              if (showLoadMore && dynamicIndex == 0) {
                return _buildLoadMoreButton(storeProvider);
              }

              return const SizedBox.shrink();
            },
          ),
        );
      },
    );
  }

  Widget _buildVerifiedStoresTab() {
    return Consumer<StoreProvider>(
      builder: (context, storeProvider, _) {
        if (storeProvider.isLoadingVerified &&
            storeProvider.verifiedStores.isEmpty) {
          return const Center(child: CircularProgressIndicator());
        }

        if (storeProvider.verifiedStores.isEmpty) {
          return _buildEmptyState(
            message: 'Aucune boutique vérifiée pour le moment',
          );
        }

        return RefreshIndicator(
          onRefresh: () => storeProvider.loadVerifiedStores(),
          child: ListView.builder(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            itemCount: storeProvider.verifiedStores.length,
            itemBuilder: (context, index) {
              final store = storeProvider.verifiedStores[index];
              return _buildStoreCard(store);
            },
          ),
        );
      },
    );
  }

  Widget _buildStoreCard(StoreModel store) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space4),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: [
          BoxShadow(
            color: AppColors.shadow.withValues(alpha: 0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppSizes.radiusXL),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => StoreDetailsScreen(store: store),
              ),
            );
          },
          child: Padding(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            child: Row(
              children: [
                // Logo de la boutique
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                    color: AppColors.background,
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                    child: _buildStoreLogo(store),
                  ),
                ),
                const SizedBox(width: AppSizes.space4),

                // Informations de la boutique
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Nom et badges
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              store.name,
                              style: AppTextStyles.titleMedium.copyWith(
                                fontWeight: FontWeight.w600,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          if (store.isVerified) ...[
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: AppSizes.space2,
                                vertical: AppSizes.space1,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.success,
                                borderRadius: BorderRadius.circular(
                                  AppSizes.radiusSM,
                                ),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.verified,
                                    color: AppColors.white,
                                    size: 12,
                                  ),
                                  SizedBox(width: 4),
                                  Text(
                                    'Vérifiée',
                                    style: TextStyle(
                                      color: AppColors.white,
                                      fontSize: 10,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: AppSizes.space2),
                          ],
                          if (store.isOfficial) ...[
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: AppSizes.space2,
                                vertical: AppSizes.space1,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.primary,
                                borderRadius: BorderRadius.circular(
                                  AppSizes.radiusSM,
                                ),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.star,
                                    color: AppColors.white,
                                    size: 12,
                                  ),
                                  SizedBox(width: 4),
                                  Text(
                                    'Officielle',
                                    style: TextStyle(
                                      color: AppColors.white,
                                      fontSize: 10,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                      const SizedBox(height: AppSizes.space2),

                      // Description
                      if (store.description != null &&
                          store.description!.isNotEmpty)
                        Text(
                          store.description!,
                          style: AppTextStyles.bodySmall.copyWith(
                            color: AppColors.textMuted,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      const SizedBox(height: AppSizes.space2),

                      // Statistiques
                      Row(
                        children: [
                          _buildStatItem(
                            Icons.inventory,
                            '${store.totalProducts}',
                            'Produits',
                          ),
                          const SizedBox(width: AppSizes.space4),
                          _buildStatItem(
                            Icons.shopping_bag,
                            '${store.totalOrders}',
                            'Commandes',
                          ),
                          const SizedBox(width: AppSizes.space4),
                          _buildStatItem(
                            Icons.star,
                            store.rating.toStringAsFixed(1),
                            'Note',
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNoStandardStoresMessage() {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: AppSizes.paddingLarge,
        vertical: AppSizes.paddingMedium,
      ),
      child: Container(
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        decoration: BoxDecoration(
          color: AppColors.background,
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            Text('Aucune boutique standard', style: AppTextStyles.h4),
            SizedBox(height: AppSizes.space1),
            Text(
              'Les boutiques officielles sont désormais visibles dans l’onglet "Officielles".',
              style: AppTextStyles.bodySmall,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem(IconData icon, String value, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: AppColors.textMuted),
        const SizedBox(width: 4),
        Text(
          value,
          style: AppTextStyles.bodySmall.copyWith(
            fontWeight: FontWeight.w600,
            color: AppColors.textMuted,
          ),
        ),
      ],
    );
  }

  Widget _buildLoadMoreButton(StoreProvider storeProvider) {
    return Container(
      margin: const EdgeInsets.all(AppSizes.paddingMedium),
      child: ElevatedButton(
        onPressed: storeProvider.isLoading
            ? null
            : () => storeProvider.loadMoreStores(),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: AppColors.white,
          padding: const EdgeInsets.symmetric(vertical: AppSizes.paddingMedium),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          ),
        ),
        child: storeProvider.isLoading
            ? const SizedBox(
                height: 20,
                width: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(AppColors.white),
                ),
              )
            : const Text('Charger plus'),
      ),
    );
  }

  Widget _buildErrorState(String error, VoidCallback onRetry) {
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
              style: AppTextStyles.h3.copyWith(color: AppColors.textDark),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              error,
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppSizes.space4),
            ElevatedButton(
              onPressed: onRetry,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
              ),
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState({String? message}) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                color: AppColors.background,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.store_outlined,
                color: AppColors.textMuted,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Aucune boutique',
              style: AppTextStyles.h3.copyWith(color: AppColors.textDark),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              message ?? 'Aucune boutique disponible pour le moment',
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

  Widget _buildStoreLogo(StoreModel store) {
    String logoUrl;
    if (store.logoUrl?.isNotEmpty == true) {
      logoUrl = _fixImageUrl(store.logoUrl!);
    } else if (store.logo?.isNotEmpty == true) {
      logoUrl = _fixImageUrl(store.logo!);
    } else {
      logoUrl = '${ApiConfig.imageBaseUrl}/images/LOGO.jpg';
    }

    final cacheSafeUrl = _appendCacheBusting(logoUrl, store.updatedAt);

    return CachedNetworkImage(
      imageUrl: cacheSafeUrl,
      fit: BoxFit.cover,
      placeholder: (context, url) => Container(
        color: AppColors.background,
        child: const Icon(Icons.store, color: AppColors.textMuted, size: 40),
      ),
      errorWidget: (context, url, error) => Container(
        color: AppColors.background,
        child: const Icon(Icons.store, color: AppColors.textMuted, size: 40),
      ),
    );
  }

  String _appendCacheBusting(String url, DateTime? updatedAt) {
    if (url.isEmpty) return url;
    final timestamp =
        updatedAt?.millisecondsSinceEpoch ??
        DateTime.now().millisecondsSinceEpoch;
    final separator = url.contains('?') ? '&' : '?';
    return '$url${separator}v=$timestamp';
  }

  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    // Si l'URL est déjà complète et correcte
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }

    // ✅ CORRECTION : Si c'est "http:" sans "//"
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }

    // ✅ CORRECTION : Si c'est "https:" sans "//"
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }

    // Sinon, construire l'URL complète
    if (imagePath.startsWith('storage/')) {
      return '${ApiConfig.imageBaseUrl}/$imagePath';
    }
    return '${ApiConfig.imageBaseUrl}/storage/$imagePath';
  }

  /// Construire le carousel des boutiques
  Widget _buildStoreCarousel(StoreProvider storeProvider) {
    print('🎨 [STORES_SCREEN] _buildStoreCarousel appelé');
    print('   - isLoadingCarousel: ${storeProvider.isLoadingCarousel}');
    print('   - Nombre de bannières: ${storeProvider.storeCarouselBanners.length}');
    
    if (storeProvider.isLoadingCarousel) {
      print('⏳ [STORES_SCREEN] Carousel en cours de chargement...');
      return Container(
        height: 180,
        margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
        child: const Center(child: CircularProgressIndicator()),
      );
    }

    if (storeProvider.storeCarouselBanners.isEmpty) {
      print('⚠️ [STORES_SCREEN] Aucune bannière de carousel disponible');
      print('💡 [STORES_SCREEN] Vérifiez les logs du backend pour voir si les carousels sont trouvés');
      // Afficher un message de débogage temporaire
      return Container(
        height: 100,
        margin: const EdgeInsets.all(AppSizes.paddingMedium),
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        decoration: BoxDecoration(
          color: AppColors.warningLight,
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          border: Border.all(color: AppColors.warning),
        ),
        child: const Center(
          child: Text(
            'Aucun carousel trouvé (ordres 8, 9, 10, 12)',
            style: TextStyle(color: AppColors.warning),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    print('✅ [STORES_SCREEN] Affichage du carousel avec ${storeProvider.storeCarouselBanners.length} bannières');
    return _StoreCarouselWidget(banners: storeProvider.storeCarouselBanners);
  }

  /// Construire la section Meilleures offres
  Widget _buildBestOffersSection(StoreProvider storeProvider) {
    print('🎨 [STORES_SCREEN] _buildBestOffersSection appelé');
    print('   - isLoadingBestOffers: ${storeProvider.isLoadingBestOffers}');
    print('   - Nombre de produits: ${storeProvider.bestOffersProducts.length}');
    
    if (storeProvider.isLoadingBestOffers) {
      print('⏳ [STORES_SCREEN] Meilleures offres en cours de chargement...');
      return Container(
        height: 280,
        margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
        child: const Center(child: CircularProgressIndicator()),
      );
    }

    if (storeProvider.bestOffersProducts.isEmpty) {
      print('⚠️ [STORES_SCREEN] Aucune meilleure offre disponible');
      // Afficher un message au lieu de cacher complètement la section
      return Container(
        margin: const EdgeInsets.only(
          top: AppSizes.paddingMedium,
          bottom: AppSizes.paddingMedium,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingMedium,
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.local_offer,
                        color: AppColors.primary,
                        size: 24,
                      ),
                      const SizedBox(width: AppSizes.space2),
                      Text(
                        'Meilleures offres',
                        style: AppTextStyles.h3.copyWith(
                          fontWeight: FontWeight.w700,
                          color: AppColors.textDark,
                        ),
                      ),
                    ],
                  ),
                  // ✅ Afficher "Voir tout" s'il y a plusieurs produits (plus de 3)
                  if (storeProvider.bestOffersProducts.length > 3)
                    TextButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const BestOffersListScreen(),
                          ),
                        );
                      },
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Voir tout',
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.primary,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Icon(
                            Icons.arrow_forward_ios,
                            size: 14,
                            color: AppColors.primary,
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Container(
              height: 150,
              margin: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingMedium,
              ),
              decoration: BoxDecoration(
                color: AppColors.background,
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                border: Border.all(color: AppColors.border),
              ),
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.local_offer_outlined,
                      size: 48,
                      color: AppColors.textMuted,
                    ),
                    const SizedBox(height: AppSizes.space2),
                    Text(
                      'Aucune meilleure offre disponible',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: AppColors.textMuted,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      );
    }
    
    print('✅ [STORES_SCREEN] Affichage de ${storeProvider.bestOffersProducts.length} meilleures offres');

    return Container(
      margin: const EdgeInsets.only(
        top: AppSizes.paddingMedium,
        bottom: AppSizes.paddingMedium,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // En-tête de la section
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: AppSizes.paddingMedium,
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.local_offer,
                      color: AppColors.primary,
                      size: 24,
                    ),
                    const SizedBox(width: AppSizes.space2),
                Text(
                  'Meilleures offres',
                  style: AppTextStyles.h3.copyWith(
                    fontWeight: FontWeight.w700,
                    color: AppColors.textDark,
                  ),
                ),
                  ],
                ),
                // ✅ Afficher "Voir tout" s'il y a plusieurs produits (plus de 2 produits affichés)
                // Cela permet de voir tous les produits même s'il n'y en a que quelques-uns
                if (storeProvider.bestOffersProducts.length > 2 || storeProvider.bestOffersHasMore)
                  TextButton(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => const BestOffersListScreen(),
                        ),
                      );
                    },
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'Voir tout',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.primary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(width: 4),
                        Icon(
                          Icons.arrow_forward_ios,
                          size: 14,
                          color: AppColors.primary,
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: AppSizes.space4),
          // Liste horizontale des produits
          SizedBox(
            height: 260,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingMedium,
              ),
              itemCount: storeProvider.bestOffersProducts.length,
              itemBuilder: (context, index) {
                final product = storeProvider.bestOffersProducts[index];
                return Container(
                  width: 160,
                  margin: const EdgeInsets.only(right: AppSizes.space4),
                  child: ProductCard(
                    product: product,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) =>
                              ProductDetailsScreen(product: product),
                        ),
                      );
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

  /// Section nouveautés officiels
  Widget _buildOfficialNewProductsSection(StoreProvider storeProvider) {
    if (storeProvider.isLoadingOfficialNew) {
      return Container(
        height: 260,
        margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
        child: const Center(child: CircularProgressIndicator()),
      );
    }

    if (storeProvider.officialNewProducts.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.only(
        top: AppSizes.paddingMedium,
        bottom: AppSizes.paddingMedium,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: AppSizes.paddingMedium,
            ),
            child: Text(
              'Nouveautés officielles',
              style: AppTextStyles.h3.copyWith(
                fontWeight: FontWeight.w700,
                color: AppColors.textDark,
              ),
            ),
          ),
          const SizedBox(height: AppSizes.space4),
          SizedBox(
            height: 260,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingMedium,
              ),
              itemCount: storeProvider.officialNewProducts.length,
              itemBuilder: (context, index) {
                final product = storeProvider.officialNewProducts[index];
                return Container(
                  width: 160,
                  margin: const EdgeInsets.only(right: AppSizes.space4),
                  child: ProductCard(
                    product: product,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) =>
                              ProductDetailsScreen(product: product),
                        ),
                      );
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

  /// Construire la section des bannières publicitaires
  Widget _buildStoreAdsSection(StoreProvider storeProvider) {
    if (storeProvider.isLoadingAds) {
      return Container(
        height: 150,
        margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
        child: const Center(child: CircularProgressIndicator()),
      );
    }

    if (storeProvider.storeAdsBanners.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.only(
        top: AppSizes.paddingMedium,
        bottom: AppSizes.paddingMedium,
      ),
      child: _StoreAdsCarousel(
        banners: storeProvider.storeAdsBanners,
        imageResolver: _fixImageUrl,
      ),
    );
  }
}

class _OfficialStoreProductsSection extends StatefulWidget {
  const _OfficialStoreProductsSection();

  @override
  State<_OfficialStoreProductsSection> createState() =>
      _OfficialStoreProductsSectionState();
}

class _OfficialStoreProductsSectionState
    extends State<_OfficialStoreProductsSection> {
  final ProductService _productService = ProductService();
  List<ProductModel> _products = [];
  bool _isLoading = true;
  bool _inStockOnly = false;
  String _selectedSort = 'recent';
  String _sortBy = 'created_at';
  String _sortOrder = 'desc';

  final List<Map<String, String>> _sortOptions = const [
    {
      'key': 'recent',
      'label': 'Récent',
      'sort_by': 'created_at',
      'order': 'desc',
    },
    {'key': 'priceAsc', 'label': 'Prix ↑', 'sort_by': 'price', 'order': 'asc'},
    {
      'key': 'priceDesc',
      'label': 'Prix ↓',
      'sort_by': 'price',
      'order': 'desc',
    },
  ];

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    setState(() {
      _isLoading = true;
    });

    final response = await _productService.getProducts(
      sortBy: _sortBy,
      sortOrder: _sortOrder,
      inStock: _inStockOnly ? true : null,
      officialStoresOnly: true,
      limit: 20,
    );

    if (!mounted) return;

    if (response['success'] == true) {
      final rawList = response['products'] as List? ?? [];
      final parsed = rawList
          .whereType<Map<String, dynamic>>()
          .map(ProductModel.fromJson)
          .toList();

      setState(() {
        _products = parsed;
        _isLoading = false;
      });
    } else {
      setState(() {
        _products = [];
        _isLoading = false;
      });
    }
  }

  void _applySort(String key) {
    if (_selectedSort == key) return;
    final option = _sortOptions.firstWhere((o) => o['key'] == key);
    setState(() {
      _selectedSort = key;
      _sortBy = option['sort_by'] ?? 'created_at';
      _sortOrder = option['order'] ?? 'desc';
    });
    _loadProducts();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: AppSizes.paddingMedium,
        vertical: AppSizes.paddingMedium,
      ),
      padding: const EdgeInsets.all(AppSizes.paddingMedium),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Expanded(
                child: Text(
                  'Tous les produits des boutiques officielles',
                  style: AppTextStyles.h3,
                ),
              ),
              IconButton(
                onPressed: _loadProducts,
                icon: const Icon(Icons.refresh),
                tooltip: 'Actualiser',
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 4,
            children: [
              for (final option in _sortOptions)
                ChoiceChip(
                  label: Text(option['label']!),
                  selected: _selectedSort == option['key'],
                  onSelected: (_) => _applySort(option['key']!),
                ),
              FilterChip(
                label: const Text('En stock'),
                selected: _inStockOnly,
                onSelected: (value) {
                  setState(() {
                    _inStockOnly = value;
                  });
                  _loadProducts();
                },
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (_isLoading)
            const Center(child: CircularProgressIndicator())
          else if (_products.isEmpty)
            Column(
              children: const [
                SizedBox(height: 24),
                Icon(
                  Icons.inventory_2_outlined,
                  size: 60,
                  color: AppColors.textLight,
                ),
                SizedBox(height: 8),
                Text(
                  'Aucun produit officiel trouvé',
                  style: AppTextStyles.body,
                ),
              ],
            )
          else
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: 0.6,
              ),
              itemCount: _products.length,
              itemBuilder: (context, index) {
                final product = _products[index];
                return ProductCard(product: product);
              },
            ),
        ],
      ),
    );
  }
}

/// Widget carousel pour les boutiques
class _StoreCarouselWidget extends StatefulWidget {
  final List<BannerModel> banners;

  const _StoreCarouselWidget({required this.banners});

  @override
  State<_StoreCarouselWidget> createState() => _StoreCarouselWidgetState();
}

/// Carousel pour les publicités boutiques
class _StoreAdsCarousel extends StatefulWidget {
  final List<BannerModel> banners;
  final String Function(String) imageResolver;

  const _StoreAdsCarousel({required this.banners, required this.imageResolver});

  @override
  State<_StoreAdsCarousel> createState() => _StoreAdsCarouselState();
}

class _StoreAdsCarouselState extends State<_StoreAdsCarousel> {
  late final PageController _pageController;
  int _currentPage = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(viewportFraction: 0.9);
    _startAutoPlay();
  }

  void _startAutoPlay() {
    if (widget.banners.length <= 1) return;
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (!_pageController.hasClients) return;
      final nextPage = (_currentPage + 1) % widget.banners.length;
      _pageController.animateToPage(
        nextPage,
        duration: const Duration(milliseconds: 500),
        curve: Curves.easeOut,
      );
    });
  }

  @override
  void didUpdateWidget(covariant _StoreAdsCarousel oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.banners.length != widget.banners.length) {
      _currentPage = 0;
      _startAutoPlay();
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        SizedBox(
          height: 150,
          child: PageView.builder(
            controller: _pageController,
            itemCount: widget.banners.length,
            onPageChanged: (index) {
              setState(() {
                _currentPage = index;
              });
            },
            itemBuilder: (context, index) {
              final banner = widget.banners[index];
              final imageUrl = widget.imageResolver(banner.image);

              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8),
                child: _StoreAdCard(banner: banner, imageUrl: imageUrl),
              );
            },
          ),
        ),
        if (widget.banners.length > 1) ...[
          const SizedBox(height: 8),
          Center(
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  widget.banners.length,
                  (index) => AnimatedContainer(
                    duration: const Duration(milliseconds: 250),
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    height: 6,
                    width: _currentPage == index ? 20 : 8,
                    decoration: BoxDecoration(
                      color: _currentPage == index
                          ? AppColors.primary
                          : AppColors.primary.withValues(alpha: 0.3),
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ],
    );
  }
}

class _StoreAdCard extends StatelessWidget {
  final BannerModel banner;
  final String imageUrl;

  const _StoreAdCard({required this.banner, required this.imageUrl});

  void _handleTap() {
    // TODO: implémenter les actions selon actionType/actionData
    // Exemple: ouvrir une URL ou naviguer vers un produit.
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: _handleTap,
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          boxShadow: [
            BoxShadow(
              color: AppColors.shadow.withValues(alpha: 0.1),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          child: CachedNetworkImage(
            imageUrl: imageUrl,
            fit: BoxFit.cover,
            placeholder: (context, url) => Container(
              decoration: const BoxDecoration(
                gradient: AppColors.primaryGradient,
              ),
              child: const Center(
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(AppColors.white),
                ),
              ),
            ),
            errorWidget: (context, url, error) => Container(
              decoration: const BoxDecoration(
                gradient: AppColors.primaryGradient,
              ),
              child: const Icon(
                Icons.image_not_supported,
                color: AppColors.white,
                size: 32,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _StoreCarouselWidgetState extends State<_StoreCarouselWidget> {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    if (widget.banners.length > 1) {
      _startAutoPlay();
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  void _startAutoPlay() {
    _timer = Timer.periodic(const Duration(seconds: 5), (timer) {
      if (_pageController.hasClients) {
        int nextPage = (_currentPage + 1) % widget.banners.length;
        _pageController.animateToPage(
          nextPage,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );
      }
    });
  }

  String _fixImageUrl(String imagePath) {
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }
    if (imagePath.startsWith('storage/')) {
      return '${ApiConfig.imageBaseUrl}/$imagePath';
    }
    return '${ApiConfig.imageBaseUrl}/storage/$imagePath';
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 180,
      margin: const EdgeInsets.only(
        left: AppSizes.paddingMedium,
        right: AppSizes.paddingMedium,
        top: AppSizes.paddingMedium,
        bottom: AppSizes.paddingMedium,
      ),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: [
          BoxShadow(
            color: AppColors.shadow.withValues(alpha: 0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        child: Stack(
          children: [
            PageView.builder(
              controller: _pageController,
              onPageChanged: (index) {
                setState(() {
                  _currentPage = index;
                });
              },
              itemCount: widget.banners.length,
              itemBuilder: (context, index) {
                final banner = widget.banners[index];
                final imageUrl = _fixImageUrl(banner.image);

                return GestureDetector(
                  onTap: () {
                    // TODO: Gérer les actions (produit, catégorie, URL, etc.)
                    if (banner.actionType == 'url' &&
                        banner.actionData != null) {
                      // Ouvrir l'URL
                    } else if (banner.actionType == 'product' &&
                        banner.actionData != null) {
                      // Naviguer vers le produit
                    }
                  },
                  child: CachedNetworkImage(
                    imageUrl: imageUrl,
                    fit: BoxFit.cover,
                    placeholder: (context, url) => Container(
                      decoration: const BoxDecoration(
                        gradient: AppColors.primaryGradient,
                      ),
                      child: const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppColors.white,
                          ),
                        ),
                      ),
                    ),
                    errorWidget: (context, url, error) => Container(
                      decoration: const BoxDecoration(
                        gradient: AppColors.primaryGradient,
                      ),
                      child: const Icon(
                        Icons.image_not_supported,
                        color: AppColors.white,
                        size: 48,
                      ),
                    ),
                  ),
                );
              },
            ),
            // Indicateurs
            if (widget.banners.length > 1)
              Positioned(
                bottom: AppSizes.space4,
                left: 0,
                right: 0,
                child: Center(
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: List.generate(
                        widget.banners.length,
                        (index) => Container(
                          width: _currentPage == index ? 24 : 8,
                          height: 8,
                          margin: const EdgeInsets.symmetric(horizontal: 4),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(4),
                            color: _currentPage == index
                                ? AppColors.white
                                : AppColors.white.withValues(alpha: 0.5),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
