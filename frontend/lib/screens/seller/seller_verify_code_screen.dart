import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../utils/constants.dart';
import 'seller_dashboard_screen.dart';

class SellerVerifyCodeScreen extends StatefulWidget {
  final String email;
  
  const SellerVerifyCodeScreen({
    super.key,
    required this.email,
  });

  @override
  State<SellerVerifyCodeScreen> createState() => _SellerVerifyCodeScreenState();
}

class _SellerVerifyCodeScreenState extends State<SellerVerifyCodeScreen> {
  final List<TextEditingController> _controllers = List.generate(
    8,
    (index) => TextEditingController(),
  );
  final List<FocusNode> _focusNodes = List.generate(
    8,
    (index) => FocusNode(),
  );
  bool _isLoading = false;

  @override
  void dispose() {
    for (var controller in _controllers) {
      controller.dispose();
    }
    for (var node in _focusNodes) {
      node.dispose();
    }
    super.dispose();
  }

  String get _code => _controllers.map((c) => c.text).join();

  Future<void> _verifyCode() async {
    if (_code.length != 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Veuillez entrer le code complet (8 chiffres)'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    print('🔵 [SELLER_VERIFY] Vérification du code: $_code');
    
    setState(() {
      _isLoading = true;
    });

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    
    print('🔄 [SELLER_VERIFY] Appel verifyLoginCode()');
    final result = await authProvider.verifyLoginCode(
      email: widget.email,
      code: _code,
    );

    print('📊 [SELLER_VERIFY] Résultat: ${result['success']}');
    
    if (result['success'] == true) {
      print('✅ [SELLER_VERIFY] Code vérifié avec succès');
      print('👤 [SELLER_VERIFY] User: ${authProvider.user}');
      
      if (authProvider.user != null) {
        print('✅ [SELLER_VERIFY] User isSeller: ${authProvider.user!.isSeller}');
        
        if (authProvider.user!.isSeller) {
          print('🎉 [SELLER_VERIFY] Utilisateur est vendeur - Redirection dashboard');
          
          if (!mounted) return;
          
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => const SellerDashboardScreen()),
          );
        } else {
          print('⚠️ [SELLER_VERIFY] Utilisateur N\'EST PAS vendeur');
          
          if (!mounted) return;
          
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Vous n\'avez pas les droits de vendeur'),
              backgroundColor: AppColors.error,
            ),
          );
          await authProvider.logout();
          Navigator.of(context).pop();
        }
      } else {
        print('❌ [SELLER_VERIFY] User est null après vérification');
      }
    } else {
      print('❌ [SELLER_VERIFY] Erreur: ${result['message']}');
      
      setState(() {
        _isLoading = false;
      });
      
      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Code incorrect'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Future<void> _resendCode() async {
    print('🔄 [SELLER_VERIFY] Renvoi du code');
    
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.resendVerificationCode(widget.email);

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Code renvoyé à votre email'),
        backgroundColor: AppColors.success,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.white,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.textDark),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSizes.paddingLarge),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: AppSizes.space4),
              
              // Icône
              Center(
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.verified_user,
                    size: 40,
                    color: AppColors.primary,
                  ),
                ),
              ),
              
              const SizedBox(height: AppSizes.space4),
              
              // Titre
              Center(
                child: Text(
                  'Vérification Vendeur',
                  style: AppTextStyles.h1.copyWith(
                    color: AppColors.textDark,
                  ),
                ),
              ),
              
              const SizedBox(height: AppSizes.space2),
              
              Center(
                child: Text(
                  'Entrez le code envoyé à',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.textMuted,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
              
              const SizedBox(height: AppSizes.space1),
              
              Center(
                child: Text(
                  widget.email,
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.primary,
                    fontWeight: FontWeight.w600,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
              
              const SizedBox(height: AppSizes.space6),
              
              // Champs de code
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: List.generate(8, (index) {
                  return SizedBox(
                    width: 40,
                    height: 50,
                    child: TextField(
                      controller: _controllers[index],
                      focusNode: _focusNodes[index],
                      textAlign: TextAlign.center,
                      keyboardType: TextInputType.number,
                      maxLength: 1,
                      style: const TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textDark,
                        letterSpacing: 0,
                      ),
                      decoration: InputDecoration(
                        counterText: '',
                        filled: true,
                        fillColor: AppColors.white,
                        contentPadding: const EdgeInsets.symmetric(vertical: 12),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: AppColors.grey400, width: 2),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: AppColors.grey400, width: 2),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: AppColors.primary, width: 3),
                        ),
                      ),
                      inputFormatters: [
                        FilteringTextInputFormatter.digitsOnly,
                      ],
                      onChanged: (value) {
                        if (value.isNotEmpty) {
                          // Forcer la mise à jour de l'affichage
                          setState(() {});
                          
                          if (index < 7) {
                            _focusNodes[index + 1].requestFocus();
                          } else {
                            _focusNodes[index].unfocus();
                            _verifyCode();
                          }
                        }
                      },
                      onTap: () {
                        // Sélectionner tout le texte au tap
                        _controllers[index].selection = TextSelection(
                          baseOffset: 0,
                          extentOffset: _controllers[index].text.length,
                        );
                      },
                    ),
                  );
                }),
              ),
              
              const SizedBox(height: AppSizes.space8),
              
              // Bouton vérifier
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _verifyCode,
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
                      : const Text('Vérifier le code'),
                ),
              ),
              
              const SizedBox(height: AppSizes.space4),
              
              // Renvoyer le code
              Center(
                child: TextButton(
                  onPressed: _resendCode,
                  child: Text(
                    'Renvoyer le code',
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
                        'Pour votre sécurité, nous avons envoyé un code de vérification à votre email.',
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
    );
  }
}
