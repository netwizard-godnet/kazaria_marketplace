import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../utils/constants.dart';
import '../../widgets/custom_text_field.dart';
import 'seller_dashboard_screen.dart';
import 'seller_verify_code_screen.dart';

class SellerLoginScreen extends StatefulWidget {
  const SellerLoginScreen({super.key});

  @override
  State<SellerLoginScreen> createState() => _SellerLoginScreenState();
}

class _SellerLoginScreenState extends State<SellerLoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    print('🔵 [SELLER_LOGIN] Début de la connexion vendeur');
    print('📧 [SELLER_LOGIN] Email: ${_emailController.text.trim()}');

    setState(() {
      _isLoading = true;
    });

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    
    print('🔄 [SELLER_LOGIN] Appel de authProvider.login()');
    final success = await authProvider.login(
      email: _emailController.text.trim(),
      password: _passwordController.text.trim(),
    );

    print('📊 [SELLER_LOGIN] Résultat login: $success');
    print('👤 [SELLER_LOGIN] User après login: ${authProvider.user}');
    
    if (authProvider.user != null) {
      print('✅ [SELLER_LOGIN] User ID: ${authProvider.user!.id}');
      print('✅ [SELLER_LOGIN] User nom: ${authProvider.user!.nom}');
      print('✅ [SELLER_LOGIN] User email: ${authProvider.user!.email}');
      print('✅ [SELLER_LOGIN] User isSeller: ${authProvider.user!.isSeller}');
    } else {
      print('❌ [SELLER_LOGIN] User est null');
    }

    setState(() {
      _isLoading = false;
    });

    if (!mounted) return;

    // Vérifier si la réponse est un Map
    if (success is Map<String, dynamic>) {
      print('📦 [SELLER_LOGIN] Réponse de type Map');
      
      if (success['success'] == true) {
        print('✅ [SELLER_LOGIN] API success = true');
        
        // Vérifier si un code est requis
        if (success['requires_code'] == true) {
          print('📧 [SELLER_LOGIN] Code requis - Redirection vers vérification');
          
          // Rediriger vers la page de vérification du code
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (_) => SellerVerifyCodeScreen(
                email: _emailController.text.trim(),
              ),
            ),
          );
        } else {
          // Connexion directe réussie
          print('✅ [SELLER_LOGIN] Connexion directe réussie');
          final user = authProvider.user;
          
          if (user != null && user.isSeller) {
            print('🎉 [SELLER_LOGIN] Utilisateur est vendeur - Redirection vers dashboard');
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(builder: (_) => const SellerDashboardScreen()),
            );
          } else {
            print('⚠️ [SELLER_LOGIN] Utilisateur N\'EST PAS vendeur');
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Vous n\'avez pas les droits de vendeur'),
                backgroundColor: AppColors.error,
              ),
            );
            await authProvider.logout();
          }
        }
      } else {
        print('❌ [SELLER_LOGIN] API success = false');
        print('❌ [SELLER_LOGIN] Message: ${success['message']}');
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(success['message'] ?? 'Erreur de connexion'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } else {
      print('❌ [SELLER_LOGIN] Réponse inattendue de type: ${success.runtimeType}');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.error ?? 'Erreur de connexion'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSizes.paddingLarge),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: AppSizes.space8),
                
                // Logo
                Center(
                  child: Container(
                    width: 120,
                    height: 120,
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.store,
                      size: 60,
                      color: AppColors.primary,
                    ),
                  ),
                ),
                
                const SizedBox(height: AppSizes.space6),
                
                // Titre
                Center(
                  child: Text(
                    'Espace Vendeur',
                    style: AppTextStyles.h1.copyWith(
                      color: AppColors.textDark,
                    ),
                  ),
                ),
                
                const SizedBox(height: AppSizes.space2),
                
                Center(
                  child: Text(
                    'Gérez votre boutique en ligne',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: AppColors.textMuted,
                    ),
                  ),
                ),
                
                const SizedBox(height: AppSizes.space8),
                
                // Email
                CustomTextField(
                  controller: _emailController,
                  label: 'Email',
                  hint: 'votre@email.com',
                  keyboardType: TextInputType.emailAddress,
                  prefixIcon: Icons.email_outlined,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer votre email';
                    }
                    if (!value.contains('@')) {
                      return 'Email invalide';
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: AppSizes.space4),
                
                // Mot de passe
                CustomTextField(
                  controller: _passwordController,
                  label: 'Mot de passe',
                  hint: '••••••••',
                  obscureText: _obscurePassword,
                  prefixIcon: Icons.lock_outlined,
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                      color: AppColors.textMuted,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscurePassword = !_obscurePassword;
                      });
                    },
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer votre mot de passe';
                    }
                    if (value.length < 6) {
                      return 'Le mot de passe doit contenir au moins 6 caractères';
                    }
                    return null;
                  },
                ),
                
                const SizedBox(height: AppSizes.space6),
                
                // Bouton de connexion
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _login,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                      ),
                    ),
                    child: _isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(AppColors.white),
                            ),
                          )
                        : const Text('Se connecter'),
                  ),
                ),
                
                const SizedBox(height: AppSizes.space4),
                
                // Lien vers connexion client
                Center(
                  child: TextButton(
                    onPressed: () {
                      Navigator.of(context).pop();
                    },
                    child: Text(
                      'Retour à la connexion client',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: AppColors.primary,
                      ),
                    ),
                  ),
                ),
                
                const SizedBox(height: AppSizes.space6),
                
                // Info
                Container(
                  padding: const EdgeInsets.all(AppSizes.paddingMedium),
                  decoration: BoxDecoration(
                    color: AppColors.infoLight,
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                    border: Border.all(
                      color: AppColors.info.withOpacity(0.3),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.info_outline,
                        color: AppColors.info,
                        size: 20,
                      ),
                      const SizedBox(width: AppSizes.space2),
                      Expanded(
                        child: Text(
                          'Cet espace est réservé aux vendeurs. Si vous souhaitez devenir vendeur, contactez-nous.',
                          style: AppTextStyles.caption.copyWith(
                            color: AppColors.info,
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
  }
}
