import 'package:flutter/material.dart';
import '../utils/constants.dart';

class ProductFiltersBottomSheet extends StatefulWidget {
  final Map<String, dynamic> currentFilters;
  final List<String> availableBrands;
  final double minPrice;
  final double maxPrice;
  final Function(Map<String, dynamic>) onApplyFilters;

  const ProductFiltersBottomSheet({
    super.key,
    required this.currentFilters,
    required this.availableBrands,
    required this.minPrice,
    required this.maxPrice,
    required this.onApplyFilters,
  });

  @override
  State<ProductFiltersBottomSheet> createState() => _ProductFiltersBottomSheetState();
}

class _ProductFiltersBottomSheetState extends State<ProductFiltersBottomSheet> {
  late Map<String, dynamic> _filters;
  late RangeValues _priceRange;

  @override
  void initState() {
    super.initState();
    _filters = Map<String, dynamic>.from(widget.currentFilters);
    
    // Initialiser le range de prix
    final minPrice = _filters['min_price'] ?? widget.minPrice;
    final maxPrice = _filters['max_price'] ?? widget.maxPrice;
    _priceRange = RangeValues(
      minPrice.toDouble(),
      maxPrice.toDouble(),
    );
  }

  void _resetFilters() {
    setState(() {
      _filters = {
        'sort_by': 'created_at',
        'min_price': widget.minPrice,
        'max_price': widget.maxPrice,
        'min_rating': null,
        'in_stock': null,
        'brands': [],
      };
      _priceRange = RangeValues(widget.minPrice, widget.maxPrice);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: const BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.white,
              border: Border(
                bottom: BorderSide(color: AppColors.border.withOpacity(0.3)),
              ),
            ),
            child: Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
                const Expanded(
                  child: Text(
                    'Filtres et Tri',
                    style: AppTextStyles.h3,
                    textAlign: TextAlign.center,
                  ),
                ),
                TextButton(
                  onPressed: _resetFilters,
                  child: const Text(
                    'Réinitialiser',
                    style: TextStyle(color: AppColors.error),
                  ),
                ),
              ],
            ),
          ),

          // Contenu scrollable
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // 🔄 Tri
                  _buildSortSection(),
                  const SizedBox(height: 24),

                  // 💰 Prix
                  _buildPriceSection(),
                  const SizedBox(height: 24),

                  // ⭐ Note
                  _buildRatingSection(),
                  const SizedBox(height: 24),

                  // 📦 Disponibilité
                  _buildAvailabilitySection(),
                  const SizedBox(height: 24),

                  // 🏷️ Marques
                  if (widget.availableBrands.isNotEmpty) ...[
                    _buildBrandsSection(),
                    const SizedBox(height: 24),
                  ],
                ],
              ),
            ),
          ),

          // Bouton Appliquer
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 10,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: SafeArea(
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    _filters['min_price'] = _priceRange.start;
                    _filters['max_price'] = _priceRange.end;
                    widget.onApplyFilters(_filters);
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Text(
                    'Appliquer les filtres',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.white,
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSortSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.sort, color: AppColors.primary, size: 22),
            SizedBox(width: 8),
            Text('Trier par', style: AppTextStyles.h4),
          ],
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            _buildSortChip('created_at', 'Nouveautés', Icons.new_releases),
            _buildSortChip('price_asc', 'Prix croissant', Icons.arrow_upward),
            _buildSortChip('price_desc', 'Prix décroissant', Icons.arrow_downward),
            _buildSortChip('discount', 'Promotions', Icons.local_offer),
            _buildSortChip('popular', 'Popularité', Icons.trending_up),
            _buildSortChip('rating', 'Meilleure note', Icons.star),
          ],
        ),
      ],
    );
  }

  Widget _buildSortChip(String value, String label, IconData icon) {
    final isSelected = _filters['sort_by'] == value;
    return FilterChip(
      selected: isSelected,
      label: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: isSelected ? AppColors.white : AppColors.primary),
          const SizedBox(width: 6),
          Text(label),
        ],
      ),
      onSelected: (selected) {
        setState(() {
          _filters['sort_by'] = value;
        });
      },
      selectedColor: AppColors.primary,
      backgroundColor: AppColors.white,
      labelStyle: TextStyle(
        color: isSelected ? AppColors.white : AppColors.textDark,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
      ),
      side: BorderSide(
        color: isSelected ? AppColors.primary : AppColors.border,
      ),
    );
  }

  Widget _buildPriceSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.attach_money, color: AppColors.primary, size: 22),
            SizedBox(width: 8),
            Text('Fourchette de prix', style: AppTextStyles.h4),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: AppColors.grey100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${_priceRange.start.toInt()} FCFA',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 8),
              child: Text('-'),
            ),
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: AppColors.grey100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${_priceRange.end.toInt()} FCFA',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        RangeSlider(
          values: _priceRange,
          min: widget.minPrice,
          max: widget.maxPrice,
          divisions: 20,
          activeColor: AppColors.primary,
          inactiveColor: AppColors.grey200,
          labels: RangeLabels(
            '${_priceRange.start.toInt()}',
            '${_priceRange.end.toInt()}',
          ),
          onChanged: (RangeValues values) {
            setState(() {
              _priceRange = values;
            });
          },
        ),
      ],
    );
  }

  Widget _buildRatingSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.star, color: AppColors.warning, size: 22),
            SizedBox(width: 8),
            Text('Note minimale', style: AppTextStyles.h4),
          ],
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            _buildRatingChip(null, 'Toutes'),
            _buildRatingChip(4.0, '4★ et +'),
            _buildRatingChip(3.0, '3★ et +'),
            _buildRatingChip(2.0, '2★ et +'),
          ],
        ),
      ],
    );
  }

  Widget _buildRatingChip(double? rating, String label) {
    final isSelected = _filters['min_rating'] == rating;
    return ChoiceChip(
      selected: isSelected,
      label: Text(label),
      onSelected: (selected) {
        setState(() {
          _filters['min_rating'] = rating;
        });
      },
      selectedColor: AppColors.warning.withOpacity(0.2),
      backgroundColor: AppColors.white,
      labelStyle: TextStyle(
        color: isSelected ? AppColors.warning.withOpacity(0.9) : AppColors.textDark,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
      ),
      side: BorderSide(
        color: isSelected ? AppColors.warning : AppColors.border,
      ),
    );
  }

  Widget _buildAvailabilitySection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.inventory_2, color: AppColors.success, size: 22),
            SizedBox(width: 8),
            Text('Disponibilité', style: AppTextStyles.h4),
          ],
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            _buildAvailabilityChip(null, 'Tous', Icons.all_inclusive),
            _buildAvailabilityChip(true, 'En stock', Icons.check_circle),
            _buildAvailabilityChip(false, 'Rupture de stock', Icons.cancel),
          ],
        ),
      ],
    );
  }

  Widget _buildAvailabilityChip(bool? inStock, String label, IconData icon) {
    final isSelected = _filters['in_stock'] == inStock;
    return FilterChip(
      selected: isSelected,
      label: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 16,
            color: isSelected 
                ? AppColors.white 
                : (inStock == true ? AppColors.success : (inStock == false ? AppColors.error : AppColors.textLight)),
          ),
          const SizedBox(width: 6),
          Text(label),
        ],
      ),
      onSelected: (selected) {
        setState(() {
          _filters['in_stock'] = inStock;
        });
      },
      selectedColor: AppColors.primary,
      backgroundColor: AppColors.white,
      labelStyle: TextStyle(
        color: isSelected ? AppColors.white : AppColors.textDark,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
      ),
      side: BorderSide(
        color: isSelected ? AppColors.primary : AppColors.border,
      ),
    );
  }

  Widget _buildBrandsSection() {
    final selectedBrands = List<String>.from(_filters['brands'] ?? []);
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.branding_watermark, color: AppColors.primary, size: 22),
            SizedBox(width: 8),
            Text('Marques', style: AppTextStyles.h4),
          ],
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: widget.availableBrands.map((brand) {
            final isSelected = selectedBrands.contains(brand);
            return FilterChip(
              selected: isSelected,
              label: Text(brand),
              onSelected: (selected) {
                setState(() {
                  if (selected) {
                    selectedBrands.add(brand);
                  } else {
                    selectedBrands.remove(brand);
                  }
                  _filters['brands'] = selectedBrands;
                });
              },
              selectedColor: AppColors.primary,
              backgroundColor: AppColors.white,
              labelStyle: TextStyle(
                color: isSelected ? AppColors.white : AppColors.textDark,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
              ),
              side: BorderSide(
                color: isSelected ? AppColors.primary : AppColors.border,
              ),
            );
          }).toList(),
        ),
      ],
    );
  }
}

