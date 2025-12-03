import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/comparison_provider.dart';
import '../screens/comparison/product_comparison_screen.dart';
import '../utils/constants.dart';

class ComparisonFloatingButton extends StatelessWidget {
  const ComparisonFloatingButton({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<ComparisonProvider>(
      builder: (context, provider, _) {
        if (provider.selectedCount == 0) {
          return const SizedBox.shrink();
        }

        return Positioned(
          bottom: 80,
          right: 16,
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOutBack,
            child: Material(
              elevation: 8,
              borderRadius: BorderRadius.circular(30),
              child: InkWell(
                onTap: () {
                  if (provider.canCompare) {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const ProductComparisonScreen(),
                      ),
                    );
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Sélectionnez au moins 2 produits pour comparer'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                  }
                },
                borderRadius: BorderRadius.circular(30),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 12,
                  ),
                  decoration: BoxDecoration(
                    gradient: provider.canCompare
                        ? AppColors.primaryGradient
                        : const LinearGradient(
                            colors: [Colors.grey, Colors.grey],
                          ),
                    borderRadius: BorderRadius.circular(30),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Stack(
                        children: [
                          const Icon(
                            Icons.compare_arrows,
                            color: Colors.white,
                            size: 24,
                          ),
                          Positioned(
                            right: -2,
                            top: -2,
                            child: Container(
                              padding: const EdgeInsets.all(4),
                              decoration: const BoxDecoration(
                                color: AppColors.error,
                                shape: BoxShape.circle,
                              ),
                              constraints: const BoxConstraints(
                                minWidth: 16,
                                minHeight: 16,
                              ),
                              child: Text(
                                '${provider.selectedCount}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(width: 12),
                      Text(
                        provider.canCompare
                            ? 'Comparer (${provider.selectedCount})'
                            : '${provider.selectedCount}/4',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}

