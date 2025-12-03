import '../config/api_config.dart';
import 'api_service.dart';

class AIChatService {
  final ApiService _apiService = ApiService();

  /// Envoyer un message au chatbot IA
  Future<Map<String, dynamic>> sendMessage({
    required String message,
    List<Map<String, String>>? conversationHistory,
  }) async {
    try {
      final body = {
        'message': message,
        if (conversationHistory != null)
          'history': conversationHistory,
      };

      print('🤖 [AI_CHAT] Envoi message: $message');

      final response = await _apiService.post(
        '${ApiConfig.baseUrl}/ai/query',
        body,
        requiresAuth: false, // Public endpoint selon routes/api.php
      );

      print('📥 [AI_CHAT] Réponse reçue: ${response['success']}');

      return response;
    } catch (e) {
      print('❌ [AI_CHAT] Erreur: $e');
      return {
        'success': false,
        'message': 'Erreur de connexion au chatbot',
      };
    }
  }

  /// Obtenir des suggestions de questions
  Future<List<String>> getSuggestedQuestions() async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/ai/suggested-questions',
        requiresAuth: false,
      );

      if (response['success'] == true && response['questions'] != null) {
        return List<String>.from(response['questions']);
      }

      return _getDefaultSuggestions();
    } catch (e) {
      return _getDefaultSuggestions();
    }
  }

  List<String> _getDefaultSuggestions() {
    return [
      "Quels sont les meilleurs smartphones ?",
      "Avez-vous des promotions en cours ?",
      "Comment devenir vendeur ?",
      "Quels sont les modes de paiement ?",
      "Délai de livraison pour Abidjan ?",
    ];
  }

  /// Réinitialiser la conversation
  Future<void> resetConversation() async {
    // Peut être implémenté côté serveur si nécessaire
    print('🔄 [AI_CHAT] Conversation réinitialisée');
  }
}

