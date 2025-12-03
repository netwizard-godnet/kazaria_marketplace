import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import '../../models/product_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../providers/product_provider.dart';
import '../../services/product_service.dart'; // ✅ Import direct du service
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/product_card.dart';
import '../../widgets/share_button.dart';
import '../../config/api_config.dart';
import '../../services/recent_products_service.dart';
import '../reviews/reviews_screen.dart';
import 'image_gallery_screen.dart';
import 'products_list_screen.dart';

class ProductDetailsScreen extends StatefulWidget {
  final ProductModel product;
  final Map<String, String>? selectedAttributes; // ✅ Attributs déjà sélectionnés (depuis le panier)
  final String? heroTag;

  const ProductDetailsScreen({
    super.key,
    required this.product,
    this.selectedAttributes, // Optionnel
    this.heroTag,
  });

  @override
  State<ProductDetailsScreen> createState() => _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends State<ProductDetailsScreen>
    with SingleTickerProviderStateMixin {
  late PageController _pageController;
  int _currentImageIndex = 0;
  int _quantity = 1;
  bool _isFavorite = false;
  late TabController _tabController;
  List<ProductModel> _similarProducts = [];
  bool _loadingSimilar = false;
  Map<String, String> _selectedAttributes = {}; // ✅ Attributs sélectionnés

  /// Construire l'URL complète de l'image
  String _buildImageUrl(String imagePath) {
    if (imagePath.isEmpty) return '';
    
    // Si l'URL est déjà complète et correcte
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    
    // ✅ CORRECTION : Si c'est "http:" sans "//" (erreur commune)
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }
    
    // ✅ CORRECTION : Si c'est "https:" sans "//"
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }
    
    // Ajouter le base URL si c'est un chemin relatif
    if (imagePath.startsWith('storage/')) {
      return '${ApiConfig.imageBaseUrl}/$imagePath';
    }
    return '${ApiConfig.imageBaseUrl}/storage/$imagePath';
  }

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    _tabController = TabController(length: 3, vsync: this);
    
    // ✅ Pré-remplir les attributs sélectionnés si le produit vient du panier
    if (widget.selectedAttributes != null) {
      _selectedAttributes = Map<String, String>.from(widget.selectedAttributes!);
      print('✅ [PRODUCT_DETAILS] Attributs pré-sélectionnés: $_selectedAttributes');
    }
    
    // Ajouter immédiatement aux produits récents (pas de Provider)
    _addToRecentProducts();

    // ✅ Attendre que le widget soit construit avant de charger les données Provider
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadSimilarProducts();
        // Charger les favoris pour avoir le bon état
        try {
          Provider.of<FavoritesProvider>(
            context,
            listen: false,
          ).loadFavorites();
        } catch (e) {
          print('⚠️ [PRODUCT_DETAILS] Erreur chargement favoris: $e');
        }
      }
    });
  }

  void _addToRecentProducts() {
    RecentProductsService.addRecentProduct(widget.product).then((_) {
      if (!mounted) return;
      try {
        Provider.of<ProductProvider>(context, listen: false)
            .loadPersonalizedSections(forceRefresh: true);
      } catch (e) {
        print('⚠️ [PRODUCT_DETAILS] Impossible de rafraîchir les recommandations: $e');
      }
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadSimilarProducts() async {
    setState(() {
      _loadingSimilar = true;
    });

    try {
      // ✅ Utiliser l'endpoint dédié avec algorithme de similarité intelligent
      final productService = ProductService();

      final response = await productService.getSimilarProducts(
        widget.product.id,
      );

      if (mounted && response['success'] == true) {
        final productsData = response['products'] as List?;

        if (productsData != null && productsData.isNotEmpty) {
          setState(() {
            // ✅ Conversion directe (le backend a déjà filtré les produits)
            _similarProducts = productsData
                .map((p) => ProductModel.fromJson(p as Map<String, dynamic>))
                .take(6) // Limiter à 6 pour l'affichage
                .toList();
            _loadingSimilar = false;

            print(
              '✅ [SIMILAR] ${_similarProducts.length} produits similaires chargés',
            );
            print(
              '   📊 Algorithme backend: même catégorie, même marque, prix similaire, meilleurs notés',
            );
          });
        } else {
          // ⚠️ Aucun produit similaire trouvé
          if (mounted) {
            setState(() {
              _similarProducts = [];
              _loadingSimilar = false;
            });
            print('⚠️ [SIMILAR] Aucun produit similaire trouvé');
          }
        }
      } else {
        if (mounted) {
          setState(() {
            _loadingSimilar = false;
          });
          print('❌ [SIMILAR] Erreur API: ${response['message']}');
        }
      }
    } catch (e) {
      print('❌ [SIMILAR] Exception: $e');
      if (mounted) {
        setState(() {
          _loadingSimilar = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final images = widget.product.images ?? [widget.product.image ?? ''];
    final cartProvider = Provider.of<CartProvider>(context);

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          // App Bar avec image
          _buildSliverAppBar(images),

          // Breadcrumb
          SliverToBoxAdapter(
            child: Container(
              width: double.infinity,
              color: AppColors.background,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: const Text('Accueil', style: AppTextStyles.caption),
                  ),
                  const Text('  >  ', style: AppTextStyles.caption),
                  const Text('Catégories', style: AppTextStyles.caption),
                  const Text('  >  ', style: AppTextStyles.caption),
                  Expanded(
                    child: Text(
                      widget.product.category?.name ?? 'Produit',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textDark,
                        fontWeight: FontWeight.w600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const Text('  >  ', style: AppTextStyles.caption),
                  Expanded(
                    child: Text(
                      widget.product.name,
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textDark,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Contenu
          SliverToBoxAdapter(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Informations principales
                _buildMainInfo(),

                const Divider(height: 32),

                // Tabs (Description, Avis, Spécifications)
                _buildTabs(),

                const Divider(height: 32),

                // Produits similaires
                _buildSimilarProducts(),

                const SizedBox(height: 100),
              ],
            ),
          ),
        ],
      ),

      // Bottom bar
      bottomSheet: _buildBottomBar(cartProvider),
    );
  }

  Widget _buildSliverAppBar(List<String> images) {
    return SliverAppBar(
      expandedHeight: 350,
      pinned: true,
      actions: [
        // Bouton favoris
        IconButton(
          icon: Icon(
            _isFavorite ? Icons.favorite : Icons.favorite_border,
            color: _isFavorite ? AppColors.error : null,
          ),
          onPressed: () {
            setState(() {
              _isFavorite = !_isFavorite;
            });
            // TODO: API favoris
          },
        ),
        // Bouton partage
        ShareButton(
          type: 'product',
          id: widget.product.id,
          name: widget.product.name,
          storeName: widget.product.store?.name,
          slug: widget.product.slug,
          description: widget.product.description,
          isCompact: true,
        ),
      ],
      flexibleSpace: FlexibleSpaceBar(
        background: Stack(
          children: [
            // Carousel d'images
            PageView.builder(
              controller: _pageController,
              itemCount: images.length,
              onPageChanged: (index) {
                setState(() {
                  _currentImageIndex = index;
                });
              },
              itemBuilder: (context, index) {
                return Hero(
                  tag: widget.heroTag ?? 'product_${widget.product.id}',
                  child: GestureDetector(
                    onTap: () {
                      // Ouvrir la galerie plein écran avec zoom
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ImageGalleryScreen(
                            images: images
                                .map((img) => '${ApiConfig.imageBaseUrl}/$img')
                                .toList(),
                            initialIndex: index,
                          ),
                        ),
                      );
                    },
                    child: Container(
                      color: AppColors.white,
                      padding: const EdgeInsets.all(16),
                      child: CachedNetworkImage(
                        imageUrl: _buildImageUrl(images[index]),
                        fit: BoxFit.contain, // Mieux pour ne pas déformer
                        placeholder: (context, url) => Container(
                          color: AppColors.background,
                          child: const Center(
                            child: CircularProgressIndicator(),
                          ),
                        ),
                        errorWidget: (context, url, error) {
                          print('❌ [PRODUCT_DETAILS] Erreur image: $url');
                          print('❌ [PRODUCT_DETAILS] Erreur: $error');
                          return Container(
                            color: AppColors.grey100,
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.shopping_bag_outlined,
                                  size: 60,
                                  color: AppColors.primary.withOpacity(0.5),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'K',
                                  style: TextStyle(
                                    fontSize: 32,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.primary,
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                  ),
                );
              },
            ),

            // Badge VEDETTE en haut à gauche
            if (widget.product.isFeatured)
              Positioned(
                top: 120,
                left: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [AppColors.warning, Colors.orange.shade700],
                    ),
                    borderRadius: const BorderRadius.only(
                      topRight: Radius.circular(12),
                      bottomRight: Radius.circular(12),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.warning.withOpacity(0.4),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: const [
                      Icon(Icons.star, color: Colors.white, size: 14),
                      SizedBox(width: 4),
                      Text(
                        'VEDETTE',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

            // Badge NOUVEAU en haut à gauche (sous VEDETTE)
            if (widget.product.isNew)
              Positioned(
                top: widget.product.isFeatured ? 170 : 120,
                left: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.success,
                    borderRadius: const BorderRadius.only(
                      topRight: Radius.circular(12),
                      bottomRight: Radius.circular(12),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.success.withOpacity(0.4),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: const [
                      Icon(Icons.new_releases, color: Colors.white, size: 14),
                      SizedBox(width: 4),
                      Text(
                        'NOUVEAU',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

            // Badge RÉDUCTION en haut à droite
            if (widget.product.hasDiscount)
              Positioned(
                top: 120,
                right: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.error,
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(12),
                      bottomLeft: Radius.circular(12),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.error.withOpacity(0.4),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Text(
                    '-${widget.product.discountPercentage?.toInt()}%',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
              ),

            // Indicateurs de page
            if (images.length > 1)
              Positioned(
                bottom: 16,
                left: 0,
                right: 0,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: images.asMap().entries.map((entry) {
                    return Container(
                      width: 8,
                      height: 8,
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: _currentImageIndex == entry.key
                            ? AppColors.white
                            : AppColors.white.withOpacity(0.5),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.3),
                            blurRadius: 4,
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildMainInfo() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Nom du produit
          Text(widget.product.name, style: AppTextStyles.h2),
          const SizedBox(height: 8),

          // Note et avis
          GestureDetector(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ReviewsScreen(productId: widget.product.id),
                ),
              );
            },
            child: Row(
              children: [
                RatingBarIndicator(
                  rating: widget.product.rating,
                  itemBuilder: (context, index) =>
                      const Icon(Icons.star, color: AppColors.warning),
                  itemCount: 5,
                  itemSize: 20,
                  direction: Axis.horizontal,
                ),
                const SizedBox(width: 8),
                Text(
                  '${widget.product.rating} (${widget.product.reviewsCount} avis)',
                  style: AppTextStyles.body.copyWith(
                    color: AppColors.textLight,
                  ),
                ),
                const SizedBox(width: 4),
                const Icon(
                  Icons.chevron_right,
                  size: 16,
                  color: AppColors.textLight,
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Prix
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                Helpers.formatPrice(widget.product.price),
                style: AppTextStyles.h1.copyWith(
                  color: AppColors.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
              if (widget.product.hasDiscount) ...[
                const SizedBox(width: 12),
                Text(
                  Helpers.formatPrice(widget.product.oldPrice!),
                  style: AppTextStyles.body.copyWith(
                    decoration: TextDecoration.lineThrough,
                    color: AppColors.textLight,
                    fontSize: 18,
                  ),
                ),
              ],
            ],
          ),

          // Card économies réalisées
          if (widget.product.hasDiscount)
            Container(
              margin: const EdgeInsets.only(top: 12),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.success.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppColors.success.withOpacity(0.3),
                  width: 1,
                ),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: AppColors.success,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.savings,
                      color: Colors.white,
                      size: 16,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Vous économisez ${Helpers.formatPrice(widget.product.oldPrice! - widget.product.price)}',
                      style: const TextStyle(
                        color: AppColors.success,
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),

          const SizedBox(height: 16),

          // ✅ Alerte de stock visuelle
          _buildStockAlert(),

          // Marque et autres infos
          if (widget.product.brand != null) ...[
            const SizedBox(height: 16),
            _buildInfoRow('Marque', widget.product.brand!),
          ],
          if (widget.product.model != null)
            _buildInfoRow('Modèle', widget.product.model!),
          if (widget.product.warranty != null)
            _buildInfoRow('Garantie', widget.product.warranty!),

          // ✅ Options disponibles (Attributs)
          if (widget.product.attributes != null &&
              widget.product.attributes!.isNotEmpty) ...[
            const SizedBox(height: 24),
            const Divider(),
            const SizedBox(height: 16),
            _buildProductAttributes(),
          ],

          // ✅ Informations de livraison
          const SizedBox(height: 24),
          const Divider(),
          const SizedBox(height: 16),
          _buildDeliveryInfo(),
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Text(
            '$label: ',
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
          ),
          Text(
            value,
            style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }

  Widget _buildTabs() {
    return Column(
      children: [
        TabBar(
          controller: _tabController,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textLight,
          indicatorColor: AppColors.primary,
          tabs: const [
            Tab(text: 'Description'),
            Tab(text: 'Avis'),
            Tab(text: 'Détails'),
          ],
        ),
        SizedBox(
          height: 200,
          child: TabBarView(
            controller: _tabController,
            children: [
              // Description
              SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Text(
                  widget.product.description ??
                      'Aucune description disponible.',
                  style: AppTextStyles.body,
                ),
              ),

              // Avis
              Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.rate_review_outlined,
                      size: 60,
                      color: AppColors.textLight,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      '${widget.product.reviewsCount} avis',
                      style: AppTextStyles.h3,
                    ),
                    const SizedBox(height: 8),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) =>
                                ReviewsScreen(productId: widget.product.id),
                          ),
                        );
                      },
                      child: const Text('Voir tous les avis'),
                    ),
                  ],
                ),
              ),

              // Détails techniques
              SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (widget.product.brand != null)
                      _buildDetailRow('Marque', widget.product.brand!),
                    if (widget.product.model != null)
                      _buildDetailRow('Modèle', widget.product.model!),
                    _buildDetailRow('Stock', '${widget.product.stock}'),
                    if (widget.product.warranty != null)
                      _buildDetailRow('Garantie', widget.product.warranty!),
                    _buildDetailRow(
                      'Catégorie',
                      widget.product.category?.name ?? 'N/A',
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
          ),
          Text(
            value,
            style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }

  /// ✅ Afficher les attributs du produit (RAM, Stockage, Couleur, etc.)
  Widget _buildProductAttributes() {
    final attributes = widget.product.attributes;
    if (attributes == null || attributes.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Options disponibles :',
          style: AppTextStyles.h3.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),

        // Parcourir chaque attribut
        ...attributes.entries.map((entry) {
          final attributeName = entry.key;
          final attributeValues = entry.value;

          // Convertir en liste si nécessaire
          List<String> valuesList = [];
          if (attributeValues is List) {
            valuesList = attributeValues.map((v) => v.toString()).toList();
          } else if (attributeValues is String) {
            valuesList = [attributeValues];
          }

          if (valuesList.isEmpty) return const SizedBox.shrink();

          return Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$attributeName:',
                  style: AppTextStyles.body.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: valuesList.map((value) {
                    final isSelected =
                        _selectedAttributes[attributeName] == value;

                    return InkWell(
                      onTap: () {
                        setState(() {
                          _selectedAttributes[attributeName] = value;
                        });
                      },
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 10,
                        ),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? AppColors.primary
                              : AppColors.background,
                          border: Border.all(
                            color: isSelected
                                ? AppColors.primary
                                : AppColors.border,
                            width: isSelected ? 2 : 1,
                          ),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          value,
                          style: AppTextStyles.body.copyWith(
                            color: isSelected
                                ? AppColors.white
                                : AppColors.textDark,
                            fontWeight: isSelected
                                ? FontWeight.bold
                                : FontWeight.normal,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ],
            ),
          );
        }).toList(),
      ],
    );
  }

  /// ✅ Afficher les informations de livraison et retours
  Widget _buildDeliveryInfo() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.background,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Titre
          Row(
            children: [
              const Icon(
                Icons.local_shipping_outlined,
                color: AppColors.primary,
                size: 24,
              ),
              const SizedBox(width: 8),
              Text(
                'LIVRAISON & RETOURS',
                style: AppTextStyles.h4.copyWith(fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Livraison
          _buildDeliveryOption(
            icon: Icons.delivery_dining,
            title: 'Livraison standard',
            description: 'Livraison gratuite dès 50 000 FCFA',
            color: AppColors.success,
          ),
          const SizedBox(height: 12),

          _buildDeliveryOption(
            icon: Icons.flash_on,
            title: 'Livraison express',
            description: 'Commandez avant 15h, livré le jour même',
            color: AppColors.warning,
          ),
          const SizedBox(height: 12),

          _buildDeliveryOption(
            icon: Icons.schedule,
            title: 'Livraison le lendemain',
            description: 'Commandes après 15h livrées le lendemain',
            color: AppColors.info,
          ),

          const Divider(height: 24),

          // Politique de retour
          Row(
            children: [
              const Icon(
                Icons.keyboard_return,
                color: AppColors.success,
                size: 20,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Retour garanti sous 7 jours',
                  style: AppTextStyles.body.copyWith(
                    fontWeight: FontWeight.w600,
                    color: AppColors.success,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            'Consultez notre politique de retour pour plus d\'informations sur les options de retour.',
            style: AppTextStyles.bodySmall.copyWith(color: AppColors.textLight),
          ),
        ],
      ),
    );
  }

  /// Helper pour afficher une option de livraison
  Widget _buildDeliveryOption({
    required IconData icon,
    required String title,
    required String description,
    required Color color,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color, size: 20),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 4),
              Text(
                description,
                style: AppTextStyles.bodySmall.copyWith(
                  color: AppColors.textLight,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildSimilarProducts() {
    if (_similarProducts.isEmpty && !_loadingSimilar) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Produits similaires', style: AppTextStyles.h3),
              TextButton(
                onPressed: () {
                  // Naviguer vers tous les produits de la même catégorie
                  if (widget.product.category != null) {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ProductsListScreen(
                          title: widget.product.category!.name,
                          category: widget.product.categoryId.toString(),
                          icon: Icons.category,
                        ),
                      ),
                    );
                  }
                },
                child: const Text('Voir tout'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        if (_loadingSimilar)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(32),
              child: CircularProgressIndicator(),
            ),
          )
        else
          SizedBox(
            height: 280,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _similarProducts.length,
              itemBuilder: (context, index) {
                final product = _similarProducts[index];
                final heroTag = 'similar_${product.id}_$index';
                return Container(
                  width: 160,
                  margin: const EdgeInsets.only(right: 12),
                  child: ProductCard(
                    product: product,
                    heroTag: heroTag,
                    onTap: () {
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ProductDetailsScreen(
                            product: product,
                            heroTag: heroTag,
                          ),
                        ),
                      );
                    },
                    // onFavorite sera géré automatiquement par ProductCard
                  ),
                );
              },
            ),
          ),
      ],
    );
  }

  /// 🎯 Alerte de stock visuelle (banner)
  Widget _buildStockAlert() {
    final stock = widget.product.stock;
    
    String message;
    Color bgColor;
    Color textColor;
    IconData icon;

    if (stock == 0) {
      // 🔴 Rupture de stock
      message = '⚠️ Rupture de stock - Article indisponible';
      bgColor = AppColors.error.withOpacity(0.1);
      textColor = AppColors.error;
      icon = Icons.block;
    } else if (stock == 1) {
      // 🟠 Dernière pièce
      message = '🔥 Dernière pièce disponible ! Commandez vite';
      bgColor = Colors.orange.withOpacity(0.1);
      textColor = Colors.orange.shade800;
      icon = Icons.warning_amber_rounded;
    } else if (stock <= 5) {
      // 🟡 Stock limité
      message = '⚡ Plus que $stock unités disponibles !';
      bgColor = Colors.deepOrange.withOpacity(0.1);
      textColor = Colors.deepOrange.shade800;
      icon = Icons.inventory_2_outlined;
    } else {
      // 🟢 En stock
      message = '✅ En stock ($stock disponibles)';
      bgColor = AppColors.success.withOpacity(0.1);
      textColor = AppColors.success;
      icon = Icons.check_circle;
    }

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: textColor.withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Icon(
            icon,
            color: textColor,
            size: 22,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              message,
              style: AppTextStyles.body.copyWith(
                color: textColor,
                fontWeight: FontWeight.w600,
                fontSize: 14,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomBar(CartProvider cartProvider) {
    final isInCart = cartProvider.isInCart(widget.product.id);
    final quantityInCart = cartProvider.getProductQuantity(widget.product.id);
    final stock = widget.product.stock;
    
    // ✅ Vérifier si on vient du panier (attributs pré-sélectionnés)
    final isFromCart = widget.selectedAttributes != null && widget.selectedAttributes!.isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            // Sélecteur de quantité
            if (!isInCart && stock > 0)
              Container(
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.border),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.remove, size: 20),
                      onPressed: _quantity > 1
                          ? () {
                              setState(() {
                                _quantity--;
                              });
                            }
                          : null,
                      padding: const EdgeInsets.all(8),
                    ),
                    Text(
                      '$_quantity',
                      style: AppTextStyles.body.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.add, size: 20),
                      onPressed: _quantity < stock
                          ? () {
                              setState(() {
                                _quantity++;
                              });
                            }
                          : null,
                      padding: const EdgeInsets.all(8),
                    ),
                  ],
                ),
              ),

            if (!isInCart && stock > 0) const SizedBox(width: 12),

            // Bouton ajouter au panier
            Expanded(
              child: CustomButton(
                text: stock == 0
                    ? 'Rupture de stock'
                    : (isFromCart
                        ? 'Déjà dans le panier ($quantityInCart)'
                        : (isInCart
                            ? 'Dans le panier ($quantityInCart)'
                            : 'Ajouter au panier')),
                icon: stock == 0
                    ? Icons.block
                    : ((isInCart || isFromCart) ? Icons.check_circle : Icons.shopping_cart),
                color: stock == 0
                    ? Colors.grey
                    : ((isInCart || isFromCart) ? AppColors.success : null),
                onPressed: stock > 0
                    ? () async {
                        if (isInCart || isFromCart) {
                          // Retourner au panier
                          Navigator.pop(context);
                        } else {
                          // Sauvegarder la quantité avant de la réinitialiser
                          final quantityToAdd = _quantity;

                          // ✅ FEEDBACK INSTANTANÉ avant l'appel API
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Row(
                                children: const [
                                  Icon(Icons.check_circle, color: Colors.white, size: 20),
                                  SizedBox(width: 12),
                                  Text('Ajouté au panier !'),
                                ],
                              ),
                              duration: const Duration(milliseconds: 1500),
                              backgroundColor: AppColors.success,
                              behavior: SnackBarBehavior.floating,
                            ),
                          );

                          // Réinitialiser la quantité immédiatement pour l'UI
                          setState(() {
                            _quantity = 1;
                          });

                          // ✅ Ajouter au panier (mise à jour optimiste - retourne immédiatement)
                          final response = await cartProvider.addToCart(
                            product: widget.product,
                            quantity: quantityToAdd,
                            attributes: _selectedAttributes,
                          );

                          // Si erreur, afficher un message
                          if (!response['success'] && mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Row(
                                  children: [
                                    const Icon(Icons.error, color: Colors.white, size: 20),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Text(response['message'] ?? 'Erreur'),
                                    ),
                                  ],
                                ),
                                backgroundColor: AppColors.error,
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          }
                        }
                      }
                    : null, // ✅ Bouton désactivé si stock = 0
              ),
            ),
          ],
        ),
      ),
    );
  }
}
