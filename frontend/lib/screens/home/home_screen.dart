import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/banner_model.dart';
import '../../models/product_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/product_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../services/popup_manager_service.dart';
import '../../utils/constants.dart';
import '../../widgets/modern_banner_carousel.dart';
import '../../widgets/home/category_section.dart';
import '../../widgets/home/promo_buttons.dart';
import '../../widgets/home/product_section.dart';
import '../../widgets/home/promo_banner.dart';
import '../../widgets/home/top_sales_section.dart';
import '../../widgets/home/brands_section.dart';
import '../../widgets/home/policies_section.dart';
import '../../widgets/home/category_products_section.dart';
import '../../widgets/home/deals_of_day_section.dart';
import '../../widgets/modern_product_card.dart';
import '../../widgets/recent_products_section.dart';
import '../products/products_list_screen.dart';
import '../products/product_details_screen.dart';
import '../products/recent_products_screen.dart';
import '../cart/cart_screen.dart';
import '../promotions/black_friday_screen.dart';
import '../ai/ai_chatbot_screen.dart';
import '../stores/stores_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final PopupManagerService _popupManager = PopupManagerService();
  final TextEditingController _searchController = TextEditingController();
  final FocusNode _searchFocusNode = FocusNode();
  bool _isSearching = false;
  List<ProductModel> _searchResults = [];
  String? _selectedCategory;
  double _minPrice = 0;
  double _maxPrice = 1000000;
  String _sortBy = 'name'; // name, price_asc, price_desc

  @override
  void initState() {
    super.initState();
    // Charger les données au démarrage
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final productProvider = Provider.of<ProductProvider>(
        context,
        listen: false,
      );

      // Charger d'abord les données de la page d'accueil (inclut les catégories)
      await productProvider.loadHomeData();

      // Si les catégories ne sont pas chargées, les charger séparément
      if (productProvider.categories.isEmpty) {
        print(
          '⚠️ [HOME] Catégories vides après loadHomeData, chargement séparé...',
        );
        await productProvider.loadCategories();
      }

      await productProvider.loadPersonalizedSections();
      Provider.of<CartProvider>(context, listen: false).loadCart();
      Provider.of<FavoritesProvider>(context, listen: false).loadFavorites();

      // Afficher les popups promotionnels (type Jumia)
      _popupManager.checkAndShowPopups(context);
    });
  }

  bool _hasActivePromotions(Map<String, dynamic> promotions) {
    final blackFriday = promotions['black_friday'];
    final flashSales = promotions['flash_sales'];
    final bool blackEnabled =
        blackFriday is Map<String, dynamic> && blackFriday['enabled'] == true;
    final bool flashEnabled =
        flashSales is Map<String, dynamic> && flashSales['enabled'] == true;
    return blackEnabled || flashEnabled;
  }

  @override
  void dispose() {
    _searchController.dispose();
    _searchFocusNode.dispose();
    super.dispose();
  }

  /// Rechercher des produits avec filtres
  void _searchProducts(String query) {
    if (query.isEmpty) {
      setState(() {
        _searchResults = [];
      });
      return;
    }

    final productProvider = Provider.of<ProductProvider>(
      context,
      listen: false,
    );

    // Créer une Map pour éviter les doublons (clé = id du produit)
    final Map<int, ProductModel> uniqueProducts = {};

    // Ajouter tous les produits en utilisant leur ID comme clé
    for (var product in productProvider.featuredProducts) {
      uniqueProducts[product.id] = product;
    }
    for (var product in productProvider.trendingProducts) {
      uniqueProducts[product.id] = product;
    }
    for (var product in productProvider.newProducts) {
      uniqueProducts[product.id] = product;
    }
    for (var product in productProvider.bestOffers) {
      uniqueProducts[product.id] = product;
    }
    for (var product in productProvider.allProducts) {
      uniqueProducts[product.id] = product;
    }

    // Convertir la Map en liste (sans doublons)
    final allProducts = uniqueProducts.values.toList();

    var results = allProducts
        .where(
          (product) =>
              product.name.toLowerCase().contains(query.toLowerCase()) ||
              (product.description?.toLowerCase().contains(
                    query.toLowerCase(),
                  ) ??
                  false),
        )
        .toList();

    // Appliquer les filtres
    if (_selectedCategory != null) {
      results = results
          .where(
            (product) => product.categoryId.toString() == _selectedCategory,
          )
          .toList();
    }

    // Filtre par prix
    results = results
        .where(
          (product) => product.price >= _minPrice && product.price <= _maxPrice,
        )
        .toList();

    // Tri
    if (_sortBy == 'price_asc') {
      results.sort((a, b) => a.price.compareTo(b.price));
    } else if (_sortBy == 'price_desc') {
      results.sort((a, b) => b.price.compareTo(a.price));
    } else {
      results.sort((a, b) => a.name.compareTo(b.name));
    }

    setState(() {
      _searchResults = results;
    });
  }

  /// Activer/désactiver le mode recherche
  void _toggleSearch() {
    setState(() {
      _isSearching = !_isSearching;
      if (!_isSearching) {
        _searchController.clear();
        _searchResults = [];
      } else {
        _searchFocusNode.requestFocus();
      }
    });
  }

  Widget _buildFiltersBar() {
    final productProvider = Provider.of<ProductProvider>(context);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.white, AppColors.grey50],
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Titre avec nombre de résultats
          Row(
            children: [
              Text(
                '${_searchResults.length} résultat${_searchResults.length > 1 ? 's' : ''}',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                  color: AppColors.textDark,
                ),
              ),
              const Spacer(),
              if (_selectedCategory != null || _sortBy != 'name')
                TextButton.icon(
                  onPressed: () {
                    setState(() {
                      _selectedCategory = null;
                      _minPrice = 0;
                      _maxPrice = 1000000;
                      _sortBy = 'name';
                    });
                    _searchProducts(_searchController.text);
                  },
                  icon: const Icon(Icons.clear_all, size: 16),
                  label: const Text(
                    'Réinitialiser',
                    style: TextStyle(fontSize: 13),
                  ),
                  style: TextButton.styleFrom(
                    foregroundColor: AppColors.error,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          // Chips de filtres
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildModernFilterChip(
                  label: _selectedCategory == null
                      ? 'Catégorie'
                      : 'Catégorie ✓',
                  icon: Icons.category_outlined,
                  isActive: _selectedCategory != null,
                  onTap: () => _showCategoryFilter(productProvider),
                ),
                const SizedBox(width: 10),
                _buildModernFilterChip(
                  label: 'Prix',
                  icon: Icons.monetization_on_outlined,
                  isActive: false,
                  onTap: _showPriceFilter,
                ),
                const SizedBox(width: 10),
                _buildModernFilterChip(
                  label: _getSortLabel(),
                  icon: Icons.swap_vert,
                  isActive: _sortBy != 'name',
                  onTap: _showSortOptions,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildModernFilterChip({
    required String label,
    required IconData icon,
    required bool isActive,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(25),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          decoration: BoxDecoration(
            gradient: isActive
                ? LinearGradient(
                    colors: [AppColors.primary, AppColors.primaryLight],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  )
                : null,
            color: isActive ? null : Colors.white,
            borderRadius: BorderRadius.circular(25),
            border: Border.all(
              color: isActive ? AppColors.primary : AppColors.grey300,
              width: isActive ? 2 : 1,
            ),
            boxShadow: isActive
                ? [
                    BoxShadow(
                      color: AppColors.primary.withOpacity(0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ]
                : null,
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 18,
                color: isActive ? Colors.white : AppColors.textMedium,
              ),
              const SizedBox(width: 8),
              Text(
                label,
                style: TextStyle(
                  color: isActive ? Colors.white : AppColors.textDark,
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _getSortLabel() {
    switch (_sortBy) {
      case 'price_asc':
        return 'Prix ↑';
      case 'price_desc':
        return 'Prix ↓';
      default:
        return 'Tri';
    }
  }

  void _showCategoryFilter(ProductProvider productProvider) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(25)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, -3),
            ),
          ],
        ),
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.category_outlined,
                    color: AppColors.primary,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                const Text('Filtrer par catégorie', style: AppTextStyles.h3),
                const Spacer(),
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.close),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // Categories
            Wrap(
              spacing: 10,
              runSpacing: 10,
              children: productProvider.categories.map((category) {
                final isSelected = _selectedCategory == category.id.toString();
                return Material(
                  color: Colors.transparent,
                  child: InkWell(
                    onTap: () {
                      setState(() {
                        _selectedCategory = isSelected
                            ? null
                            : category.id.toString();
                      });
                      _searchProducts(_searchController.text);
                      Navigator.pop(context);
                    },
                    borderRadius: BorderRadius.circular(20),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        gradient: isSelected
                            ? LinearGradient(
                                colors: [
                                  AppColors.primary,
                                  AppColors.primaryLight,
                                ],
                              )
                            : null,
                        color: isSelected ? null : AppColors.grey100,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: isSelected
                              ? AppColors.primary
                              : Colors.transparent,
                          width: 2,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (isSelected)
                            const Icon(
                              Icons.check,
                              color: Colors.white,
                              size: 16,
                            ),
                          if (isSelected) const SizedBox(width: 6),
                          Text(
                            category.name,
                            style: TextStyle(
                              color: isSelected
                                  ? Colors.white
                                  : AppColors.textDark,
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  void _showPriceFilter() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          decoration: BoxDecoration(
            color: AppColors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(25)),
          ),
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.warning.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.monetization_on_outlined,
                      color: AppColors.warning,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Text('Filtrer par prix', style: AppTextStyles.h3),
                  const Spacer(),
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              // Affichage des valeurs
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Minimum',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textMedium,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${_minPrice.toInt()} FCFA',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppColors.primary,
                        ),
                      ),
                    ],
                  ),
                  const Icon(Icons.arrow_forward, color: AppColors.textLight),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'Maximum',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textMedium,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${_maxPrice.toInt()} FCFA',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppColors.primary,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 20),
              // Range Slider
              RangeSlider(
                values: RangeValues(_minPrice, _maxPrice),
                min: 0,
                max: 1000000,
                divisions: 100,
                activeColor: AppColors.primary,
                inactiveColor: AppColors.grey200,
                onChanged: (values) {
                  setModalState(() {
                    _minPrice = values.start;
                    _maxPrice = values.end;
                  });
                },
              ),
              const SizedBox(height: 24),
              // Bouton Appliquer
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    setState(() {});
                    _searchProducts(_searchController.text);
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 0,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.check_circle, size: 20),
                      const SizedBox(width: 8),
                      const Text(
                        'Appliquer le filtre',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showSortOptions() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(25)),
        ),
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.info.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.swap_vert,
                    color: AppColors.info,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                const Text('Trier par', style: AppTextStyles.h3),
                const Spacer(),
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.close),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // Options de tri
            _buildSortOption(
              icon: Icons.sort_by_alpha,
              title: 'Nom (A-Z)',
              isSelected: _sortBy == 'name',
              onTap: () {
                setState(() => _sortBy = 'name');
                _searchProducts(_searchController.text);
                Navigator.pop(context);
              },
            ),
            const SizedBox(height: 12),
            _buildSortOption(
              icon: Icons.arrow_upward,
              title: 'Prix croissant',
              subtitle: 'Du moins cher au plus cher',
              isSelected: _sortBy == 'price_asc',
              onTap: () {
                setState(() => _sortBy = 'price_asc');
                _searchProducts(_searchController.text);
                Navigator.pop(context);
              },
            ),
            const SizedBox(height: 12),
            _buildSortOption(
              icon: Icons.arrow_downward,
              title: 'Prix décroissant',
              subtitle: 'Du plus cher au moins cher',
              isSelected: _sortBy == 'price_desc',
              onTap: () {
                setState(() => _sortBy = 'price_desc');
                _searchProducts(_searchController.text);
                Navigator.pop(context);
              },
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildSortOption({
    required IconData icon,
    required String title,
    String? subtitle,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isSelected
                ? AppColors.primary.withOpacity(0.1)
                : AppColors.grey50,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? AppColors.primary : Colors.transparent,
              width: 2,
            ),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: isSelected ? AppColors.primary : AppColors.grey200,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  icon,
                  color: isSelected ? Colors.white : AppColors.textMedium,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 15,
                        color: isSelected
                            ? AppColors.primary
                            : AppColors.textDark,
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textMedium,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              if (isSelected)
                const Icon(
                  Icons.check_circle,
                  color: AppColors.primary,
                  size: 24,
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSearchResults() {
    return Column(
      children: [
        // Barre de filtres
        _buildFiltersBar(),

        // Résultats
        Expanded(
          child: _searchResults.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.search_off,
                        size: 80,
                        color: AppColors.textLight,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Aucun produit trouvé',
                        style: AppTextStyles.h3.copyWith(
                          color: AppColors.textMedium,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Essayez avec d\'autres mots-clés',
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: AppColors.textLight,
                        ),
                      ),
                    ],
                  ),
                )
              : GridView.builder(
                  padding: const EdgeInsets.all(16),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 0.75,
                  ),
                  itemCount: _searchResults.length,
                  itemBuilder: (context, index) {
                    final product = _searchResults[index];
                    return ModernProductCard(
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
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildLoadingState() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [AppColors.background, AppColors.surface],
        ),
      ),
      child: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppColors.primary),
              strokeWidth: 3,
            ),
            SizedBox(height: AppSizes.space4),
            Text('Chargement...', style: AppTextStyles.bodyMedium),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cartProvider = Provider.of<CartProvider>(context);
    final productProvider = Provider.of<ProductProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: _isSearching
            ? Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(8),
                ),
                padding: const EdgeInsets.symmetric(horizontal: 12),
                child: TextField(
                  controller: _searchController,
                  focusNode: _searchFocusNode,
                  style: const TextStyle(color: Colors.black, fontSize: 16),
                  cursorColor: AppColors.primary,
                  decoration: InputDecoration(
                    hintText: 'Rechercher des produits...',
                    hintStyle: TextStyle(
                      color: Colors.grey.shade600,
                      fontSize: 15,
                    ),
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                    icon: Icon(
                      Icons.search,
                      color: Colors.grey.shade600,
                      size: 20,
                    ),
                  ),
                  onChanged: _searchProducts,
                ),
              )
            : Image.asset(
                'assets/images/logoKaz.png',
                height: 35,
                fit: BoxFit.contain,
                alignment: Alignment.centerLeft,
              ),
        titleSpacing: 16, // Espacement à gauche
        automaticallyImplyLeading:
            false, // Retire l'espace pour le bouton retour
        backgroundColor: const Color(0xFF204fA1), // Bleu foncé élégant
        foregroundColor: Colors.white, // Icônes en blanc
        elevation: 0,
        actions: [
          IconButton(
            icon: Icon(
              _isSearching ? Icons.close : Icons.search,
              color: Colors.white,
            ),
            onPressed: _toggleSearch,
          ),
          Stack(
            children: [
              IconButton(
                icon: const Icon(
                  Icons.shopping_cart_outlined,
                  color: Colors.white,
                ),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CartScreen()),
                  );
                },
              ),
              if (cartProvider.itemCount > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: AppColors.error,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      '${cartProvider.itemCount}',
                      style: const TextStyle(
                        color: AppColors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          // Force le rechargement des données depuis le serveur
          await Future.wait([
            productProvider.loadHomeData(forceRefresh: true),
            productProvider.loadPersonalizedSections(forceRefresh: true),
            Provider.of<CartProvider>(context, listen: false).loadCart(),
            Provider.of<FavoritesProvider>(
              context,
              listen: false,
            ).loadFavorites(),
          ]);
        },
        child: _isSearching && _searchController.text.isNotEmpty
            ? _buildSearchResults()
            : productProvider.isLoading && !productProvider.hasData
            ? _buildLoadingState()
            : SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: AppSizes.space4),

                    // Bannières promotionnelles dynamiques
                    Builder(
                      builder: (context) {
                        final banners = _buildDynamicBanners(productProvider);
                        print(
                          '🎯 [HOME] Bannières passées au widget: ${banners.length}',
                        );
                        if (banners.isEmpty) {
                          return const SizedBox.shrink();
                        }
                        return ModernBannerCarousel(
                          height: 220,
                          banners: banners,
                          autoPlayInterval: const Duration(seconds: 8),
                        );
                      },
                    ),

                    const SizedBox(height: AppSizes.space4),

                    // Boutiques officielles en priorité
                    const BrandsSection(),

                    const SizedBox(height: AppSizes.space3),

                    // Boutons d'accès rapide aux promotions (affichés uniquement si activés côté backend)
                    if (_hasActivePromotions(productProvider.promotions)) ...[
                      PromoButtons(promotions: productProvider.promotions),
                      const SizedBox(height: AppSizes.space3),
                    ],

                    // Catégories
                    CategorySection(categories: productProvider.categories),

                    // Pour vous
                    ProductSection(
                      title: 'Pour vous',
                      products: productProvider.forYouProducts,
                      icon: Icons.thumb_up_alt,
                      category: null,
                      isLoading:
                          productProvider.personalizedLoading &&
                          productProvider.forYouProducts.isEmpty,
                    ),

                    // Recommandé
                    ProductSection(
                      title: 'Recommandé pour vous',
                      products: productProvider.recommendedProducts,
                      icon: Icons.star,
                      category: 'best_offers',
                      isLoading:
                          productProvider.personalizedLoading &&
                          productProvider.recommendedProducts.isEmpty,
                    ),

                    if (productProvider.recentProducts.isNotEmpty)
                      RecentProductsSection(
                        products: productProvider.recentProducts,
                        onViewAll: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => const RecentProductsScreen(),
                            ),
                          );
                        },
                      ),

                    // 🔥 NOUVEAU : Deals du Jour avec compte à rebours
                    const DealsOfDaySection(),

                    // Nouveautés
                    ProductSection(
                      title: 'Nouveautés',
                      products: productProvider.newProducts,
                      icon: Icons.new_releases,
                      category: 'new',
                      isLoading:
                          productProvider.isLoading &&
                          productProvider.newProducts.isEmpty,
                    ),

                    // 🏆 NOUVEAU : Section Top Ventes
                    const TopSalesSection(),

                    // Meilleures offres
                    ProductSection(
                      title: 'Meilleures offres',
                      products: productProvider.bestOffers,
                      icon: Icons.local_offer,
                      category: 'best_offers',
                      isLoading:
                          productProvider.isLoading &&
                          productProvider.bestOffers.isEmpty,
                    ),

                    // 🎨 Bannière publicitaire / Publicités de la page d'accueil
                    if (productProvider.homepageAds.isNotEmpty)
                      PromoBanner(homepageAds: productProvider.homepageAds)
                    else
                      PromoBanner(
                        imageUrl: 'assets/images/bg-2.jpg',
                        title: 'NOUVELLE COLLECTION',
                        subtitle: 'JUSTE POUR VOUS',
                        date: 'Octobre 2025',
                        buttonText: 'Découvrir',
                        overlayColor: const Color(0xFF1A3A52),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ProductsListScreen(
                                title: 'Nouvelle Collection',
                                category: 'new',
                                icon: Icons.new_releases,
                              ),
                            ),
                          );
                        },
                      ),

                    // Tendance
                    ProductSection(
                      title: 'Tendance',
                      products: productProvider.trendingProducts,
                      icon: Icons.trending_up,
                      category: 'trending',
                      isLoading:
                          productProvider.isLoading &&
                          productProvider.trendingProducts.isEmpty,
                    ),

                    const SizedBox(height: 24),

                    // Sections dynamiques par catégorie
                    const CategoryProductsSection(),

                    const SizedBox(height: 24),

                    // Politiques et Garanties
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16),
                      child: PoliciesSection(),
                    ),

                    const SizedBox(height: 32),
                  ],
                ),
              ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AIChatbotScreen()),
          );
        },
        icon: const Icon(Icons.smart_toy),
        label: const Text('Assistant IA'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 8,
        tooltip: 'Discuter avec l\'IA',
      ),
    );
  }

  /// Construire les bannières dynamiques depuis l'API
  List<ModernBannerItem> _buildDynamicBanners(ProductProvider productProvider) {
    final List<ModernBannerItem> items = [];

    // ✅ Utiliser les bannières depuis l'API si disponibles
    // Le carousel affiche uniquement: slides du carousel principal + homepage_banner_1 + homepage_banner_2
    print(
      '📊 [HOME] Vérification des bannières: ${productProvider.banners.length}',
    );

    if (productProvider.banners.isNotEmpty) {
      for (var banner in productProvider.banners) {
        print('🔍 [HOME] Vérification bannière: ${banner.title}, type: ${banner.type}, image: ${banner.image}');
        
        // ✅ Ne pas ajouter de bannière sans image
        if (banner.image.isEmpty || banner.image == 'null') {
          print('⚠️ [HOME] Bannière sans image ignorée: ${banner.title} (type: ${banner.type})');
          continue;
        }

        // ✅ Ne pas générer de buttonText automatiquement si aucun n'est configuré
        // Seulement utiliser buttonText si explicitement configuré depuis l'admin
        String? finalButtonText;
        if (banner.buttonText != null && banner.buttonText!.isNotEmpty) {
          finalButtonText = banner.buttonText;
        } else if (banner.actionType != 'none' && banner.actionType.isNotEmpty) {
          // Seulement générer un texte de bouton si une action est configurée
          finalButtonText = _getBannerButtonText(banner.actionType);
        }

        print(
          '✅ [HOME] Ajout bannière: title="${banner.title}", description="${banner.description}", buttonText="$finalButtonText", type: ${banner.type}',
        );

        items.add(
          ModernBannerItem(
            imageUrl: banner.image, // URL complète depuis l'API
            // ✅ N'afficher le titre que s'il est configuré depuis l'admin (non vide)
            title: banner.title.isNotEmpty ? banner.title : null,
            // ✅ N'afficher le sous-titre que s'il est configuré depuis l'admin (non vide)
            subtitle: (banner.description != null && banner.description!.isNotEmpty) ? banner.description : null,
            // ✅ N'afficher le bouton que s'il est configuré depuis l'admin (non vide)
            buttonText: finalButtonText,
            contentAlignment: CrossAxisAlignment.start,
            onTap: () => _handleBannerTap(banner),
          ),
        );
      }

      print('✅ [HOME] ${items.length} bannières chargées depuis l\'API');
      return items;
    } else {
      print('⚠️ [HOME] Aucune bannière disponible depuis l\'API');
    }

    // 🔄 Bannières de fallback si l'API ne retourne rien
    print('⚠️ [HOME] Utilisation des bannières de fallback');
    return [
      // Bannière 1 - Black Friday
      ModernBannerItem(
        gradient: const LinearGradient(
          colors: [Color(0xFF000000), Color(0xFF1A1A1A), Color(0xFF000000)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        badge: '🖤 BLACK FRIDAY',
        badgeGradient: const LinearGradient(
          colors: [AppColors.warning, Colors.orange],
        ),
        title: 'BLACK FRIDAY 💛',
        subtitle: 'Jusqu\'à -80% sur des milliers de produits',
        buttonText: 'Découvrir',
        contentAlignment: CrossAxisAlignment.start,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const BlackFridayScreen()),
          );
        },
      ),

      // Bannière 2 - Livraison gratuite
      ModernBannerItem(
        gradient: AppColors.accentGradient,
        badge: '🚚 LIVRAISON',
        badgeGradient: AppColors.primaryGradient,
        title: 'Livraison Gratuite',
        subtitle: 'Pour toute commande supérieure à 50 000 FCFA',
        buttonText: 'Commander',
        contentAlignment: CrossAxisAlignment.start,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => ProductsListScreen(
                title: 'Tous les produits',
                category: 'all',
                icon: Icons.shopping_bag,
              ),
            ),
          );
        },
      ),
    ];
  }

  /// Obtenir le texte du bouton selon le type d'action
  String _getBannerButtonText(String actionType) {
    switch (actionType) {
      case 'product':
        return 'Voir le produit';
      case 'category':
        return 'Découvrir';
      case 'url':
        return 'En savoir plus';
      case 'screen':
        return 'Accéder';
      default:
        return 'Découvrir';
    }
  }

  /// Gérer le tap sur une bannière
  void _handleBannerTap(BannerModel banner) {
    print('👆 [HOME] Tap sur bannière: ${banner.title}');
    print('   - Type d\'action: ${banner.actionType}');
    print('   - Données: ${banner.actionData}');

    // Navigation selon le type d'action
    switch (banner.actionType) {
      case 'product':
        // Navigation vers les détails d'un produit
        final productId =
            banner.actionData?['product_id'] ?? banner.actionData?['id'];
        if (productId != null) {
          _navigateToProduct(productId);
        }
        break;

      case 'category':
        // Navigation vers une catégorie
        final categoryId =
            banner.actionData?['category_id'] ?? banner.actionData?['id'];
        final categorySlug = banner.actionData?['slug'];
        if (categoryId != null || categorySlug != null) {
          _navigateToCategory(categoryId, categorySlug);
        }
        break;

      case 'store':
        // Navigation vers une boutique
        final storeId =
            banner.actionData?['store_id'] ?? banner.actionData?['id'];
        if (storeId != null) {
          _navigateToStore(storeId);
        }
        break;

      case 'url':
        // Ouvrir une URL externe
        final url = banner.actionData?['url'] ?? banner.actionData?['link'];
        if (url != null) {
          _openExternalUrl(url);
        }
        break;

      case 'screen':
        // Navigation vers un écran spécifique
        final screenName = banner.actionData?['screen'];
        if (screenName != null) {
          _navigateToScreen(screenName);
        }
        break;

      default:
        // Aucune action définie
        print('⚠️ [HOME] Type d\'action non géré: ${banner.actionType}');
        break;
    }
  }

  /// Naviguer vers un produit
  void _navigateToProduct(dynamic productId) {
    final productProvider = Provider.of<ProductProvider>(
      context,
      listen: false,
    );

    // Chercher le produit dans les données déjà chargées
    ProductModel? product;
    for (var p in productProvider.allProducts) {
      if (p.id.toString() == productId.toString()) {
        product = p;
        break;
      }
    }

    if (product != null) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => ProductDetailsScreen(product: product!),
        ),
      );
    } else {
      // Si le produit n'est pas trouvé, afficher un message
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Produit non disponible')));
    }
  }

  /// Naviguer vers une catégorie
  void _navigateToCategory(dynamic categoryId, String? categorySlug) {
    // Trouver la catégorie pour obtenir son nom
    final productProvider = Provider.of<ProductProvider>(
      context,
      listen: false,
    );
    String categoryName = 'Catégorie';

    try {
      final category = productProvider.categories.firstWhere(
        (cat) => cat.id.toString() == categoryId.toString(),
      );
      categoryName = category.name;
    } catch (e) {
      print('⚠️ [HOME] Catégorie non trouvée: $categoryId');
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ProductsListScreen(
          title: categoryName,
          category: categorySlug ?? categoryId?.toString() ?? '',
          icon: Icons.category,
        ),
      ),
    );
  }

  /// Naviguer vers une boutique
  void _navigateToStore(dynamic storeId) {
    // Import nécessaire ajouté en haut du fichier
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) =>
            const StoresScreen(), // Ou StoreDetailsScreen si on a l'ID
      ),
    );
  }

  /// Ouvrir une URL externe
  void _openExternalUrl(String url) {
    // Afficher un dialogue de confirmation avant d'ouvrir l'URL
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Ouvrir le lien'),
        content: Text('Voulez-vous ouvrir ce lien ?\n\n$url'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              // TODO: Utiliser url_launcher pour ouvrir l'URL
              ScaffoldMessenger.of(
                context,
              ).showSnackBar(SnackBar(content: Text('Ouverture de $url')));
            },
            child: const Text('Ouvrir'),
          ),
        ],
      ),
    );
  }

  /// Naviguer vers un écran spécifique
  void _navigateToScreen(String screenName) {
    switch (screenName.toLowerCase()) {
      case 'cart':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const CartScreen()),
        );
        break;
      case 'blackfriday':
      case 'black_friday':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const BlackFridayScreen()),
        );
        break;
      case 'ai':
      case 'chatbot':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const AIChatbotScreen()),
        );
        break;
      default:
        print('⚠️ [HOME] Écran non reconnu: $screenName');
        break;
    }
  }
}
