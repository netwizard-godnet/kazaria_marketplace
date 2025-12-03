import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';
import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';

class WishlistShareManagementScreen extends StatefulWidget {
  final int wishlistId;
  final String wishlistName;

  const WishlistShareManagementScreen({
    super.key,
    required this.wishlistId,
    required this.wishlistName,
  });

  @override
  State<WishlistShareManagementScreen> createState() => _WishlistShareManagementScreenState();
}

class _WishlistShareManagementScreenState extends State<WishlistShareManagementScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<WishlistProvider>().loadWishlistShares(widget.wishlistId);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Partages - ${widget.wishlistName}'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_link),
            tooltip: 'Nouveau partage',
            onPressed: _createShare,
          ),
        ],
      ),
      body: Consumer<WishlistProvider>(
        builder: (context, provider, _) {
          final isLoading = provider.isSharesLoading(widget.wishlistId);
          final shares = provider.sharesForWishlist(widget.wishlistId);
          final error = provider.sharesError(widget.wishlistId);

          if (isLoading && shares.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (error != null && shares.isEmpty) {
            return _buildErrorState(error);
          }

          if (shares.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadWishlistShares(widget.wishlistId),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: shares.length,
              itemBuilder: (context, index) {
                final share = shares[index];
                return _buildShareTile(share);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildShareTile(Map<String, dynamic> share) {
    final isActive = share['is_active'] == true;
    final permission = share['permission'] as String? ?? 'view';
    final expiresAt = share['expires_at'] as String?;
    final email = share['shared_with_email'] as String?;
    final shareUrl = share['share_url'] as String?;
    final views = share['views_count'] ?? 0;

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  isActive ? Icons.link : Icons.link_off,
                  color: isActive ? AppColors.primary : AppColors.textLight,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isActive ? 'Lien actif' : 'Lien expiré',
                        style: AppTextStyles.h4,
                      ),
                      if (email != null)
                        Text(
                          'Partager avec : $email',
                          style: AppTextStyles.caption.copyWith(color: AppColors.textLight),
                        ),
                    ],
                  ),
                ),
                PopupMenuButton(
                  onSelected: (value) {
                    switch (value) {
                      case 'copy':
                        if (shareUrl != null) _copyLink(shareUrl);
                        break;
                      case 'share':
                        if (shareUrl != null) Share.share(
                          'Découvre ma liste "${widget.wishlistName}" : $shareUrl',
                        );
                        break;
                      case 'revoke':
                        _revokeShare(share['id'] as int);
                        break;
                    }
                  },
                  itemBuilder: (context) => [
                    if (shareUrl != null)
                      const PopupMenuItem(
                        value: 'copy',
                        child: ListTile(
                          leading: Icon(Icons.copy),
                          title: Text('Copier le lien'),
                        ),
                      ),
                    if (shareUrl != null)
                      const PopupMenuItem(
                        value: 'share',
                        child: ListTile(
                          leading: Icon(Icons.share),
                          title: Text('Partager...'),
                        ),
                      ),
                    const PopupMenuItem(
                      value: 'revoke',
                      child: ListTile(
                        leading: Icon(Icons.delete_outline, color: AppColors.error),
                        title: Text('Révoquer', style: TextStyle(color: AppColors.error)),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _buildChip(
                  permission == 'edit' ? Icons.edit : Icons.visibility,
                  permission == 'edit' ? 'Peut modifier' : 'Lecture seule',
                ),
                const SizedBox(width: 8),
                _buildChip(Icons.remove_red_eye, '$views vues'),
                if (expiresAt != null)
                  Padding(
                    padding: const EdgeInsets.only(left: 8),
                    child: _buildChip(Icons.timer, 'Expire le ${_formatDate(expiresAt)}'),
                  ),
              ],
            ),
            if (shareUrl != null) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.grey50,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        shareUrl,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 13),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.copy),
                      onPressed: () => _copyLink(shareUrl),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildChip(IconData icon, String label) {
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

  Widget _buildErrorState(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text('Erreur de chargement', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Text(
            message,
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => context.read<WishlistProvider>().loadWishlistShares(widget.wishlistId),
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.link_off,
              size: 100,
              color: AppColors.textLight.withOpacity(0.2),
            ),
            const SizedBox(height: 24),
            const Text('Aucun partage actif', style: AppTextStyles.h3),
            const SizedBox(height: 8),
            Text(
              'Générez un lien ou partagez par email pour permettre à vos proches de consulter cette liste.',
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _createShare,
              icon: const Icon(Icons.add_link),
              label: const Text('Créer un partage'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _createShare() async {
    final permission = await showModalBottomSheet<String>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 12),
            Container(
              height: 4,
              width: 48,
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: AppColors.grey200,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const ListTile(
              title: Text('Choisir le type de partage'),
              subtitle: Text('Définissez les permissions accordées au destinataire'),
            ),
            ListTile(
              leading: const Icon(Icons.visibility),
              title: const Text('Lecture seule'),
              subtitle: const Text('Le destinataire peut seulement consulter la liste'),
              onTap: () => Navigator.pop(context, 'view'),
            ),
            ListTile(
              leading: const Icon(Icons.edit),
              title: const Text('Peut modifier'),
              subtitle: const Text('Le destinataire peut ajouter ou retirer des produits'),
              onTap: () => Navigator.pop(context, 'edit'),
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );

    if (permission == null) return;

    final provider = context.read<WishlistProvider>();
    final response = await provider.shareWishlist(
      wishlistId: widget.wishlistId,
      permission: permission,
      expiresInDays: 30,
    );

    if (!mounted) return;

    if (response['success'] == true) {
      final shareUrl = response['share_url'] as String?;
      if (shareUrl != null) {
        await Clipboard.setData(ClipboardData(text: shareUrl));
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Lien copié dans le presse-papiers'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'Impossible de créer le partage'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Future<void> _revokeShare(int shareId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Révoquer le partage'),
        content: const Text('Le lien ne sera plus accessible. Confirmer ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.error),
            child: const Text('Révoquer'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final provider = context.read<WishlistProvider>();
    final response = await provider.revokeWishlistShare(shareId, widget.wishlistId);

    if (!mounted) return;

    if (response['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Partage révoqué'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'Erreur lors de la révocation'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Future<void> _copyLink(String link) async {
    await Clipboard.setData(ClipboardData(text: link));
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Lien copié'),
        backgroundColor: AppColors.success,
      ),
    );
  }

  String _formatDate(String isoDate) {
    try {
      final parsed = DateTime.parse(isoDate).toLocal();
      return '${parsed.day.toString().padLeft(2, '0')}/${parsed.month.toString().padLeft(2, '0')}/${parsed.year}';
    } catch (_) {
      return isoDate;
    }
  }
}


