import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/auth_provider.dart';
import '../services/app_config_service.dart';
import '../utils/constants.dart';
import '../routes/app_router.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  String? _logoUrl;
  String _appDescription = 'Marketplace préféré';

  @override
  void initState() {
    super.initState();
    _loadAppConfig();
    // Attendre que le widget soit construit avant d'initialiser
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initializeApp();
    });
  }

  Future<void> _loadAppConfig() async {
    try {
      final appConfigService = AppConfigService();
      final response = await appConfigService.getAppLogo();

      if (response['success'] && mounted) {
        setState(() {
          _logoUrl = response['logo'];
          _appDescription =
              response['app_tagline'] ??
              response['app_description'] ??
              'Marketplace premium';
        });
      }
    } catch (e) {
      print('Erreur lors du chargement du logo: $e');
    }
  }

  Future<void> _initializeApp() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    // Vérifier l'état d'authentification
    await authProvider.checkAuthStatus();

    // Attendre au moins 2 secondes pour le splash screen
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;

    // Navigation vers l'écran approprié
    if (authProvider.isAuthenticated) {
      Navigator.of(context).pushReplacementNamed(AppRouter.main);
    } else {
      Navigator.of(context).pushReplacementNamed(AppRouter.welcome);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A73E8), // Bleu corporate
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(
              width: 140,
              height: 140,
              child: Image.asset(
                'assets/images/logoKaz.png',
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) {
                  // Fallback vers le logo distant si le local est indisponible
                  return _logoUrl != null
                      ? CachedNetworkImage(
                          imageUrl: _logoUrl!,
                          fit: BoxFit.contain,
                          placeholder: (context, url) => const Center(
                            child: CircularProgressIndicator(
                              valueColor: AlwaysStoppedAnimation<Color>(
                                AppColors.white,
                              ),
                            ),
                          ),
                          errorWidget: (context, url, error) => const Icon(
                            Icons.shopping_bag,
                            size: 60,
                            color: AppColors.white,
                          ),
                        )
                      : const Icon(
                          Icons.shopping_bag,
                          size: 60,
                          color: Colors.white,
                        );
                },
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _appDescription,
              style: const TextStyle(
                fontSize: 16,
                color: AppColors.white,
                letterSpacing: 1,
              ),
            ),
            const SizedBox(height: 48),
            const CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppColors.white),
            ),
          ],
        ),
      ),
    );
  }
}
