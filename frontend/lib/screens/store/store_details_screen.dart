import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../models/store_model.dart';
import '../../models/product_model.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../services/store_service.dart';
import '../../widgets/modern_product_card.dart';
import '../../widgets/share_button.dart';
import '../products/product_details_screen.dart';

class StoreDetailsScreen extends StatefulWidget {
  final StoreModel store;

  const StoreDetailsScreen({super.key, required this.store});

  @override
  State<StoreDetailsScreen> createState() => _StoreDetailsScreenState();
}

class _StoreSortOption {
  final String id;
  final String label;
  final String description;
  final String sortBy;
  final String sortOrder;
  final IconData icon;

  const _StoreSortOption({
    required this.id,
    required this.label,
    required this.description,
    required this.sortBy,
    required this.sortOrder,
    required this.icon,
  });
}

class _StoreDetailsScreenState extends State<StoreDetailsScreen> {
  final StoreService _storeService = StoreService();
  final List<ProductModel> _products = [];
  List<ProductModel> _bestOffers = [];
  List<ProductModel> _newProducts = [];
  final TextEditingController _searchController = TextEditingController();
  bool _isLoading = true;
  bool _isLoadingBestOffers = false;
  bool _isLoadingNewProducts = false;
  String? _error;
  int _currentPage = 1;
  bool _hasMoreData = true;
  StoreModel? _store; // détails actualisés (logo, bannière, stats)

  String _searchQuery = '';
  String _selectedSortId = 'new';
  String _sortBy = 'created_at';
  String _sortOrder = 'desc';
  double? _minPrice;
  double? _maxPrice;
  bool _inStockOnly = false;

  final List<_StoreSortOption> _sortOptions = const [
    _StoreSortOption(
      id: 'new',
      label: 'Nouveautés',
      description: 'Derniers produits ajoutés',
      sortBy: 'created_at',
      sortOrder: 'desc',
      icon: Icons.schedule,
    ),
    _StoreSortOption(
      id: 'price_asc',
      label: 'Prix croissant',
      description: 'Moins cher au plus cher',
      sortBy: 'price',
      sortOrder: 'asc',
      icon: Icons.arrow_upward,
    ),
    _StoreSortOption(
      id: 'price_desc',
      label: 'Prix décroissant',
      description: 'Plus cher au moins cher',
      sortBy: 'price',
      sortOrder: 'desc',
      icon: Icons.arrow_downward,
    ),
    _StoreSortOption(
      id: 'rating',
      label: 'Mieux notés',
      description: 'Avis clients',
      sortBy: 'rating',
      sortOrder: 'desc',
      icon: Icons.star,
    ),
    _StoreSortOption(
      id: 'sales',
      label: 'Meilleures ventes',
      description: 'Produits les plus populaires',
      sortBy: 'sales',
      sortOrder: 'desc',
      icon: Icons.trending_up,
    ),
  ];

  String _resolveImageUrl(String? path) {
    if (path == null || path.isEmpty) return '';

    // Si l'URL est déjà complète et correcte
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }

    // ✅ CORRECTION : Si c'est "http:" sans "//" (erreur commune)
    if (path.startsWith('http:') && !path.startsWith('http://')) {
      return path.replaceFirst('http:', 'http://');
    }

    // ✅ CORRECTION : Si c'est "https:" sans "//"
    if (path.startsWith('https:') && !path.startsWith('https://')) {
      return path.replaceFirst('https:', 'https://');
    }

    // Fallback pour les anciens chemins (au cas où)
    if (path.startsWith('storage/')) {
      return '${ApiConfig.imageBaseUrl}/$path';
    }
    return '${ApiConfig.imageBaseUrl}/storage/$path';
  }

  String _withCacheBusting(String url, StoreModel store) {
    if (url.isEmpty) return url;
    final timestamp =
        store.updatedAt?.millisecondsSinceEpoch ??
        DateTime.now().millisecondsSinceEpoch;
    final separator = url.contains('?') ? '&' : '?';
    return '$url$separator'
        'v=$timestamp';
  }

  @override
  void initState() {
    super.initState();
    _loadStoreDetails();
    _loadProducts();
    // Charger les meilleures offres et nouveautés
    _loadBestOffers();
    _loadNewProducts();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadStoreDetails() async {
    try {
      final resp = await _storeService.getStoreDetails(widget.store.id);
      if (resp['success'] == true && resp['store'] != null) {
        // Adapter le JSON vers StoreModel si nécessaire
        final data = resp['store'];
        print('📦 [STORE_DETAILS] Données reçues du backend:');
        print('   - Description: ${data['description']}');
        print('   - Address: ${data['address']}');
        print('   - City: ${data['city']}');
        print('   - Phone: ${data['phone']}');
        print('   - Email: ${data['email']}');
        print('   - Logo: ${data['logo']}');
        print('   - LogoUrl: ${data['logo_url']}');
        print('   - Banner: ${data['banner']}');
        print('   - BannerUrl: ${data['banner_url']}');
        setState(() {
          _store = StoreModel(
            id: widget.store.id,
            userId: widget.store.userId,
            name: data['name'] ?? widget.store.name,
            slug: data['slug'] ?? widget.store.slug,
            description: data['description'] ?? widget.store.description,
            categoryId: data['category_id'] ?? widget.store.categoryId,
            subcategoryId: data['subcategory_id'] ?? widget.store.subcategoryId,
            category: data['category'] ?? widget.store.category,
            subcategory: data['subcategory'] ?? widget.store.subcategory,
            phone: data['phone'] ?? widget.store.phone,
            email: data['email'] ?? widget.store.email,
            address: data['address'] ?? widget.store.address,
            city: data['city'] ?? widget.store.city,
            // Le backend retourne maintenant des URLs complètes
            logo: data['logo'] ?? widget.store.logo,
            banner: data['banner'] ?? widget.store.banner,
            logoUrl: data['logo_url'] ?? widget.store.logoUrl,
            bannerUrl: data['banner_url'] ?? widget.store.bannerUrl,
            status: widget.store.status,
            isVerified:
                (data['is_verified'] ?? widget.store.isVerified) == true,
            isOfficial:
                (data['is_official'] ?? widget.store.isOfficial) == true,
            commissionRate: widget.store.commissionRate,
            businessHours: data['business_hours'] ?? widget.store.businessHours,
            socialLinks: data['social_links'] ?? widget.store.socialLinks,
            totalProducts:
                data['totalProducts'] ??
                data['total_products'] ??
                widget.store.totalProducts,
            totalOrders:
                data['totalOrders'] ??
                data['total_orders'] ??
                widget.store.totalOrders,
            totalSales: widget.store.totalSales,
            rating: (data['rating'] ?? widget.store.rating)?.toDouble() ?? 0,
            reviewsCount: data['reviews_count'] ?? widget.store.reviewsCount,
            createdAt: widget.store.createdAt,
            updatedAt: widget.store.updatedAt,
          );
        });
      }
    } catch (_) {}
  }

  Future<void> _loadProducts({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _products.clear();
      _hasMoreData = true;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      print(
        '🔄 [STORE_DETAILS] Chargement produits boutique: ${widget.store.id}',
      );

      final response = await _storeService.getStoreProducts(
        widget.store.id,
        page: _currentPage,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        sortBy: _sortBy,
        sortOrder: _sortOrder,
        minPrice: _minPrice,
        maxPrice: _maxPrice,
        inStockOnly: _inStockOnly,
      );

      if (response['success']) {
        print(
          '📊 [STORE_DETAILS] Type response[products]: ${response['products'].runtimeType}',
        );

        final productsRaw = response['products'];

        // Vérifier que productsRaw est bien une liste
        if (productsRaw is! List) {
          print(
            '❌ [STORE_DETAILS] products n\'est pas une List: ${productsRaw.runtimeType}',
          );
          _error = 'Format de données invalide (products n\'est pas une liste)';
          setState(() {
            _isLoading = false;
          });
          return;
        }

        final List<dynamic> productsData = productsRaw;
        print('📊 [STORE_DETAILS] Nombre de produits: ${productsData.length}');

        final List<ProductModel> newProducts = [];

        for (var i = 0; i < productsData.length; i++) {
          try {
            final productData = productsData[i];
            print(
              '📊 [STORE_DETAILS] Produit $i type: ${productData.runtimeType}',
            );

            if (productData is! Map<String, dynamic>) {
              print(
                '❌ [STORE_DETAILS] Produit $i n\'est pas une Map: ${productData.runtimeType}',
              );
              continue;
            }

            newProducts.add(ProductModel.fromJson(productData));
          } catch (e) {
            print('❌ [STORE_DETAILS] Erreur parsing produit $i: $e');
          }
        }

        if (refresh) {
          _products.clear();
        }
        _products.addAll(newProducts);

        // Gestion de la pagination
        final pagination = response['pagination'] ?? {};
        final lastPage = pagination['last_page'] ?? 1;
        _hasMoreData = _currentPage < lastPage;

        print('✅ [STORE_DETAILS] Produits chargés: ${_products.length}');
      } else {
        _error =
            response['message'] ?? 'Erreur lors du chargement des produits';
        print('❌ [STORE_DETAILS] Erreur: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('💥 [STORE_DETAILS] Exception: $_error');
    }

    setState(() {
      _isLoading = false;
    });
  }

  Future<void> _loadMoreProducts() async {
    if (_hasMoreData && !_isLoading) {
      _currentPage++;
      await _loadProducts();
    }
  }

  Future<void> _loadBestOffers() async {
    setState(() {
      _isLoadingBestOffers = true;
    });

    try {
      final response = await _storeService.getStoreProducts(
        widget.store.id,
        page: 1,
        sortBy: 'created_at',
        sortOrder: 'desc',
      );

      if (response['success'] && response['products'] is List) {
        final allProducts = (response['products'] as List)
            .map((e) => ProductModel.fromJson(e))
            .toList();
        
        // Filtrer les produits avec is_best_offer = true
        setState(() {
          _bestOffers = allProducts
              .where((p) => p.isBestOffer == true)
              .take(10)
              .toList();
        });
        print('✅ [STORE_DETAILS] Meilleures offres chargées: ${_bestOffers.length}');
      }
    } catch (e) {
      print('❌ [STORE_DETAILS] Erreur chargement meilleures offres: $e');
    } finally {
      setState(() {
        _isLoadingBestOffers = false;
      });
    }
  }

  Future<void> _loadNewProducts() async {
    setState(() {
      _isLoadingNewProducts = true;
    });

    try {
      final response = await _storeService.getStoreProducts(
        widget.store.id,
        page: 1,
        sortBy: 'created_at',
        sortOrder: 'desc',
      );

      if (response['success'] && response['products'] is List) {
        final allProducts = (response['products'] as List)
            .map((e) => ProductModel.fromJson(e))
            .toList();
        
        // Filtrer les produits avec is_new = true ou récents
        setState(() {
          _newProducts = allProducts
              .where((p) => p.isNew == true)
              .take(10)
              .toList();
        });
        print('✅ [STORE_DETAILS] Nouveautés chargées: ${_newProducts.length}');
      }
    } catch (e) {
      print('❌ [STORE_DETAILS] Erreur chargement nouveautés: $e');
    } finally {
      setState(() {
        _isLoadingNewProducts = false;
      });
    }
  }

  Future<void> _refresh() async {
    print('🔄 [STORE_DETAILS] Rafraîchissement manuel');
    await Future.wait([
      _loadStoreDetails(),
      _loadProducts(refresh: true),
      _loadBestOffers(),
      _loadNewProducts(),
    ]);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('✅ Produits mis à jour'),
          duration: Duration(seconds: 1),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  void _onSearchSubmitted(String value) {
    final trimmed = value.trim();
    setState(() {
      _searchQuery = trimmed;
    });
    _loadProducts(refresh: true);
  }

  void _clearSearch() {
    _searchController.clear();
    if (_searchQuery.isEmpty) return;
    setState(() {
      _searchQuery = '';
    });
    _loadProducts(refresh: true);
  }

  void _openFilters() {
    final TextEditingController minPriceController = TextEditingController(
      text: _minPrice?.toStringAsFixed(0) ?? '',
    );
    final TextEditingController maxPriceController = TextEditingController(
      text: _maxPrice?.toStringAsFixed(0) ?? '',
    );
    bool tempInStockOnly = _inStockOnly;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) => SafeArea(
            child: Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.all(AppSizes.paddingLarge),
                    child: Row(
                      children: [
                        const Text('Filtres et tri', style: AppTextStyles.h3),
                        const Spacer(),
                        TextButton(
                          onPressed: () {
                            setModalState(() {
                              _selectedSortId = 'new';
                              _sortBy = 'created_at';
                              _sortOrder = 'desc';
                              minPriceController.clear();
                              maxPriceController.clear();
                              tempInStockOnly = false;
                            });
                          },
                          child: const Text('Réinitialiser'),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.of(context).pop(),
                        ),
                      ],
                    ),
                  ),
                  // Tri
                  Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSizes.paddingLarge,
                    ),
                    child: const Text(
                      'Trier par',
                      style: AppTextStyles.h4,
                    ),
                  ),
                  ..._sortOptions.map(
                    (option) => RadioListTile<String>(
                      value: option.id,
                      groupValue: _selectedSortId,
                      onChanged: (value) {
                        if (value == null) return;
                        setModalState(() {
                          _selectedSortId = value;
                          _sortBy = option.sortBy;
                          _sortOrder = option.sortOrder;
                        });
                      },
                      title: Text(option.label),
                      subtitle: Text(option.description),
                      secondary: Icon(option.icon, color: AppColors.primary),
                    ),
                  ),
                  const Divider(),
                  // Filtres de prix
                  Padding(
                    padding: const EdgeInsets.all(AppSizes.paddingLarge),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Filtrer par prix', style: AppTextStyles.h4),
                        const SizedBox(height: AppSizes.space3),
                        Row(
                          children: [
                            Expanded(
                              child: TextField(
                                controller: minPriceController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Prix minimum (FCFA)',
                                  border: OutlineInputBorder(),
                                ),
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: TextField(
                                controller: maxPriceController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Prix maximum (FCFA)',
                                  border: OutlineInputBorder(),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  // Disponibilité
                  CheckboxListTile(
                    title: const Text('En stock uniquement'),
                    value: tempInStockOnly,
                    onChanged: (value) {
                      setModalState(() {
                        tempInStockOnly = value ?? false;
                      });
                    },
                  ),
                  const SizedBox(height: AppSizes.space4),
                  // Bouton appliquer
                  Padding(
                    padding: const EdgeInsets.all(AppSizes.paddingLarge),
                    child: SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () {
                          setState(() {
                            _minPrice = minPriceController.text.isNotEmpty
                                ? double.tryParse(minPriceController.text)
                                : null;
                            _maxPrice = maxPriceController.text.isNotEmpty
                                ? double.tryParse(maxPriceController.text)
                                : null;
                            _inStockOnly = tempInStockOnly;
                          });
                          Navigator.of(context).pop();
                          _loadProducts(refresh: true);
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: AppColors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                        ),
                        child: const Text('Appliquer les filtres'),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  void _applySort(_StoreSortOption option) {
    setState(() {
      _selectedSortId = option.id;
      _sortBy = option.sortBy;
      _sortOrder = option.sortOrder;
    });
    _loadProducts(refresh: true);
  }

  String get _currentSortLabel =>
      _sortOptions.firstWhere((o) => o.id == _selectedSortId).label;

  bool get _hasActiveSearch => _searchQuery.isNotEmpty;

  /// Obtenir l'URL complète de la bannière
  String _getBannerUrl(StoreModel store) {
    String bannerUrl;
    if (store.bannerUrl != null && store.bannerUrl!.isNotEmpty) {
      bannerUrl = store.bannerUrl!;
    } else if (store.banner != null && store.banner!.isNotEmpty) {
      bannerUrl = _resolveImageUrl(store.banner);
    } else {
      bannerUrl =
          '${ApiConfig.imageBaseUrl}/storage/stores/banners/default-banner.jpg';
    }
    return _withCacheBusting(bannerUrl, store);
  }

  /// Obtenir l'URL complète du logo
  String _getLogoUrl(StoreModel store) {
    String logoUrl;
    if (store.logoUrl != null && store.logoUrl!.isNotEmpty) {
      logoUrl = store.logoUrl!;
    } else if (store.logo != null && store.logo!.isNotEmpty) {
      logoUrl = _resolveImageUrl(store.logo);
    } else {
      logoUrl =
          '${ApiConfig.imageBaseUrl}/storage/stores/logos/default-store.png';
    }
    return _withCacheBusting(logoUrl, store);
  }

  @override
  Widget build(BuildContext context) {
    final store = _store ?? widget.store;
    final String bannerUrl = _getBannerUrl(store);
    final String logoUrl = _getLogoUrl(store);

    print('🖼️ [STORE_DETAILS] Banner raw: ${store.banner}');
    print('🖼️ [STORE_DETAILS] BannerUrl: ${store.bannerUrl}');
    print('🖼️ [STORE_DETAILS] Banner URL final: $bannerUrl');
    print('🖼️ [STORE_DETAILS] Logo raw: ${store.logo}');
    print('🖼️ [STORE_DETAILS] LogoUrl: ${store.logoUrl}');
    print('🖼️ [STORE_DETAILS] Logo URL final: $logoUrl');
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          // App Bar with banner
          SliverAppBar(
            expandedHeight: 220,
            pinned: true,
            actions: [
              // Bouton rafraîchir
              IconButton(
                icon: const Icon(Icons.refresh),
                tooltip: 'Actualiser',
                onPressed: _refresh,
              ),
              ShareButton(
                type: 'store',
                id: store.id,
                name: store.name,
                slug: store.slug,
                description: store.description,
                isCompact: true,
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: CachedNetworkImage(
                imageUrl: bannerUrl,
                fit: BoxFit.cover,
                placeholder: (context, url) => Container(
                  color: AppColors.primary,
                  child: const Center(
                    child: CircularProgressIndicator(color: Colors.white),
                  ),
                ),
                errorWidget: (context, url, error) {
                  print('❌ [STORE_DETAILS] Erreur bannière: $url');
                  print('❌ [STORE_DETAILS] Erreur: $error');
                  return Container(
                    color: AppColors.primary,
                    child: const Center(
                      child: Icon(
                        Icons.broken_image,
                        color: Colors.white,
                        size: 50,
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
          // Store info with logo below banner
          SliverToBoxAdapter(
            child: Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              child: Row(
                children: [
                  // Logo
                  Container(
                    width: 90,
                    height: 90,
                    decoration: BoxDecoration(
                      color: AppColors.white,
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.white, width: 4),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.15),
                          blurRadius: 15,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: ClipOval(
                      child: CachedNetworkImage(
                        imageUrl: logoUrl,
                        fit: BoxFit.cover,
                        placeholder: (context, url) => Container(
                          color: Colors.grey[200],
                          child: const Center(
                            child: CircularProgressIndicator(),
                          ),
                        ),
                        errorWidget: (context, url, error) {
                          print('❌ [STORE_DETAILS] Erreur logo: $url');
                          print('❌ [STORE_DETAILS] Erreur: $error');
                          return const Icon(
                            Icons.store,
                            size: 45,
                            color: AppColors.primary,
                          );
                        },
                      ),
                    ),
                  ),
                  const SizedBox(width: 20),
                  // Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(store.name, style: AppTextStyles.h2),
                            ),
                            if (store.isOfficial)
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: AppColors.info,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.verified,
                                      color: AppColors.white,
                                      size: 16,
                                    ),
                                    SizedBox(width: 4),
                                    Text(
                                      'Officielle',
                                      style: TextStyle(
                                        color: AppColors.white,
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(
                              Icons.star,
                              color: AppColors.warning,
                              size: 16,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              '${store.rating} (${store.reviewsCount} avis)',
                              style: AppTextStyles.bodySmall,
                            ),
                          ],
                        ),
                        if (store.category != null || store.subcategory != null) ...[
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              const Icon(
                                Icons.category,
                                color: AppColors.textMedium,
                                size: 14,
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  store.category != null && store.subcategory != null
                                      ? '${store.category!['name']} • ${store.subcategory!['name']}'
                                      : store.category != null
                                          ? store.category!['name']
                                          : store.subcategory!['name'],
                                  style: AppTextStyles.bodySmall.copyWith(
                                    color: AppColors.textMedium,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          SliverToBoxAdapter(child: _buildSearchAndFilters()),
          // Stats
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingLarge,
              ),
              child: Row(
                children: [
                  Expanded(
                    child: _buildStatItem('${store.totalProducts}', 'Produits'),
                  ),
                  Expanded(
                    child: _buildStatItem('${store.reviewsCount}', 'Avis'),
                  ),
                ],
              ),
            ),
          ),
          // Informations de la boutique
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingLarge,
              ),
              child: Container(
                margin: const EdgeInsets.only(bottom: AppSizes.paddingLarge),
                padding: const EdgeInsets.all(AppSizes.paddingLarge),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                  boxShadow: AppShadows.shadowMD,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // À propos
                    if (store.description != null &&
                        store.description!.isNotEmpty) ...[
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(
                              Icons.info_outline,
                              color: AppColors.primary,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Text('À propos', style: AppTextStyles.h4),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        store.description!,
                        style: AppTextStyles.body.copyWith(
                          color: AppColors.textMedium,
                          height: 1.5,
                        ),
                      ),
                      const SizedBox(height: 20),
                      Divider(color: AppColors.grey200, height: 1),
                      const SizedBox(height: 20),
                    ],

                    // Localisation
                    if ((store.address != null && store.address!.isNotEmpty) ||
                        (store.city != null && store.city!.isNotEmpty)) ...[
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.error.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(
                              Icons.location_on,
                              color: AppColors.error,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Text('Localisation', style: AppTextStyles.h4),
                        ],
                      ),
                      const SizedBox(height: 12),
                      if (store.city != null && store.city!.isNotEmpty)
                        Text(
                          store.city!,
                          style: AppTextStyles.body.copyWith(
                            color: AppColors.textDark,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      if (store.address != null &&
                          store.address!.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          store.address!,
                          style: AppTextStyles.body.copyWith(
                            color: AppColors.textMedium,
                          ),
                        ),
                      ],
                      const SizedBox(height: 20),
                      Divider(color: AppColors.grey200, height: 1),
                      const SizedBox(height: 20),
                    ],

                    // Contact
                    if ((store.email != null && store.email!.isNotEmpty) ||
                        (store.phone != null && store.phone!.isNotEmpty)) ...[
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.error.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(
                              Icons.phone,
                              color: AppColors.error,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Text('Contact', style: AppTextStyles.h4),
                        ],
                      ),
                      const SizedBox(height: 12),
                      if (store.email != null && store.email!.isNotEmpty)
                        Row(
                          children: [
                            const Icon(
                              Icons.email_outlined,
                              size: 18,
                              color: AppColors.textMedium,
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                store.email!,
                                style: AppTextStyles.body.copyWith(
                                  color: AppColors.textMedium,
                                ),
                              ),
                            ),
                          ],
                        ),
                      if (store.phone != null && store.phone!.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            const Icon(
                              Icons.phone_android,
                              size: 18,
                              color: AppColors.textMedium,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              store.phone!,
                              style: AppTextStyles.body.copyWith(
                                color: AppColors.textMedium,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ],
                ),
              ),
            ),
          ),
          // Meilleures offres
          if (_bestOffers.isNotEmpty || _isLoadingBestOffers)
            SliverToBoxAdapter(
              child: _buildProductSection(
                title: 'Meilleures offres',
                icon: Icons.local_offer,
                products: _bestOffers,
                isLoading: _isLoadingBestOffers,
              ),
            ),
          // Nouveautés
          if (_newProducts.isNotEmpty || _isLoadingNewProducts)
            SliverToBoxAdapter(
              child: _buildProductSection(
                title: 'Nouveautés',
                icon: Icons.new_releases,
                products: _newProducts,
                isLoading: _isLoadingNewProducts,
              ),
            ),
          // Products section title
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.all(AppSizes.paddingLarge),
              child: Row(
                children: [
                  Text('Tous les produits', style: AppTextStyles.h3),
                ],
              ),
            ),
          ),
          // Products grid
          _isLoading && _products.isEmpty
              ? const SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(child: CircularProgressIndicator()),
                )
              : _error != null && _products.isEmpty
              ? SliverFillRemaining(
                  hasScrollBody: false,
                  child: _buildErrorState(),
                )
              : _products.isEmpty
              ? SliverFillRemaining(
                  hasScrollBody: false,
                  child: _buildEmptyState(),
                )
              : SliverPadding(
                  padding: const EdgeInsets.all(AppSizes.paddingMedium),
                  sliver: SliverGrid(
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          childAspectRatio: 0.75,
                        ),
                    delegate: SliverChildBuilderDelegate((context, index) {
                      if (index == _products.length) {
                        // Bouton "Charger plus"
                        return _buildLoadMoreButton();
                      }
                      final product = _products[index];
                      return ModernProductCard(
                        product: product,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ProductDetailsScreen(
                                product: product,
                                heroTag: 'store_${widget.store.id}_product_${product.id}',
                              ),
                            ),
                          );
                        },
                      );
                    }, childCount: _products.length + (_hasMoreData ? 1 : 0)),
                  ),
                ),
        ],
      ),
    );
  }

  Widget _buildSearchAndFilters() {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: AppSizes.paddingLarge,
        vertical: AppSizes.space4,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextField(
            controller: _searchController,
            textInputAction: TextInputAction.search,
            onSubmitted: _onSearchSubmitted,
            onChanged: (_) => setState(() {}),
            decoration: InputDecoration(
              hintText: 'Rechercher un produit dans la boutique',
              filled: true,
              fillColor: AppColors.white,
              prefixIcon: const Icon(Icons.search),
              suffixIcon: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (_searchController.text.isNotEmpty)
                    IconButton(
                      icon: const Icon(Icons.clear),
                      onPressed: _clearSearch,
                    ),
                  IconButton(
                    icon: const Icon(Icons.filter_list),
                    onPressed: _openFilters,
                  ),
                ],
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                borderSide: BorderSide.none,
              ),
            ),
          ),
          const SizedBox(height: AppSizes.space3),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              InputChip(
                avatar: const Icon(Icons.sort, size: 18),
                label: Text('Tri : $_currentSortLabel'),
                onPressed: _openFilters,
              ),
              if (_hasActiveSearch)
                InputChip(
                  label: Text('Recherche: $_searchQuery'),
                  onDeleted: _clearSearch,
                  deleteIcon: const Icon(Icons.close),
                ),
              if (_minPrice != null || _maxPrice != null)
                InputChip(
                  label: Text(
                    _minPrice != null && _maxPrice != null
                        ? 'Prix: ${_minPrice!.toInt()}-${_maxPrice!.toInt()} FCFA'
                        : _minPrice != null
                            ? 'Prix: >${_minPrice!.toInt()} FCFA'
                            : 'Prix: <${_maxPrice!.toInt()} FCFA',
                  ),
                  onDeleted: () {
                    setState(() {
                      _minPrice = null;
                      _maxPrice = null;
                    });
                    _loadProducts(refresh: true);
                  },
                  deleteIcon: const Icon(Icons.close, size: 18),
                ),
              if (_inStockOnly)
                InputChip(
                  label: const Text('En stock uniquement'),
                  onDeleted: () {
                    setState(() {
                      _inStockOnly = false;
                    });
                    _loadProducts(refresh: true);
                  },
                  deleteIcon: const Icon(Icons.close, size: 18),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem(String value, String label) {
    return Column(
      children: [
        Text(value, style: AppTextStyles.h3.copyWith(color: AppColors.primary)),
        const SizedBox(height: 4),
        Text(label, style: AppTextStyles.caption),
      ],
    );
  }

  Widget _buildLoadMoreButton() {
    return Container(
      margin: const EdgeInsets.all(AppSizes.paddingMedium),
      child: ElevatedButton(
        onPressed: _isLoading ? null : _loadMoreProducts,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: AppColors.white,
          padding: const EdgeInsets.symmetric(vertical: AppSizes.paddingMedium),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          ),
        ),
        child: _isLoading
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

  Widget _buildErrorState() {
    return Center(
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(AppSizes.paddingLarge),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.errorLight,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.error_outline,
                  color: AppColors.error,
                  size: 48,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Erreur de chargement',
                style: AppTextStyles.h3.copyWith(color: AppColors.textDark),
              ),
              const SizedBox(height: AppSizes.space2),
              Text(
                _error ?? 'Une erreur est survenue',
                style: AppTextStyles.bodyMedium.copyWith(
                  color: AppColors.textMuted,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: AppSizes.space4),
              ElevatedButton(
                onPressed: () => _loadProducts(refresh: true),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: AppColors.white,
                ),
                child: const Text('Réessayer'),
              ),
            ],
          ),
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
                color: AppColors.background,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.inventory_2_outlined,
                color: AppColors.textMuted,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Aucun produit',
              style: AppTextStyles.h3.copyWith(color: AppColors.textDark),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              'Cette boutique n\'a pas encore de produits disponibles',
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppSizes.space4),
            ElevatedButton(
              onPressed: () => _loadProducts(refresh: true),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
              ),
              child: const Text('Actualiser'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSocialButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    required Color color,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 20),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.w600,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _openUrl(String url) {
    // Utiliser url_launcher si disponible, sinon afficher un message
    print('Ouverture URL: $url');
    // TODO: Implémenter url_launcher
  }

  Widget _buildProductSection({
    required String title,
    required IconData icon,
    required List<ProductModel> products,
    required bool isLoading,
  }) {
    if (isLoading && products.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingLarge,
              ),
              child: Row(
                children: [
                  Icon(icon, color: AppColors.primary),
                  const SizedBox(width: 8),
                  Text(title, style: AppTextStyles.h3),
                ],
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              height: 280,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSizes.paddingLarge,
                ),
                itemCount: 4,
                itemBuilder: (context, index) {
                  return Container(
                    width: 180,
                    margin: const EdgeInsets.only(right: 12),
                    decoration: BoxDecoration(
                      color: AppColors.grey100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Center(
                      child: CircularProgressIndicator(),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      );
    }

    if (products.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: AppSizes.paddingLarge,
            ),
            child: Row(
              children: [
                Icon(icon, color: AppColors.primary),
                const SizedBox(width: 8),
                Text(title, style: AppTextStyles.h3),
              ],
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 280,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingLarge,
              ),
              itemCount: products.length,
              itemBuilder: (context, index) {
                final product = products[index];
                return Container(
                  width: 180,
                  margin: const EdgeInsets.only(right: 12),
                  child: ModernProductCard(
                    product: product,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ProductDetailsScreen(
                            product: product,
                          ),
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
}
