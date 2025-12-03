import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/product_provider.dart';
import '../../utils/constants.dart';
import '../../widgets/modern_product_card.dart';
import 'product_details_screen.dart';

class BestOffersListScreen extends StatefulWidget {
  const BestOffersListScreen({super.key});

  @override
  State<BestOffersListScreen> createState() => _BestOffersListScreenState();
}

class _BestOffersListScreenState extends State<BestOffersListScreen> {
  final ScrollController _scrollController = ScrollController();
  int _currentPage = 1;
  bool _isLoadingMore = false;
  bool _hasMore = true;

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
    
    final provider = Provider.of<ProductProvider>(context, listen: false);
    await provider.loadProducts(
      categoryId: null,
      search: null,
      sortBy: 'discount_percentage',
      specialCategory: null,
      officialStoresBestOffers: true, // ✅ Filtrer les meilleures offres des boutiques officielles
      page: _currentPage,
      limit: 20,
    );
    
    // Vérifier s'il y a plus de produits
    if (provider.allProducts.length < 20) {
      _hasMore = false;
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
      search: null,
      sortBy: 'discount_percentage',
      specialCategory: null,
      officialStoresBestOffers: true,
      page: _currentPage,
      limit: 20,
    );
    
    // Vérifier s'il y a plus de produits
    if (provider.allProducts.length == previousCount) {
      _hasMore = false;
    }
    
    setState(() => _isLoadingMore = false);
  }

  Future<void> _refresh() async {
    await _loadProducts(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Meilleures offres'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        elevation: 0,
      ),
      body: Consumer<ProductProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.allProducts.isEmpty) {
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
                    Icons.local_offer_outlined,
                    size: 64,
                    color: AppColors.textMuted,
                  ),
                  const SizedBox(height: AppSizes.space4),
                  Text(
                    'Aucune offre disponible',
                    style: AppTextStyles.h3,
                  ),
                  const SizedBox(height: AppSizes.space2),
                  Text(
                    'Il n\'y a pas de meilleures offres pour le moment',
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
          );
        },
      ),
    );
  }
}

