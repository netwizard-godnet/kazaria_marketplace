import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../providers/product_provider.dart';
import '../../utils/constants.dart';
import '../../widgets/modern_product_card.dart';
import '../../config/api_config.dart';
import 'product_details_screen.dart';

/// Page dédiée aux produits d'une marque spécifique
class BrandProductsScreen extends StatefulWidget {
  final String brandName;
  final String? brandImageUrl;

  const BrandProductsScreen({
    super.key,
    required this.brandName,
    this.brandImageUrl,
  });

  @override
  State<BrandProductsScreen> createState() => _BrandProductsScreenState();
}

class _BrandProductsScreenState extends State<BrandProductsScreen> {
  final ScrollController _scrollController = ScrollController();
  int _currentPage = 1;
  bool _isLoadingMore = false;
  bool _hasMore = true;
  bool _isInitialLoading = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadProducts();
    });
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent * 0.8) {
      _loadMoreProducts();
    }
  }

  Future<void> _loadProducts({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _hasMore = true;
    }
    
    setState(() {
      if (_currentPage == 1) {
        _isInitialLoading = true;
      }
    });

    final provider = Provider.of<ProductProvider>(context, listen: false);
    await provider.loadProducts(
      categoryId: null,
      search: widget.brandName, // ✅ Rechercher par nom de marque
      sortBy: 'created_at',
      specialCategory: null,
      page: _currentPage,
      limit: 20,
    );
    
    if (mounted) {
      setState(() {
        _isInitialLoading = false;
        // Vérifier s'il y a plus de produits
        if (provider.allProducts.length < 20 * _currentPage) {
          _hasMore = false;
        }
      });
    }
  }

  Future<void> _loadMoreProducts() async {
    if (_isLoadingMore || !_hasMore) return;
    
    setState(() => _isLoadingMore = true);
    _currentPage++;
    
    final provider = Provider.of<ProductProvider>(context, listen: false);
    final previousCount = provider.allProducts.length;
    
    await provider.loadProducts(
      categoryId: null,
      search: widget.brandName,
      sortBy: 'created_at',
      specialCategory: null,
      page: _currentPage,
      limit: 20,
    );
    
    if (mounted) {
      setState(() {
        // Vérifier s'il y a plus de produits
        if (provider.allProducts.length == previousCount) {
          _hasMore = false;
        }
        _isLoadingMore = false;
      });
    }
  }

  Future<void> _refresh() async {
    await _loadProducts(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            // Logo de la marque si disponible
            if (widget.brandImageUrl != null && widget.brandImageUrl!.isNotEmpty)
              Container(
                width: 32,
                height: 32,
                margin: const EdgeInsets.only(right: 12),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.grey200),
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: CachedNetworkImage(
                    imageUrl: _getBrandImageUrl(widget.brandImageUrl!),
                    fit: BoxFit.contain,
                    placeholder: (context, url) => const SizedBox(
                      width: 32,
                      height: 32,
                      child: Center(
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                    ),
                    errorWidget: (context, url, error) => const SizedBox(),
                  ),
                ),
              ),
            Expanded(
              child: Text(
                'Produits ${widget.brandName}',
                style: AppTextStyles.h3,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        elevation: 0,
      ),
      body: Consumer<ProductProvider>(
        builder: (context, provider, _) {
          if (_isInitialLoading && provider.allProducts.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.allProducts.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 64,
                    color: AppColors.error,
                  ),
                  const SizedBox(height: AppSizes.space4),
                  Text(
                    'Erreur de chargement',
                    style: AppTextStyles.h3,
                  ),
                  const SizedBox(height: AppSizes.space2),
                  Text(
                    provider.error!,
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: AppColors.textMuted,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: AppSizes.space4),
                  ElevatedButton(
                    onPressed: _refresh,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.white,
                    ),
                    child: const Text('Réessayer'),
                  ),
                ],
              ),
            );
          }

          if (provider.allProducts.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.branding_watermark_outlined,
                    size: 64,
                    color: AppColors.textMuted,
                  ),
                  const SizedBox(height: AppSizes.space4),
                  Text(
                    'Aucun produit disponible',
                    style: AppTextStyles.h3,
                  ),
                  const SizedBox(height: AppSizes.space2),
                  Text(
                    'Aucun produit trouvé pour la marque "${widget.brandName}"',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: AppColors.textMuted,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: _refresh,
            child: Column(
              children: [
                // En-tête avec nombre de produits
                Container(
                  padding: const EdgeInsets.all(AppSizes.paddingMedium),
                  color: AppColors.background,
                  child: Row(
                    children: [
                      Text(
                        '${provider.allProducts.length} produit${provider.allProducts.length > 1 ? 's' : ''}',
                        style: AppTextStyles.bodyMedium.copyWith(
                          fontWeight: FontWeight.bold,
                          color: AppColors.textDark,
                        ),
                      ),
                    ],
                  ),
                ),
                // Grille de produits
                Expanded(
                  child: GridView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.all(AppSizes.paddingMedium),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      childAspectRatio: 0.7,
                      crossAxisSpacing: AppSizes.space4,
                      mainAxisSpacing: AppSizes.space4,
                    ),
                    itemCount: provider.allProducts.length + (_isLoadingMore ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index == provider.allProducts.length) {
                        return const Center(child: CircularProgressIndicator());
                      }

                      final product = provider.allProducts[index];
                      return ModernProductCard(
                        product: product,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ProductDetailsScreen(product: product),
                            ),
                          );
                        },
                      );
                    },
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  /// Obtenir l'URL complète de l'image de la marque
  String _getBrandImageUrl(String imagePath) {
    // Si l'URL contient localhost, remplacer par 10.0.2.2 pour l'émulateur
    String url = imagePath;
    if (url.contains('localhost') || url.contains('127.0.0.1')) {
      url = url
          .replaceAll('http://localhost', 'http://10.0.2.2')
          .replaceAll('http://127.0.0.1', 'http://10.0.2.2')
          .replaceAll('https://localhost', 'http://10.0.2.2')
          .replaceAll('https://127.0.0.1', 'http://10.0.2.2');
    }
    
    // Si ce n'est pas une URL complète, construire l'URL
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      if (url.startsWith('storage/') || url.startsWith('images/')) {
        return '${ApiConfig.imageBaseUrl}/$url';
      }
      return '${ApiConfig.imageBaseUrl}/storage/$url';
    }
    
    return url;
  }
}

