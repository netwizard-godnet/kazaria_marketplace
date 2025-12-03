import 'package:flutter/material.dart';
import '../../services/wishlist_service.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';

class SharedWishlistScreen extends StatefulWidget {
  final String shareToken;

  const SharedWishlistScreen({super.key, required this.shareToken});

  @override
  State<SharedWishlistScreen> createState() => _SharedWishlistScreenState();
}

class _SharedWishlistScreenState extends State<SharedWishlistScreen> {
  final WishlistService _wishlistService = WishlistService();
  Map<String, dynamic>? _wishlist;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadSharedWishlist();
  }

  Future<void> _loadSharedWishlist() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    final response = await _wishlistService.viewSharedWishlist(widget.shareToken);

    if (!mounted) return;

    if (response['success'] == true) {
      setState(() {
        _wishlist = response['wishlist'] as Map<String, dynamic>?;
        _isLoading = false;
      });
    } else {
      setState(() {
        _error = response['message'] ?? 'Lien invalide ou expiré';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_wishlist != null ? _wishlist!['name'] ?? 'Liste partagée' : 'Liste partagée'),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return _buildError();
    }

    if (_wishlist == null) {
      return _buildError(message: 'Liste introuvable.');
    }

    final items = _wishlist!['items_with_products'] as List<dynamic>? ?? [];

    return RefreshIndicator(
      onRefresh: _loadSharedWishlist,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildHeader(),
          const SizedBox(height: 16),
          if (items.isEmpty)
            _buildEmptyItems()
          else
            ...items.map((item) => _buildSharedItem(item as Map<String, dynamic>)).toList(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    final description = _wishlist!['description'] as String?;
    final itemCount = _wishlist!['items_count'] ?? 0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: AppColors.primaryGradient,
        borderRadius: BorderRadius.circular(16),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            _wishlist!['name'] ?? 'Liste partagée',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '$itemCount produit${itemCount > 1 ? 's' : ''}',
            style: const TextStyle(color: Colors.white70),
          ),
          if (description != null && description.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(
              description,
              style: const TextStyle(color: Colors.white70),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSharedItem(Map<String, dynamic> item) {
    final product = item['product'] as Map<String, dynamic>? ?? {};

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1.5,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    color: AppColors.grey100,
                  ),
                  child: const Icon(Icons.shopping_bag_outlined, size: 28, color: AppColors.primary),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        product['name'] ?? 'Produit #${product['id'] ?? item['product_id']}',
                        style: AppTextStyles.h4,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        Helpers.formatPrice(product['price']),
                        style: AppTextStyles.h3.copyWith(color: AppColors.primary),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _buildTag(Icons.priority_high, _priorityLabel(item['priority'])),
                const SizedBox(width: 8),
                if (item['target_price'] != null)
                  _buildTag(Icons.notifications_active, 'Alerte à ${Helpers.formatPrice(item['target_price'])}'),
              ],
            ),
            if (item['note'] != null && (item['note'] as String).isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.grey50,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(item['note']),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyItems() {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: AppColors.grey50,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          Icon(
            Icons.list_alt_outlined,
            size: 80,
            color: AppColors.textLight.withOpacity(0.3),
          ),
          const SizedBox(height: 16),
          const Text('Cette liste est encore vide', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Text(
            'Ajoutez des produits depuis l\'application pour les partager ici.',
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildTag(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: AppColors.primary),
          const SizedBox(width: 6),
          Text(
            label,
            style: const TextStyle(fontSize: 12, color: AppColors.primary),
          ),
        ],
      ),
    );
  }

  String _priorityLabel(dynamic priority) {
    switch (priority) {
      case 2:
        return 'Priorité urgente';
      case 1:
        return 'Priorité haute';
      default:
        return 'Priorité normale';
    }
  }

  Widget _buildError({String? message}) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.link_off, size: 96, color: AppColors.error),
            const SizedBox(height: 16),
            const Text('Lien invalide ou expiré', style: AppTextStyles.h3),
            const SizedBox(height: 8),
            Text(
              message ?? _error ?? 'Cette liste n\'est plus disponible.',
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _loadSharedWishlist,
              icon: const Icon(Icons.refresh),
              label: const Text('Actualiser'),
            ),
          ],
        ),
      ),
    );
  }
}


