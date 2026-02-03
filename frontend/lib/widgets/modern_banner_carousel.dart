import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../utils/constants.dart';

class ModernBannerCarousel extends StatefulWidget {
  final double height;
  final List<ModernBannerItem> banners;
  final Duration autoPlayInterval;
  final bool showIndicators;
  final bool enableSwipe;

  const ModernBannerCarousel({
    super.key,
    this.height = 200,
    required this.banners,
    this.autoPlayInterval = const Duration(seconds: 5),
    this.showIndicators = true,
    this.enableSwipe = true,
  });

  @override
  State<ModernBannerCarousel> createState() => _ModernBannerCarouselState();
}

class _ModernBannerCarouselState extends State<ModernBannerCarousel>
    with TickerProviderStateMixin {
  final PageController _pageController = PageController();
  
  int _currentPage = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _startAutoPlay();
  }

  @override
  void dispose() {
    _stopAutoPlay();
    _pageController.dispose();
    super.dispose();
  }

  void _startAutoPlay() {
    if (widget.banners.length <= 1) return;
    
    _stopAutoPlay();
    _timer = Timer.periodic(widget.autoPlayInterval, (timer) {
      if (_pageController.hasClients) {
        int nextPage = (_currentPage + 1) % widget.banners.length;
        _pageController.animateToPage(
          nextPage,
          duration: AppAnimations.normal,
          curve: AppAnimations.easeInOut,
        );
      }
    });
  }

  void _stopAutoPlay() {
    _timer?.cancel();
    _timer = null;
  }

  @override
  Widget build(BuildContext context) {
    print('🎨 [MODERN_BANNER_CAROUSEL] Build appelé avec ${widget.banners.length} bannières');
    
    if (widget.banners.isEmpty) {
      print('⚠️ [MODERN_BANNER_CAROUSEL] Aucune bannière, retourne SizedBox.shrink()');
      return const SizedBox.shrink();
    }

    print('✅ [MODERN_BANNER_CAROUSEL] Affichage du carousel avec ${widget.banners.length} bannières');
    return Container(
      height: widget.height,
      margin: const EdgeInsets.symmetric(horizontal: 0),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        boxShadow: AppShadows.shadowXL,
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        child: Stack(
          children: [
            // Carousel principal
            PageView.builder(
              controller: _pageController,
              physics: const PageScrollPhysics(),
              onPageChanged: (index) {
                setState(() {
                  _currentPage = index;
                });
              },
              itemCount: widget.banners.length,
              itemBuilder: (context, index) {
                return _buildBannerItem(widget.banners[index]);
              },
            ),

            // Indicateurs modernes
            if (widget.showIndicators && widget.banners.length > 1)
              Positioned(
                bottom: AppSizes.space4,
                left: 0,
                right: 0,
                child: _buildIndicators(),
              ),

            // Overlay de navigation (optionnel)
            if (widget.enableSwipe && widget.banners.length > 1)
              _buildNavigationOverlay(),
          ],
        ),
      ),
    );
  }

  Widget _buildBannerItem(ModernBannerItem banner) {
    print('🎨 [MODERN_BANNER_CAROUSEL] Construction bannière: title=${banner.title}, imageUrl=${banner.imageUrl}');
    
    return Container(
      width: double.infinity,
      height: double.infinity,
      decoration: BoxDecoration(
        gradient: banner.gradient,
        color: banner.gradient == null ? Colors.grey[300] : null,
      ),
      child: Stack(
        fit: StackFit.expand,
        children: [
          // Image de fond (cliquable si pas de bouton)
          Positioned.fill(
            child: GestureDetector(
              onTap: banner.buttonText == null ? banner.onTap : null,
              child: (banner.imageUrl != null && banner.imageUrl!.isNotEmpty)
                  ? Container(
                      color: Colors.grey[100],
                      child: CachedNetworkImage(
                        imageUrl: _fixImageUrl(banner.imageUrl!),
                        fit: BoxFit.contain,
                        width: double.infinity,
                        height: double.infinity,
                        alignment: Alignment.center,
                        placeholder: (context, url) {
                          print('⏳ [MODERN_BANNER_CAROUSEL] Chargement image: $url');
                          return Container(
                            width: double.infinity,
                            height: double.infinity,
                            decoration: const BoxDecoration(
                              gradient: AppColors.primaryGradient,
                            ),
                            child: const Center(
                              child: CircularProgressIndicator(color: Colors.white),
                            ),
                          );
                        },
                        errorWidget: (context, url, error) {
                          print('❌ [MODERN_BANNER_CAROUSEL] Erreur chargement image: $url, erreur: $error');
                          return Container(
                            width: double.infinity,
                            height: double.infinity,
                            decoration: const BoxDecoration(
                              gradient: AppColors.primaryGradient,
                            ),
                            child: Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.error, color: Colors.white),
                                  const SizedBox(height: 8),
                                  Text('Erreur: $error', style: const TextStyle(color: Colors.white)),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    )
                  : Container(
                      width: double.infinity,
                      height: double.infinity,
                      decoration: const BoxDecoration(
                        gradient: AppColors.primaryGradient,
                      ),
                      child: const Center(
                        child: Text('Pas d\'image', style: TextStyle(color: Colors.white)),
                      ),
                    ),
            ),
          ),

          // Overlay gradient
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  Colors.black.withOpacity(0.1),
                  Colors.black.withOpacity(0.3),
                ],
              ),
            ),
          ),

          // Contenu
          Positioned(
            left: AppSizes.space6,
            right: AppSizes.space6,
            bottom: AppSizes.space8,
            child: _buildBannerContent(banner),
          ),

          // Éléments décoratifs
          _buildDecorativeElements(),
        ],
      ),
    );
  }

  Widget _buildBannerContent(ModernBannerItem banner) {
    // ✅ Ne rien afficher si aucun contenu n'est configuré
    final hasContent = banner.badge != null || 
                      banner.title != null || 
                      banner.subtitle != null || 
                      banner.buttonText != null;
    
    if (!hasContent) {
      return const SizedBox.shrink();
    }
    
    return Column(
      crossAxisAlignment: banner.contentAlignment,
      mainAxisSize: MainAxisSize.min,
      children: [
        // Badge
        if (banner.badge != null)
          Container(
            margin: const EdgeInsets.only(bottom: AppSizes.space3),
            padding: const EdgeInsets.symmetric(
              horizontal: AppSizes.space3,
              vertical: AppSizes.space1,
            ),
            decoration: BoxDecoration(
              gradient: banner.badgeGradient ?? AppColors.accentGradient,
              borderRadius: BorderRadius.circular(AppSizes.radius2XL),
              boxShadow: AppShadows.shadowMD,
            ),
            child: Text(
              banner.badge!,
              style: AppTextStyles.labelMedium.copyWith(
                color: AppColors.white,
                fontWeight: FontWeight.w600,
                letterSpacing: 0.5,
              ),
            ),
          ),

        // Titre
        if (banner.title != null)
          Text(
            banner.title!,
            style: AppTextStyles.headlineMedium.copyWith(
              color: AppColors.white,
              fontWeight: FontWeight.w700,
              height: 1.2,
              shadows: [
                Shadow(
                  offset: const Offset(0, 2),
                  blurRadius: 4,
                  color: Colors.black.withOpacity(0.3),
                ),
              ],
            ),
            textAlign: _getTextAlign(banner.contentAlignment),
          ),

        // Sous-titre
        if (banner.subtitle != null) ...[
          const SizedBox(height: AppSizes.space2),
          Text(
            banner.subtitle!,
            style: AppTextStyles.bodyMedium.copyWith(
              color: AppColors.white.withOpacity(0.9),
              height: 1.4,
              shadows: [
                Shadow(
                  offset: const Offset(0, 1),
                  blurRadius: 2,
                  color: Colors.black.withOpacity(0.3),
                ),
              ],
            ),
            textAlign: _getTextAlign(banner.contentAlignment),
          ),
        ],

        // Bouton
        if (banner.buttonText != null) ...[
          const SizedBox(height: AppSizes.space4),
          GestureDetector(
            onTap: () {
              // Empêcher la propagation au PageView
              if (banner.onTap != null) {
                banner.onTap!();
              }
            },
            child: Container(
              decoration: BoxDecoration(
                gradient: AppColors.secondaryGradient,
                borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                boxShadow: AppShadows.shadowLG,
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () {
                    // Empêcher la propagation au PageView
                    if (banner.onTap != null) {
                      banner.onTap!();
                    }
                  },
                  borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSizes.space6,
                      vertical: AppSizes.space3,
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          banner.buttonText!,
                          style: AppTextStyles.labelLarge.copyWith(
                            color: AppColors.white,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(width: AppSizes.space2),
                        const Icon(
                          Icons.arrow_forward_rounded,
                          color: AppColors.white,
                          size: AppSizes.iconSM,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildIndicators() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: widget.banners.asMap().entries.map((entry) {
        bool isActive = entry.key == _currentPage;
        return GestureDetector(
          onTap: () => _pageController.animateToPage(
            entry.key,
            duration: AppAnimations.normal,
            curve: AppAnimations.easeInOut,
          ),
          child: AnimatedContainer(
            duration: AppAnimations.fast,
            margin: const EdgeInsets.symmetric(horizontal: AppSizes.space1),
            width: isActive ? 24 : 8,
            height: 8,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(AppSizes.radiusXL),
              gradient: isActive 
                  ? AppColors.primaryGradient 
                  : LinearGradient(
                      colors: [
                        AppColors.white.withOpacity(0.5),
                        AppColors.white.withOpacity(0.3),
                      ],
                    ),
              boxShadow: isActive ? AppShadows.shadowSM : null,
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildNavigationOverlay() {
    return IgnorePointer(
      ignoring: false,
      child: Row(
        children: [
          // Navigation gauche
          Expanded(
            flex: 1,
            child: GestureDetector(
              behavior: HitTestBehavior.translucent,
              onTap: () {
                int prevPage = _currentPage > 0 
                    ? _currentPage - 1 
                    : widget.banners.length - 1;
                _pageController.animateToPage(
                  prevPage,
                  duration: AppAnimations.normal,
                  curve: AppAnimations.easeInOut,
                );
              },
              child: Container(
                height: widget.height,
                alignment: Alignment.centerLeft,
                padding: const EdgeInsets.only(left: AppSizes.space2),
                child: Container(
                  padding: const EdgeInsets.all(AppSizes.space1),
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.chevron_left_rounded,
                    color: AppColors.white,
                    size: AppSizes.iconMD,
                  ),
                ),
              ),
            ),
          ),
          
          // Zone centrale (n'intercepte pas les clics)
          const Expanded(
            flex: 2,
            child: SizedBox(),
          ),
          
          // Navigation droite
          Expanded(
            flex: 1,
            child: GestureDetector(
              behavior: HitTestBehavior.translucent,
              onTap: () {
                int nextPage = (_currentPage + 1) % widget.banners.length;
                _pageController.animateToPage(
                  nextPage,
                  duration: AppAnimations.normal,
                  curve: AppAnimations.easeInOut,
                );
              },
              child: Container(
                height: widget.height,
                alignment: Alignment.centerRight,
                padding: const EdgeInsets.only(right: AppSizes.space2),
                child: Container(
                  padding: const EdgeInsets.all(AppSizes.space1),
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.chevron_right_rounded,
                    color: AppColors.white,
                    size: AppSizes.iconMD,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDecorativeElements() {
    return Stack(
      children: [
        // Cercle décoratif 1
        Positioned(
          top: -50,
          right: -50,
          child: Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  AppColors.primary.withOpacity(0.1),
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),
        
        // Cercle décoratif 2
        Positioned(
          bottom: -30,
          left: -30,
          child: Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  AppColors.secondary.withOpacity(0.1),
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  TextAlign _getTextAlign(CrossAxisAlignment alignment) {
    switch (alignment) {
      case CrossAxisAlignment.start:
        return TextAlign.left;
      case CrossAxisAlignment.center:
        return TextAlign.center;
      case CrossAxisAlignment.end:
        return TextAlign.right;
      default:
        return TextAlign.left;
    }
  }
  
  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    // Si l'URL est déjà complète et correcte
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    
    // ✅ CORRECTION : Si c'est "http:" sans "//"
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }
    
    // ✅ CORRECTION : Si c'est "https:" sans "//"
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }
    
    // Si c'est un chemin relatif, retourner tel quel
    return imagePath;
  }
}

class ModernBannerItem {
  final String? imageUrl;
  final LinearGradient? gradient;
  final String? badge;
  final LinearGradient? badgeGradient;
  final String? title;
  final String? subtitle;
  final String? buttonText;
  final CrossAxisAlignment contentAlignment;
  final VoidCallback? onTap;

  ModernBannerItem({
    this.imageUrl,
    this.gradient,
    this.badge,
    this.badgeGradient,
    this.title,
    this.subtitle,
    this.buttonText,
    this.contentAlignment = CrossAxisAlignment.start,
    this.onTap,
  });
}
