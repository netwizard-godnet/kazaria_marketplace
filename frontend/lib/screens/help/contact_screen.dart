import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

class ContactScreen extends StatefulWidget {
  const ContactScreen({super.key});

  @override
  State<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends State<ContactScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _subjectController = TextEditingController();
  final _messageController = TextEditingController();
  final _apiService = ApiService();
  bool _isLoading = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _subjectController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _sendMessage() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    final response = await _apiService.post(
      ApiConfig.contact,
      {
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim(),
        'subject': _subjectController.text.trim(),
        'message': _messageController.text.trim(),
      },
    );

    setState(() {
      _isLoading = false;
    });

    if (!mounted) return;

    if (response['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Message envoyé avec succès'),
          backgroundColor: AppColors.success,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'Erreur d\'envoi'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Nous contacter'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Envoyez-nous un message',
                style: AppTextStyles.h2,
              ),
              const SizedBox(height: 8),
              const Text(
                'Nous vous répondrons dans les plus brefs délais',
                style: AppTextStyles.bodySmall,
              ),
              const SizedBox(height: 32),
              CustomTextField(
                label: 'Nom complet',
                controller: _nameController,
                validator: (value) => Helpers.validateRequired(value, 'Le nom'),
              ),
              const SizedBox(height: 16),
              CustomTextField(
                label: 'Email',
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                validator: Helpers.validateEmail,
              ),
              const SizedBox(height: 16),
              CustomTextField(
                label: 'Sujet',
                controller: _subjectController,
                validator: (value) => Helpers.validateRequired(value, 'Le sujet'),
              ),
              const SizedBox(height: 16),
              CustomTextField(
                label: 'Message',
                controller: _messageController,
                maxLines: 5,
                validator: (value) => Helpers.validateRequired(value, 'Le message'),
              ),
              const SizedBox(height: 32),
              CustomButton(
                text: 'Envoyer',
                onPressed: _sendMessage,
                isLoading: _isLoading,
                width: double.infinity,
              ),
              const SizedBox(height: 32),
              const Divider(),
              const SizedBox(height: 16),
              const Text(
                'Autres moyens de contact',
                style: AppTextStyles.h3,
              ),
              const SizedBox(height: 16),
              _buildContactMethod(
                Icons.phone,
                'Téléphone',
                '+225 XX XX XX XX XX',
              ),
              const SizedBox(height: 12),
              _buildContactMethod(
                Icons.email,
                'Email',
                'contact@kazaria.com',
              ),
              const SizedBox(height: 12),
              _buildContactMethod(
                Icons.location_on,
                'Adresse',
                'Abidjan, Côte d\'Ivoire',
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContactMethod(IconData icon, String title, String value) {
    return Row(
      children: [
        Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            color: AppColors.primary.withOpacity(0.1),
            shape: BoxShape.circle,
          ),
          child: Icon(
            icon,
            color: AppColors.primary,
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: AppTextStyles.bodySmall,
              ),
              Text(
                value,
                style: AppTextStyles.body.copyWith(
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

