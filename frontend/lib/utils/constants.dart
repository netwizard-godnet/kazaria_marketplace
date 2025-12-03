import 'package:flutter/material.dart';
import '../config/api_config.dart';

class AppColors {
  // Palette Kazaria selon la charte graphique
  static const Color primary = Color(0xFFf04e26); // Rouge Orange Kazaria
  static const Color primaryLight = Color(0xFFff6b47); // Rouge Orange clair
  static const Color primaryDark = Color(0xFFd63a1a); // Rouge Orange foncé

  static const Color secondary = Color(0xFF26a0f0); // Bleu complémentaire
  static const Color secondaryLight = Color(0xFF47b3ff); // Bleu clair

  static const Color accent = Color(0xFFf0a026); // Orange doré
  static const Color accentLight = Color(0xFFffb347); // Orange doré clair

  // Couleurs de fond modernes
  static const Color background = Color(0xFFFAFAFA); // Blanc cassé
  static const Color surface = Color(0xFFFFFFFF); // Blanc pur
  static const Color surfaceVariant = Color(0xFFF3F4F6); // Gris très clair

  // Couleurs de texte modernes
  static const Color textDark = Color(0xFF111827); // Gris très foncé
  static const Color textMedium = Color(0xFF374151); // Gris moyen
  static const Color textLight = Color(0xFF6B7280); // Gris clair
  static const Color textMuted = Color(0xFF9CA3AF); // Gris très clair

  // Couleurs d'état harmonisées avec Kazaria
  static const Color success = Color(0xFF10B981); // Émeraude (conservé)
  static const Color successLight = Color(0xFFD1FAE5); // Émeraude très clair
  static const Color warning = Color(0xFFF59E0B); // Ambre (conservé)
  static const Color warningLight = Color(0xFFFEF3C7); // Ambre très clair
  static const Color error = Color(0xFFDC2626); // Rouge plus foncé
  static const Color errorLight = Color(0xFFFEE2E2); // Rouge très clair
  static const Color info = Color(0xFF26a0f0); // Bleu Kazaria
  static const Color infoLight = Color(0xFFDBEAFE); // Bleu très clair
  static const Color shadow = Color(0xFF000000); // Ombre

  // Couleurs neutres modernes
  static const Color white = Color(0xFFFFFFFF);
  static const Color black = Color(0xFF000000);
  static const Color border = Color(0xFFE5E7EB);
  static const Color grey50 = Color(0xFFF9FAFB);
  static const Color grey100 = Color(0xFFF3F4F6);
  static const Color grey200 = Color(0xFFE5E7EB);
  static const Color grey300 = Color(0xFFD1D5DB);
  static const Color grey400 = Color(0xFF9CA3AF);
  static const Color grey500 = Color(0xFF6B7280);
  static const Color grey600 = Color(0xFF4B5563);
  static const Color grey700 = Color(0xFF374151);
  static const Color grey800 = Color(0xFF1F2937);
  static const Color grey900 = Color(0xFF111827);

  // Dégradés modernes
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [primary, primaryLight],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient secondaryGradient = LinearGradient(
    colors: [secondary, secondaryLight],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient accentGradient = LinearGradient(
    colors: [accent, accentLight],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient cardGradient = LinearGradient(
    colors: [white, grey50],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );

  static const LinearGradient darkGradient = LinearGradient(
    colors: [grey800, grey900],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );
}

class AppTextStyles {
  // Styles modernes avec Inter font
  static const TextStyle displayLarge = TextStyle(
    fontSize: 32,
    fontWeight: FontWeight.w800,
    color: AppColors.textDark,
    letterSpacing: -0.5,
    height: 1.2,
  );

  static const TextStyle displayMedium = TextStyle(
    fontSize: 28,
    fontWeight: FontWeight.w700,
    color: AppColors.textDark,
    letterSpacing: -0.3,
    height: 1.25,
  );

  static const TextStyle displaySmall = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w700,
    color: AppColors.textDark,
    letterSpacing: -0.2,
    height: 1.3,
  );

  static const TextStyle headlineLarge = TextStyle(
    fontSize: 22,
    fontWeight: FontWeight.w600,
    color: AppColors.textDark,
    letterSpacing: 0,
    height: 1.3,
  );

  static const TextStyle headlineMedium = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w600,
    color: AppColors.textDark,
    letterSpacing: 0,
    height: 1.35,
  );

  static const TextStyle headlineSmall = TextStyle(
    fontSize: 18,
    fontWeight: FontWeight.w600,
    color: AppColors.textDark,
    letterSpacing: 0,
    height: 1.4,
  );

  static const TextStyle titleLarge = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    color: AppColors.textDark,
    letterSpacing: 0,
    height: 1.4,
  );

  static const TextStyle titleMedium = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w500,
    color: AppColors.textMedium,
    letterSpacing: 0.1,
    height: 1.4,
  );

  static const TextStyle titleSmall = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w500,
    color: AppColors.textMedium,
    letterSpacing: 0.1,
    height: 1.4,
  );

  static const TextStyle bodyLarge = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w400,
    color: AppColors.textDark,
    letterSpacing: 0,
    height: 1.5,
  );

  static const TextStyle bodyMedium = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w400,
    color: AppColors.textMedium,
    letterSpacing: 0,
    height: 1.5,
  );

  static const TextStyle bodySmall = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w400,
    color: AppColors.textLight,
    letterSpacing: 0,
    height: 1.5,
  );

  static const TextStyle labelLarge = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w500,
    color: AppColors.textMedium,
    letterSpacing: 0.1,
    height: 1.4,
  );

  static const TextStyle labelMedium = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w500,
    color: AppColors.textLight,
    letterSpacing: 0.1,
    height: 1.4,
  );

  static const TextStyle labelSmall = TextStyle(
    fontSize: 10,
    fontWeight: FontWeight.w500,
    color: AppColors.textMuted,
    letterSpacing: 0.1,
    height: 1.4,
  );

  // Styles spéciaux
  static const TextStyle button = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    color: AppColors.white,
    letterSpacing: 0.5,
    height: 1.2,
  );

  static const TextStyle caption = TextStyle(
    fontSize: 11,
    fontWeight: FontWeight.w400,
    color: AppColors.textMuted,
    letterSpacing: 0,
    height: 1.4,
  );

  // Styles hérités pour compatibilité
  static const TextStyle h1 = displayLarge;
  static const TextStyle h2 = displayMedium;
  static const TextStyle h3 = displaySmall;
  static const TextStyle h4 = headlineMedium;
  static const TextStyle body = bodyLarge;
}

class AppSizes {
  // Espacements modernes (basés sur 8px grid)
  static const double space1 = 4.0;
  static const double space2 = 8.0;
  static const double space3 = 12.0;
  static const double space4 = 16.0;
  static const double space5 = 20.0;
  static const double space6 = 24.0;
  static const double space8 = 32.0;
  static const double space10 = 40.0;
  static const double space12 = 48.0;
  static const double space16 = 64.0;
  static const double space20 = 80.0;

  // Anciens noms pour compatibilité
  static const double paddingSmall = space2;
  static const double paddingMedium = space4;
  static const double paddingLarge = space6;

  // Rayons modernes
  static const double radiusXS = 4.0;
  static const double radiusSM = 6.0;
  static const double radiusMD = 8.0;
  static const double radiusLG = 12.0;
  static const double radiusXL = 16.0;
  static const double radius2XL = 20.0;
  static const double radius3XL = 24.0;

  // Anciens noms pour compatibilité
  static const double radiusSmall = radiusSM;
  static const double radiusMedium = radiusMD;
  static const double radiusLarge = radiusLG;

  // Tailles d'icônes
  static const double iconXS = 12.0;
  static const double iconSM = 16.0;
  static const double iconMD = 20.0;
  static const double iconLG = 24.0;
  static const double iconXL = 28.0;
  static const double icon2XL = 32.0;

  // Anciens noms pour compatibilité
  static const double iconSmall = iconSM;
  static const double iconMedium = iconLG;
  static const double iconLarge = icon2XL;

  // Hauteurs et largeurs spéciales
  static const double buttonHeight = 48.0;
  static const double buttonHeightSmall = 36.0;
  static const double buttonHeightLarge = 56.0;

  static const double inputHeight = 48.0;
  static const double cardMinHeight = 120.0;
  static const double avatarSize = 40.0;
  static const double avatarSizeLarge = 56.0;
}

class AppShadows {
  // Ombres modernes avec différentes élévations
  static const List<BoxShadow> shadowXS = [
    BoxShadow(
      color: Color(0x0A000000),
      offset: Offset(0, 1),
      blurRadius: 2,
      spreadRadius: 0,
    ),
  ];

  static const List<BoxShadow> shadowSM = [
    BoxShadow(
      color: Color(0x14000000),
      offset: Offset(0, 1),
      blurRadius: 3,
      spreadRadius: 0,
    ),
    BoxShadow(
      color: Color(0x0A000000),
      offset: Offset(0, 1),
      blurRadius: 2,
      spreadRadius: -1,
    ),
  ];

  static const List<BoxShadow> shadowMD = [
    BoxShadow(
      color: Color(0x1A000000),
      offset: Offset(0, 4),
      blurRadius: 6,
      spreadRadius: -1,
    ),
    BoxShadow(
      color: Color(0x0F000000),
      offset: Offset(0, 2),
      blurRadius: 4,
      spreadRadius: -2,
    ),
  ];

  static const List<BoxShadow> shadowLG = [
    BoxShadow(
      color: Color(0x25000000),
      offset: Offset(0, 10),
      blurRadius: 15,
      spreadRadius: -3,
    ),
    BoxShadow(
      color: Color(0x12000000),
      offset: Offset(0, 4),
      blurRadius: 6,
      spreadRadius: -4,
    ),
  ];

  static const List<BoxShadow> shadowXL = [
    BoxShadow(
      color: Color(0x30000000),
      offset: Offset(0, 20),
      blurRadius: 25,
      spreadRadius: -5,
    ),
    BoxShadow(
      color: Color(0x15000000),
      offset: Offset(0, 10),
      blurRadius: 10,
      spreadRadius: -5,
    ),
  ];

  static const List<BoxShadow> shadow2XL = [
    BoxShadow(
      color: Color(0x40000000),
      offset: Offset(0, 25),
      blurRadius: 50,
      spreadRadius: -12,
    ),
  ];
}

class AppAnimations {
  // Durées d'animation modernes
  static const Duration fast = Duration(milliseconds: 150);
  static const Duration normal = Duration(milliseconds: 250);
  static const Duration slow = Duration(milliseconds: 350);
  static const Duration slower = Duration(milliseconds: 500);

  // Courbes d'animation modernes
  static const Curve easeInOut = Curves.easeInOut;
  static const Curve easeOut = Curves.easeOut;
  static const Curve easeIn = Curves.easeIn;
  static const Curve bounceOut = Curves.bounceOut;
  static const Curve elasticOut = Curves.elasticOut;
}

/// ✅ Utilitaire pour corriger les URLs d'images
class ImageUrlHelper {
  /// Corriger et construire l'URL d'image
  /// Remplace 127.0.0.1/localhost par 10.0.2.2 pour l'émulateur Android
  static String fixImageUrl(String imagePath) {
    // Si l'URL est déjà complète
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      // ✅ CORRECTION : Remplacer 127.0.0.1 ou localhost par 10.0.2.2 pour l'émulateur Android
      if (imagePath.contains('127.0.0.1') || imagePath.contains('localhost')) {
        return imagePath
            .replaceAll('127.0.0.1', '10.0.2.2')
            .replaceAll('localhost', '10.0.2.2');
      }
      return imagePath;
    }

    // ✅ CORRECTION : Si c'est "http:" sans "//" (erreur commune)
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }

    // ✅ CORRECTION : Si c'est "https:" sans "//" (erreur commune)
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }

    // Si c'est un chemin relatif (commence par "products/", "storage/", etc.)
    // Construire l'URL complète avec ApiConfig.imageBaseUrl
    if (imagePath.startsWith('storage/')) {
      return '${ApiConfig.imageBaseUrl}/$imagePath';
    } else if (imagePath.startsWith('products/') ||
        imagePath.startsWith('images/')) {
      return '${ApiConfig.imageBaseUrl}/storage/$imagePath';
    }

    // Sinon, construire l'URL complète
    return '${ApiConfig.imageBaseUrl}/$imagePath';
  }
}
