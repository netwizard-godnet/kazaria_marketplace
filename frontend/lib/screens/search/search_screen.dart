import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../utils/constants.dart';
import '../../models/product_model.dart';
import '../../models/category_model.dart';
import '../../providers/product_provider.dart';
import '../../widgets/product_card.dart';
import '../products/product_details_screen.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  int? _selectedCategoryId;
  String _sortBy = 'created_at';
  bool _isLoading = false;
  List<ProductModel> _products = [];
  List<CategoryModel> _categories = [];

  @override
  void initState() {
    super.initState();
    // Listener pour mettre à jour l'UI quand le texte change
    _searchController.addListener(() {
      setState(() {});
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadCategories();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadCategories() async {
    final productProvider = Provider.of<ProductProvider>(context, listen: false);
    await productProvider.loadCategories();
    if (mounted) {
      setState(() {
        _categories = productProvider.categories;
      });
    }
  }

  Future<void> _search() async {
    final query = _searchQuery.trim();
    
    if (query.isEmpty && _selectedCategoryId == null) {
      print('⚠️ [SEARCH] Recherche vide annulée');
      return;
    }

    print('🔍 [SEARCH] Démarrage recherche: "$query" (catégorie: $_selectedCategoryId, tri: $_sortBy)');

    setState(() {
      _isLoading = true;
    });

    try {
      final productProvider = Provider.of<ProductProvider>(context, listen: false);
      await productProvider.loadProducts(
        search: query.isEmpty ? null : query,
        categoryId: _selectedCategoryId,
        sortBy: _sortBy,
      );

      if (mounted) {
        setState(() {
          _products = productProvider.allProducts;
          _isLoading = false;
        });
        
        print('✅ [SEARCH] Recherche terminée: ${_products.length} produits trouvés');
        
        if (_products.isEmpty) {
          print('⚠️ [SEARCH] Aucun résultat pour "$query"');
        }
      }
    } catch (e) {
      print('💥 [SEARCH] Exception: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: ${e.toString()}')),
        );
      }
    }
  }

  void _clearSearch() {
    setState(() {
      _searchController.clear();
      _searchQuery = '';
      _selectedCategoryId = null;
      _products = [];
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: TextField(
          controller: _searchController,
          autofocus: true,
          decoration: InputDecoration(
            hintText: 'Rechercher un produit...',
            border: InputBorder.none,
            hintStyle: const TextStyle(color: AppColors.textLight),
            suffixIcon: _searchController.text.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear, size: 20),
                    onPressed: _clearSearch,
                  )
                : null,
          ),
          style: const TextStyle(color: AppColors.textDark),
          onChanged: (value) {
            setState(() {
              _searchQuery = value;
            });
          },
          onSubmitted: (value) {
            _search();
          },
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: _search,
          ),
          // Bouton de tri
          PopupMenuButton<String>(
            icon: const Icon(Icons.sort),
            onSelected: (value) {
              setState(() {
                _sortBy = value;
              });
              if (_products.isNotEmpty) {
                _search();
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'created_at',
                child: Text('Plus récents'),
              ),
              const PopupMenuItem(
                value: 'price_asc',
                child: Text('Prix croissant'),
              ),
              const PopupMenuItem(
                value: 'price_desc',
                child: Text('Prix décroissant'),
              ),
              const PopupMenuItem(
                value: 'rating',
                child: Text('Meilleures notes'),
              ),
              const PopupMenuItem(
                value: 'popular',
                child: Text('Plus populaires'),
              ),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Filtres de catégories
          if (_categories.isNotEmpty)
            Container(
              height: 50,
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: _categories.length + 1,
                itemBuilder: (context, index) {
                  if (index == 0) {
                    return _buildCategoryChip(
                      'Tous',
                      null,
                      _selectedCategoryId == null,
                    );
                  }
                  final category = _categories[index - 1];
                  return _buildCategoryChip(
                    category.name,
                    category.id,
                    _selectedCategoryId == category.id,
                  );
                },
              ),
            ),

          // Résultats
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              _searchQuery.isEmpty && _selectedCategoryId == null
                                  ? Icons.search
                                  : Icons.inbox_outlined,
                              size: 80,
                              color: AppColors.textLight.withOpacity(0.5),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              _searchQuery.isEmpty && _selectedCategoryId == null
                                  ? 'Recherchez un produit'
                                  : 'Aucun résultat trouvé',
                              style: AppTextStyles.h3.copyWith(
                                color: AppColors.textLight,
                              ),
                            ),
                            if (_searchQuery.isEmpty && _selectedCategoryId == null)
                              const SizedBox(height: 8),
                            if (_searchQuery.isEmpty && _selectedCategoryId == null)
                              Text(
                                'Essayez de rechercher par nom, marque...',
                                style: AppTextStyles.body.copyWith(
                                  color: AppColors.textLight,
                                ),
                                textAlign: TextAlign.center,
                              ),
                          ],
                        ),
                      )
                    : Column(
                        children: [
                          // Nombre de résultats
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 8,
                            ),
                            child: Row(
                              children: [
                                Text(
                                  '${_products.length} résultat${_products.length > 1 ? 's' : ''}',
                                  style: AppTextStyles.body.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                if (_searchQuery.isNotEmpty) ...[
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'pour "$_searchQuery"',
                                      style: AppTextStyles.body.copyWith(
                                        color: AppColors.textLight,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                          // Grille de produits
                          Expanded(
                            child: GridView.builder(
                              padding: const EdgeInsets.all(16),
                              gridDelegate:
                                  const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                childAspectRatio: 0.7,
                                crossAxisSpacing: 12,
                                mainAxisSpacing: 12,
                              ),
                              itemCount: _products.length,
                              itemBuilder: (context, index) {
                                final product = _products[index];
                                return ProductCard(
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
                                  // onFavorite sera géré automatiquement par ProductCard
                                );
                              },
                            ),
                          ),
                        ],
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryChip(String label, int? categoryId, bool isSelected) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) {
          setState(() {
            _selectedCategoryId = selected ? categoryId : null;
          });
          _search();
        },
        selectedColor: AppColors.primary,
        backgroundColor: AppColors.background,
        labelStyle: TextStyle(
          color: isSelected ? AppColors.white : AppColors.textDark,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          fontSize: 13,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
    );
  }
}
