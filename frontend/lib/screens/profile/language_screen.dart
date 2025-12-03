import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/locale_provider.dart';
import '../../utils/constants.dart';

class LanguageScreen extends StatefulWidget {
  const LanguageScreen({super.key});

  @override
  State<LanguageScreen> createState() => _LanguageScreenState();
}

class _LanguageScreenState extends State<LanguageScreen> {
  String _selectedLanguage = 'fr';

  final List<Map<String, dynamic>> _languages = [
    {
      'code': 'fr',
      'name': 'Français',
      'nativeName': 'Français',
      'flag': '🇫🇷',
    },
    {
      'code': 'en',
      'name': 'Anglais',
      'nativeName': 'English',
      'flag': '🇬🇧',
    },
  ];

  @override
  void initState() {
    super.initState();
    // Récupérer la langue actuelle
    final localeProvider = Provider.of<LocaleProvider>(context, listen: false);
    _selectedLanguage = localeProvider.locale.languageCode;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Langue'),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(AppSizes.space6),
            decoration: BoxDecoration(
              gradient: AppColors.primaryGradient,
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(AppSizes.radius3XL),
                bottomRight: Radius.circular(AppSizes.radius3XL),
              ),
            ),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(AppSizes.space6),
                  decoration: BoxDecoration(
                    color: AppColors.white.withOpacity(0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.language,
                    size: 64,
                    color: AppColors.white,
                  ),
                ),
                const SizedBox(height: AppSizes.space4),
                Text(
                  'Choisissez votre langue',
                  style: AppTextStyles.headlineMedium.copyWith(
                    color: AppColors.white,
                    fontWeight: FontWeight.bold,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: AppSizes.space2),
                Text(
                  'Sélectionnez la langue de l\'application',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.white.withOpacity(0.9),
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
          
          // Liste des langues
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(AppSizes.space4),
              itemCount: _languages.length,
              itemBuilder: (context, index) {
                return _buildLanguageTile(_languages[index]);
              },
            ),
          ),
          
          // Bouton Appliquer
          Padding(
            padding: const EdgeInsets.all(AppSizes.space4),
            child: _buildApplyButton(),
          ),
        ],
      ),
    );
  }

  Widget _buildLanguageTile(Map<String, dynamic> language) {
    final isSelected = _selectedLanguage == language['code'];
    
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space3),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        border: isSelected
            ? Border.all(color: AppColors.primary, width: 2)
            : null,
        boxShadow: isSelected ? AppShadows.shadowLG : AppShadows.shadowMD,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => setState(() => _selectedLanguage = language['code']),
          borderRadius: BorderRadius.circular(AppSizes.radiusXL),
          child: Padding(
            padding: const EdgeInsets.all(AppSizes.space4),
            child: Row(
              children: [
                // Drapeau
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    gradient: isSelected
                        ? AppColors.primaryGradient
                        : LinearGradient(
                            colors: [
                              AppColors.grey100,
                              AppColors.grey50,
                            ],
                          ),
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  ),
                  child: Center(
                    child: Text(
                      language['flag'],
                      style: const TextStyle(fontSize: 32),
                    ),
                  ),
                ),
                
                const SizedBox(width: AppSizes.space4),
                
                // Texte
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        language['name'],
                        style: AppTextStyles.titleLarge.copyWith(
                          fontWeight: FontWeight.w600,
                          color: isSelected
                              ? AppColors.primary
                              : AppColors.textDark,
                        ),
                      ),
                      const SizedBox(height: AppSizes.space1),
                      Text(
                        language['nativeName'],
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                
                // Sélection
                if (isSelected)
                  Container(
                    padding: const EdgeInsets.all(AppSizes.space2),
                    decoration: BoxDecoration(
                      gradient: AppColors.primaryGradient,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.check,
                      color: AppColors.white,
                      size: AppSizes.iconMD,
                    ),
                  )
                else
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: AppColors.grey300,
                        width: 2,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildApplyButton() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: AppColors.primaryGradient,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: AppShadows.shadowLG,
      ),
      child: ElevatedButton(
        onPressed: _applyLanguage,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          shadowColor: Colors.transparent,
          padding: const EdgeInsets.symmetric(vertical: AppSizes.space4),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppSizes.radiusXL),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.check_circle, size: AppSizes.iconMD),
            const SizedBox(width: AppSizes.space2),
            Text(
              'Appliquer',
              style: AppTextStyles.button.copyWith(fontSize: 16),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _applyLanguage() async {
    final language = _languages.firstWhere(
      (lang) => lang['code'] == _selectedLanguage,
    );
    
    // Changer la langue avec le LocaleProvider
    final localeProvider = Provider.of<LocaleProvider>(context, listen: false);
    await localeProvider.setLanguageCode(_selectedLanguage);
    
    if (!mounted) return;
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 12),
            Text('Langue changée: ${language['name']}'),
          ],
        ),
        backgroundColor: AppColors.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
        ),
      ),
    );
    
    // Retour à la page précédente
    Future.delayed(const Duration(seconds: 1), () {
      if (mounted) {
        Navigator.pop(context);
      }
    });
  }
}

