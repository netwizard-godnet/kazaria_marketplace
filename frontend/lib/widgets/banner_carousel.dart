import 'package:flutter/material.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../utils/constants.dart';

class BannerCarousel extends StatefulWidget {
  final List<BannerItem> banners;
  final double height;
  final Duration autoPlayDuration;

  const BannerCarousel({
    super.key,
    required this.banners,
    this.height = 200,
    this.autoPlayDuration = const Duration(seconds: 5),
  });

  @override
  State<BannerCarousel> createState() => _BannerCarouselState();
}

class _BannerCarouselState extends State<BannerCarousel> {
  late PageController _pageController;
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    
    // Auto-play
    if (widget.banners.length > 1) {
      Future.delayed(widget.autoPlayDuration, _autoPlay);
    }
  }

  void _autoPlay() {
    if (!mounted) return;
    
    if (_currentPage < widget.banners.length - 1) {
      _currentPage++;
    } else {
      _currentPage = 0;
    }
    
    _pageController.animateToPage(
      _currentPage,
      duration: const Duration(milliseconds: 400),
      curve: Curves.easeInOut,
    );
    
    Future.delayed(widget.autoPlayDuration, _autoPlay);
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.banners.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      height: widget.height,
      margin: const EdgeInsets.symmetric(
        horizontal: AppSizes.paddingMedium,
        vertical: AppSizes.paddingSmall,
      ),
      child: Stack(
        children: [
          // Banner Pages
          PageView.builder(
            controller: _pageController,
            itemCount: widget.banners.length,
            onPageChanged: (index) {
              setState(() {
                _currentPage = index;
              });
            },
            itemBuilder: (context, index) {
              return _buildBannerItem(widget.banners[index]);
            },
          ),
          
          // Page Indicator
          if (widget.banners.length > 1)
            Positioned(
              bottom: 16,
              left: 0,
              right: 0,
              child: Center(
                child: SmoothPageIndicator(
                  controller: _pageController,
                  count: widget.banners.length,
                  effect: ExpandingDotsEffect(
                    activeDotColor: AppColors.white,
                    dotColor: AppColors.white.withOpacity(0.5),
                    dotHeight: 8,
                    dotWidth: 8,
                    expansionFactor: 3,
                    spacing: 6,
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildBannerItem(BannerItem banner) {
    return GestureDetector(
      onTap: banner.onTap,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
        child: Stack(
          fit: StackFit.expand,
          children: [
            // Background Image or Gradient
            if (banner.imageUrl != null)
              SizedBox(
                width: double.infinity,
                height: double.infinity,
                child: CachedNetworkImage(
                imageUrl: banner.imageUrl!,
                  fit: BoxFit.cover,
                  width: double.infinity,
                  height: double.infinity,
                placeholder: (context, url) => Container(
                    width: double.infinity,
                    height: double.infinity,
                  color: AppColors.background,
                  child: const Center(
                    child: CircularProgressIndicator(),
                  ),
                ),
                errorWidget: (context, url, error) => Container(
                    width: double.infinity,
                    height: double.infinity,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: banner.gradientColors ?? [
                        AppColors.primary,
                        AppColors.primary.withOpacity(0.7),
                      ],
                      ),
                    ),
                  ),
                ),
              )
            else
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: banner.gradientColors ?? [
                      AppColors.primary,
                      AppColors.primary.withOpacity(0.7),
                    ],
                  ),
                ),
              ),
            
            // Overlay Gradient
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withOpacity(0.5),
                  ],
                ),
              ),
            ),
            
            // Content
            Positioned(
              left: 16,
              right: 16,
              bottom: 16,
              child: Column(
                crossAxisAlignment: banner.alignment,
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (banner.badge != null)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: banner.badgeColor ?? AppColors.error,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        banner.badge!,
                        style: const TextStyle(
                          color: AppColors.white,
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  
                  if (banner.badge != null) const SizedBox(height: 4),
                  
                  if (banner.title != null)
                    Text(
                      banner.title!,
                      style: const TextStyle(
                        color: AppColors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        height: 1.1,
                        shadows: [
                          Shadow(
                            offset: Offset(0, 2),
                            blurRadius: 4,
                            color: Colors.black26,
                          ),
                        ],
                      ),
                      textAlign: banner.alignment == CrossAxisAlignment.center
                          ? TextAlign.center
                          : banner.alignment == CrossAxisAlignment.end
                              ? TextAlign.right
                              : TextAlign.left,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  
                  if (banner.subtitle != null) const SizedBox(height: 3),
                  
                  if (banner.subtitle != null)
                    Text(
                      banner.subtitle!,
                      style: const TextStyle(
                        color: AppColors.white,
                        fontSize: 11,
                        height: 1.2,
                        shadows: [
                          Shadow(
                            offset: Offset(0, 1),
                            blurRadius: 3,
                            color: Colors.black26,
                          ),
                        ],
                      ),
                      textAlign: banner.alignment == CrossAxisAlignment.center
                          ? TextAlign.center
                          : banner.alignment == CrossAxisAlignment.end
                              ? TextAlign.right
                              : TextAlign.left,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  
                  if (banner.buttonText != null) const SizedBox(height: 8),
                  
                  if (banner.buttonText != null)
                    ElevatedButton(
                      onPressed: banner.onTap,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.white,
                        foregroundColor: banner.buttonColor ?? AppColors.primary,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 6,
                        ),
                        minimumSize: const Size(0, 28),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      child: Text(
                        banner.buttonText!,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class BannerItem {
  final String? imageUrl;
  final List<Color>? gradientColors;
  final String? badge;
  final Color? badgeColor;
  final String? title;
  final String? subtitle;
  final String? buttonText;
  final Color? buttonColor;
  final CrossAxisAlignment alignment;
  final VoidCallback? onTap;

  const BannerItem({
    this.imageUrl,
    this.gradientColors,
    this.badge,
    this.badgeColor,
    this.title,
    this.subtitle,
    this.buttonText,
    this.buttonColor,
    this.alignment = CrossAxisAlignment.start,
    this.onTap,
  });
}

