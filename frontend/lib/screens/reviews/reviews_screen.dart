import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import '../../models/review_model.dart';
import '../../services/review_service.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';

class ReviewsScreen extends StatefulWidget {
  final int productId;

  const ReviewsScreen({super.key, required this.productId});

  @override
  State<ReviewsScreen> createState() => _ReviewsScreenState();
}

class _ReviewsScreenState extends State<ReviewsScreen> {
  final ReviewService _reviewService = ReviewService();
  List<ReviewModel> _reviews = [];
  bool _isLoading = true;
  double _averageRating = 0.0;
  Map<int, int> _ratingDistribution = {};

  @override
  void initState() {
    super.initState();
    _loadReviews();
  }

  Future<void> _loadReviews() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final result = await _reviewService.getProductReviews(widget.productId);

      if (mounted) {
        if (result['success'] == true) {
          // ✅ Le service retourne maintenant les données dans result['data']
          final data = result['data'] ?? {};
          final stats = data['stats'] ?? {};
          final reviewsData = data['reviews'] ?? [];

          print('📊 [REVIEWS_SCREEN] Nombre d\'avis: ${reviewsData.length}');
          print('📊 [REVIEWS_SCREEN] Type reviews: ${reviewsData.runtimeType}');

          setState(() {
            // ✅ reviewsData est déjà une List<ReviewModel> depuis le service
            if (reviewsData is List<ReviewModel>) {
              _reviews = reviewsData;
            } else if (reviewsData is List) {
              // Fallback si ce sont encore des Maps
              _reviews = reviewsData
                  .map(
                    (r) => r is ReviewModel
                        ? r
                        : ReviewModel.fromJson(r as Map<String, dynamic>),
                  )
                  .toList();
            } else {
              _reviews = [];
            }

            _averageRating = (stats['average_rating'] ?? 0.0).toDouble();

            // Distribution des notes
            final distribution =
                stats['distribution'] ?? stats['rating_distribution'] ?? {};
            _ratingDistribution = {
              5: (distribution['5'] ?? distribution[5] ?? 0) is int
                  ? distribution['5'] ?? distribution[5] ?? 0
                  : int.tryParse(distribution['5']?.toString() ?? '0') ?? 0,
              4: (distribution['4'] ?? distribution[4] ?? 0) is int
                  ? distribution['4'] ?? distribution[4] ?? 0
                  : int.tryParse(distribution['4']?.toString() ?? '0') ?? 0,
              3: (distribution['3'] ?? distribution[3] ?? 0) is int
                  ? distribution['3'] ?? distribution[3] ?? 0
                  : int.tryParse(distribution['3']?.toString() ?? '0') ?? 0,
              2: (distribution['2'] ?? distribution[2] ?? 0) is int
                  ? distribution['2'] ?? distribution[2] ?? 0
                  : int.tryParse(distribution['2']?.toString() ?? '0') ?? 0,
              1: (distribution['1'] ?? distribution[1] ?? 0) is int
                  ? distribution['1'] ?? distribution[1] ?? 0
                  : int.tryParse(distribution['1']?.toString() ?? '0') ?? 0,
            };

            _isLoading = false;
          });

          print('✅ [REVIEWS_SCREEN] Avis chargés: ${_reviews.length}');
          print('📊 [REVIEWS_SCREEN] Note moyenne: $_averageRating');
        } else {
          setState(() {
            _isLoading = false;
          });

          // Afficher un message d'erreur si nécessaire
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(
                  result['message'] ?? 'Erreur lors du chargement des avis',
                ),
                backgroundColor: AppColors.error,
              ),
            );
          }
        }
      }
    } catch (e) {
      print('💥 [REVIEWS_SCREEN] Exception: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Avis clients'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () {
              _showAddReviewDialog();
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadReviews,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppSizes.paddingLarge),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Overall rating
                    Container(
                      padding: const EdgeInsets.all(AppSizes.paddingLarge),
                      decoration: BoxDecoration(
                        color: AppColors.white,
                        borderRadius: BorderRadius.circular(
                          AppSizes.radiusMedium,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.05),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          Column(
                            children: [
                              Text(
                                _averageRating.toStringAsFixed(1),
                                style: AppTextStyles.h1.copyWith(
                                  color: AppColors.primary,
                                ),
                              ),
                              RatingBar.builder(
                                initialRating: _averageRating,
                                minRating: 0,
                                direction: Axis.horizontal,
                                allowHalfRating: true,
                                itemCount: 5,
                                itemSize: 20,
                                ignoreGestures: true,
                                itemBuilder: (context, _) => const Icon(
                                  Icons.star,
                                  color: AppColors.warning,
                                ),
                                onRatingUpdate: (_) {},
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${_reviews.length} avis',
                                style: AppTextStyles.bodySmall,
                              ),
                            ],
                          ),
                          const SizedBox(width: 32),
                          Expanded(
                            child: Column(
                              children: List.generate(5, (index) {
                                final star = 5 - index;
                                final count = _ratingDistribution[star] ?? 0;
                                final total = _reviews.length;
                                final percentage = total > 0
                                    ? (count / total * 100)
                                    : 0;

                                return Padding(
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 2,
                                  ),
                                  child: Row(
                                    children: [
                                      Text('$star'),
                                      const SizedBox(width: 4),
                                      const Icon(
                                        Icons.star,
                                        size: 14,
                                        color: AppColors.warning,
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: LinearProgressIndicator(
                                          value: percentage / 100,
                                          backgroundColor: AppColors.background,
                                          valueColor:
                                              const AlwaysStoppedAnimation<
                                                Color
                                              >(AppColors.warning),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        '${percentage.toInt()}%',
                                        style: AppTextStyles.caption,
                                      ),
                                    ],
                                  ),
                                );
                              }),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    // Reviews list
                    const Text('Tous les avis', style: AppTextStyles.h3),
                    const SizedBox(height: 16),
                    _reviews.isEmpty
                        ? Center(
                            child: Column(
                              children: [
                                Icon(
                                  Icons.rate_review_outlined,
                                  size: 80,
                                  color: AppColors.textLight,
                                ),
                                const SizedBox(height: 16),
                                const Text(
                                  'Aucun avis pour le moment',
                                  style: AppTextStyles.body,
                                ),
                                const SizedBox(height: 8),
                                const Text(
                                  'Soyez le premier à donner votre avis',
                                  style: AppTextStyles.bodySmall,
                                ),
                              ],
                            ),
                          )
                        : ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _reviews.length,
                            itemBuilder: (context, index) {
                              return _buildReviewCard(_reviews[index]);
                            },
                          ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildReviewCard(ReviewModel review) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // User info and rating
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: AppColors.primary,
                  child: Text(
                    review.userName[0].toUpperCase(),
                    style: const TextStyle(color: AppColors.white),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            review.userName,
                            style: AppTextStyles.body.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (review.isVerifiedPurchase) ...[
                            const SizedBox(width: 8),
                            const Icon(
                              Icons.verified,
                              size: 16,
                              color: AppColors.success,
                            ),
                          ],
                        ],
                      ),
                      Text(
                        Helpers.getTimeAgo(review.createdAt ?? DateTime.now()),
                        style: AppTextStyles.caption,
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            // Rating
            RatingBar.builder(
              initialRating: review.rating,
              minRating: 1,
              direction: Axis.horizontal,
              allowHalfRating: false,
              itemCount: 5,
              itemSize: 16,
              ignoreGestures: true,
              itemBuilder: (context, _) =>
                  const Icon(Icons.star, color: AppColors.warning),
              onRatingUpdate: (_) {},
            ),
            const SizedBox(height: 8),
            // Title
            if (review.title != null) ...[
              Text(
                review.title!,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 4),
            ],
            // Comment
            Text(review.comment, style: AppTextStyles.body),
            const SizedBox(height: 12),
            // Helpful votes
            Row(
              children: [
                const Text(
                  'Cet avis vous a-t-il été utile ?',
                  style: AppTextStyles.caption,
                ),
                const SizedBox(width: 16),
                TextButton.icon(
                  onPressed: () => _voteOnReview(review.id, true),
                  icon: const Icon(Icons.thumb_up_outlined, size: 16),
                  label: Text('Oui (${review.upvotes})'),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                  ),
                ),
                TextButton.icon(
                  onPressed: () => _voteOnReview(review.id, false),
                  icon: const Icon(Icons.thumb_down_outlined, size: 16),
                  label: Text('Non (${review.downvotes})'),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showAddReviewDialog() async {
    double rating = 5.0;
    final titleController = TextEditingController();
    final commentController = TextEditingController();

    final dialogResult = await showDialog<Map<String, dynamic>>(
      context: context,
      barrierColor: Colors.black.withOpacity(0.3),
      builder: (dialogContext) => StatefulBuilder(
        builder: (statefulContext, setDialogState) => Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.all(20),
          child: Container(
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 20,
                  offset: const Offset(0, 10),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header léger
                Padding(
                  padding: const EdgeInsets.fromLTRB(24, 24, 24, 16),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Ajouter un avis',
                        style: AppTextStyles.h3.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close, size: 20),
                        color: AppColors.textLight,
                        onPressed: () => Navigator.pop(dialogContext),
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                      ),
                    ],
                  ),
                ),

                // Contenu avec scroll
                Flexible(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Note avec design léger
                        Text(
                          'Votre note',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.textMedium,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Center(
                          child: RatingBar.builder(
                            initialRating: rating,
                            minRating: 1,
                            direction: Axis.horizontal,
                            allowHalfRating: false,
                            itemCount: 5,
                            itemSize: 36,
                            itemPadding: const EdgeInsets.symmetric(
                              horizontal: 4,
                            ),
                            itemBuilder: (context, _) => Icon(
                              Icons.star_rounded,
                              color: AppColors.warning,
                            ),
                            onRatingUpdate: (value) {
                              setDialogState(() {
                                rating = value;
                              });
                            },
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Titre optionnel
                        TextField(
                          controller: titleController,
                          style: AppTextStyles.body,
                          decoration: InputDecoration(
                            labelText: 'Titre (optionnel)',
                            labelStyle: AppTextStyles.bodySmall.copyWith(
                              color: AppColors.textLight,
                            ),
                            filled: true,
                            fillColor: AppColors.grey50,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 14,
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Commentaire
                        TextField(
                          controller: commentController,
                          maxLines: 5,
                          style: AppTextStyles.body,
                          decoration: InputDecoration(
                            labelText: 'Votre avis *',
                            labelStyle: AppTextStyles.bodySmall.copyWith(
                              color: AppColors.textLight,
                            ),
                            filled: true,
                            fillColor: AppColors.grey50,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            contentPadding: const EdgeInsets.all(16),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Minimum 10 caractères',
                          style: AppTextStyles.caption.copyWith(
                            color: AppColors.textLight,
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),

                // Actions avec design léger
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppColors.grey50,
                    borderRadius: const BorderRadius.only(
                      bottomLeft: Radius.circular(20),
                      bottomRight: Radius.circular(20),
                    ),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: TextButton(
                          onPressed: () => Navigator.pop(dialogContext),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Annuler',
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.textMedium,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        flex: 2,
                        child: ElevatedButton(
                          onPressed: () {
                            final comment = commentController.text.trim();
                            final title = titleController.text.trim();

                            // Validation
                            if (comment.isEmpty) {
                              ScaffoldMessenger.of(
                                statefulContext,
                              ).showSnackBar(
                                SnackBar(
                                  content: const Text(
                                    'Veuillez écrire un commentaire',
                                  ),
                                  backgroundColor: AppColors.error,
                                  behavior: SnackBarBehavior.floating,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                              );
                              return;
                            }

                            if (comment.length < 10) {
                              ScaffoldMessenger.of(
                                statefulContext,
                              ).showSnackBar(
                                SnackBar(
                                  content: const Text(
                                    'Le commentaire doit contenir au moins 10 caractères',
                                  ),
                                  backgroundColor: AppColors.error,
                                  behavior: SnackBarBehavior.floating,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                              );
                              return;
                            }

                            Navigator.pop(dialogContext, {
                              'rating': rating.toInt(),
                              'title': title.isEmpty ? null : title,
                              'comment': comment,
                            });
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            elevation: 0,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Publier',
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.white,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
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

    // ✅ Attendre que le dialog soit complètement fermé avant de disposer
    await Future.delayed(const Duration(milliseconds: 100));

    // Nettoyer les controllers après fermeture complète du dialog
    titleController.dispose();
    commentController.dispose();

    // Si le dialog a retourné des données, soumettre l'avis
    if (dialogResult != null && mounted) {
      print(
        '🔄 [REVIEWS] Soumission d\'un avis pour le produit ${widget.productId}',
      );

      final submitResult = await _reviewService.submitReview(
        productId: widget.productId,
        rating: dialogResult['rating'] as int,
        comment: dialogResult['comment'] as String,
        title: dialogResult['title'] as String?,
      );

      print('📥 [REVIEWS] Réponse soumission: ${submitResult['success']}');
      print('📨 [REVIEWS] Message: ${submitResult['message']}');

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              submitResult['message'] ??
                  (submitResult['success'] == true
                      ? 'Avis soumis avec succès'
                      : 'Erreur lors de la soumission'),
            ),
            backgroundColor: submitResult['success'] == true
                ? AppColors.success
                : AppColors.error,
          ),
        );

        // Recharger les avis si succès
        if (submitResult['success'] == true) {
          await _loadReviews();
        }
      }
    }
  }

  /// Voter sur un avis
  Future<void> _voteOnReview(int reviewId, bool isHelpful) async {
    final result = await _reviewService.voteOnReview(reviewId, isHelpful);

    if (mounted) {
      if (result['success'] == true) {
        // Mettre à jour localement le compteur
        setState(() {
          final reviewIndex = _reviews.indexWhere((r) => r.id == reviewId);
          if (reviewIndex != -1) {
            if (isHelpful) {
              _reviews[reviewIndex] = _reviews[reviewIndex].copyWith(
                upvotes: _reviews[reviewIndex].upvotes + 1,
              );
            } else {
              _reviews[reviewIndex] = _reviews[reviewIndex].copyWith(
                downvotes: _reviews[reviewIndex].downvotes + 1,
              );
            }
          }
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Vote enregistré'),
            backgroundColor: AppColors.success,
            duration: const Duration(seconds: 1),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Erreur lors du vote'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }
}
