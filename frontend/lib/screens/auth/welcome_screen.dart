import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../widgets/custom_button.dart';
import 'login_screen.dart';
import 'register_screen.dart';

class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(AppSizes.paddingLarge),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Spacer(),
              // Logo
              SizedBox(
                width: 120,
                height: 120,
                child: Image.asset(
                  'assets/images/logoKaz.png',
                  fit: BoxFit.contain,
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'KAZARIA',
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textDark,
                  letterSpacing: 2,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Votre marketplace préféré',
                style: AppTextStyles.body,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 48),
              const Text(
                'Découvrez des milliers de produits\nde vendeurs de confiance',
                style: AppTextStyles.bodySmall,
                textAlign: TextAlign.center,
              ),
              const Spacer(),
              // Boutons
              CustomButton(
                text: 'Se connecter',
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                },
                width: double.infinity,
                color: const Color(0xFF1A73E8), // Bleu pour cette page uniquement
              ),
              const SizedBox(height: 16),
              CustomButton(
                text: 'Créer un compte',
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const RegisterScreen()),
                  );
                },
                isOutlined: true,
                width: double.infinity,
                color: const Color(0xFF1A73E8), // Bleu pour cette page uniquement
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }
}

