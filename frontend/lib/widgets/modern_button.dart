import 'package:flutter/material.dart';
import '../utils/constants.dart';

enum ModernButtonType {
  primary,
  secondary,
  outline,
  ghost,
  gradient,
}

enum ModernButtonSize {
  small,
  medium,
  large,
}

class ModernButton extends StatefulWidget {
  final String text;
  final VoidCallback? onPressed;
  final ModernButtonType type;
  final ModernButtonSize size;
  final IconData? icon;
  final bool isLoading;
  final bool isFullWidth;
  final EdgeInsetsGeometry? padding;
  final BorderRadius? borderRadius;

  const ModernButton({
    super.key,
    required this.text,
    this.onPressed,
    this.type = ModernButtonType.primary,
    this.size = ModernButtonSize.medium,
    this.icon,
    this.isLoading = false,
    this.isFullWidth = false,
    this.padding,
    this.borderRadius,
  });

  @override
  State<ModernButton> createState() => _ModernButtonState();
}

class _ModernButtonState extends State<ModernButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: AppAnimations.fast,
      vsync: this,
    );
    _scaleAnimation = Tween<double>(
      begin: 1.0,
      end: 0.95,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: AppAnimations.easeOut,
    ));
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  void _onTapDown(TapDownDetails details) {
    _animationController.forward();
  }

  void _onTapUp(TapUpDetails details) {
    _animationController.reverse();
  }

  void _onTapCancel() {
    _animationController.reverse();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _scaleAnimation,
      builder: (context, child) {
        return Transform.scale(
          scale: _scaleAnimation.value,
          child: GestureDetector(
            onTapDown: widget.onPressed != null ? _onTapDown : null,
            onTapUp: widget.onPressed != null ? _onTapUp : null,
            onTapCancel: widget.onPressed != null ? _onTapCancel : null,
            onTap: widget.isLoading ? null : widget.onPressed,
            child: Container(
              width: widget.isFullWidth ? double.infinity : null,
              height: _getButtonHeight(),
              padding: widget.padding ?? _getButtonPadding(),
              decoration: BoxDecoration(
                gradient: _getGradient(),
                color: _getBackgroundColor(),
                borderRadius: widget.borderRadius ?? _getBorderRadius(),
                border: _getBorder(),
                boxShadow: _getShadow(),
              ),
              child: Row(
                mainAxisSize: widget.isFullWidth ? MainAxisSize.max : MainAxisSize.min,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  if (widget.isLoading)
                    SizedBox(
                      width: _getIconSize(),
                      height: _getIconSize(),
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(_getTextColor()),
                      ),
                    )
                  else if (widget.icon != null) ...[
                    Icon(
                      widget.icon,
                      size: _getIconSize(),
                      color: _getTextColor(),
                    ),
                    SizedBox(width: AppSizes.space2),
                  ],
                  Text(
                    widget.text,
                    style: _getTextStyle(),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  double _getButtonHeight() {
    switch (widget.size) {
      case ModernButtonSize.small:
        return AppSizes.buttonHeightSmall;
      case ModernButtonSize.medium:
        return AppSizes.buttonHeight;
      case ModernButtonSize.large:
        return AppSizes.buttonHeightLarge;
    }
  }

  EdgeInsetsGeometry _getButtonPadding() {
    switch (widget.size) {
      case ModernButtonSize.small:
        return const EdgeInsets.symmetric(
          horizontal: AppSizes.space4,
          vertical: AppSizes.space2,
        );
      case ModernButtonSize.medium:
        return const EdgeInsets.symmetric(
          horizontal: AppSizes.space6,
          vertical: AppSizes.space3,
        );
      case ModernButtonSize.large:
        return const EdgeInsets.symmetric(
          horizontal: AppSizes.space8,
          vertical: AppSizes.space4,
        );
    }
  }

  double _getIconSize() {
    switch (widget.size) {
      case ModernButtonSize.small:
        return AppSizes.iconSM;
      case ModernButtonSize.medium:
        return AppSizes.iconMD;
      case ModernButtonSize.large:
        return AppSizes.iconLG;
    }
  }

  TextStyle _getTextStyle() {
    switch (widget.size) {
      case ModernButtonSize.small:
        return AppTextStyles.labelMedium.copyWith(color: _getTextColor());
      case ModernButtonSize.medium:
        return AppTextStyles.button.copyWith(color: _getTextColor());
      case ModernButtonSize.large:
        return AppTextStyles.button.copyWith(
          fontSize: 18,
          color: _getTextColor(),
        );
    }
  }

  Color _getTextColor() {
    switch (widget.type) {
      case ModernButtonType.primary:
      case ModernButtonType.gradient:
        return AppColors.white;
      case ModernButtonType.secondary:
        return AppColors.white;
      case ModernButtonType.outline:
        return AppColors.primary;
      case ModernButtonType.ghost:
        return AppColors.primary;
    }
  }

  Color? _getBackgroundColor() {
    switch (widget.type) {
      case ModernButtonType.primary:
        return AppColors.primary;
      case ModernButtonType.secondary:
        return AppColors.secondary;
      case ModernButtonType.outline:
      case ModernButtonType.ghost:
        return Colors.transparent;
      case ModernButtonType.gradient:
        return null; // Utilise le gradient
    }
  }

  Gradient? _getGradient() {
    switch (widget.type) {
      case ModernButtonType.gradient:
        return AppColors.primaryGradient;
      default:
        return null;
    }
  }

  Border? _getBorder() {
    switch (widget.type) {
      case ModernButtonType.outline:
        return Border.all(color: AppColors.primary, width: 1.5);
      default:
        return null;
    }
  }

  List<BoxShadow> _getShadow() {
    switch (widget.type) {
      case ModernButtonType.primary:
      case ModernButtonType.gradient:
        return AppShadows.shadowMD;
      case ModernButtonType.secondary:
        return AppShadows.shadowMD;
      case ModernButtonType.outline:
      case ModernButtonType.ghost:
        return AppShadows.shadowXS;
    }
  }

  BorderRadius _getBorderRadius() {
    switch (widget.size) {
      case ModernButtonSize.small:
        return BorderRadius.circular(AppSizes.radiusSM);
      case ModernButtonSize.medium:
        return BorderRadius.circular(AppSizes.radiusMD);
      case ModernButtonSize.large:
        return BorderRadius.circular(AppSizes.radiusLG);
    }
  }
}
