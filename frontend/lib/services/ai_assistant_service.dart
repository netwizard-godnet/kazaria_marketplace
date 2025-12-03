import 'api_service.dart';
import '../config/api_config.dart';
import '../models/chat_message_model.dart';
import '../models/product_model.dart';

/// 🤖 Service de l'Assistant IA
class AiAssistantService {
  final ApiService _apiService = ApiService();

  /// 💬 Envoyer une requête au chatbot
  Future<Map<String, dynamic>> sendMessage(String query) async {
    try {
      print('🤖 [AI] Envoi requête: $query');

      final response = await _apiService.post(
        ApiConfig.aiQuery,
        {'message': query.trim()},
        requiresAuth: false, // Accessible à tous
      );

      print('✅ [AI] Réponse reçue: ${response['success']}');

      return response;
    } catch (e) {
      print('❌ [AI] Erreur: $e');
      return {
        'success': false,
        'message': 'Désolé, je ne peux pas répondre pour le moment. Vérifiez votre connexion.',
        'error': e.toString(),
      };
    }
  }

  /// 💡 Obtenir des suggestions rapides
  Future<List<String>> getSuggestions() async {
    try {
      final response = await _apiService.get(
        ApiConfig.aiSuggestions,
        requiresAuth: false,
      );

      if (response['success'] && response['suggestions'] != null) {
        return List<String>.from(response['suggestions']);
      }

      return _getDefaultSuggestions();
    } catch (e) {
      print('⚠️ [AI] Erreur suggestions: $e');
      return _getDefaultSuggestions();
    }
  }

  /// 📝 Suggestions par défaut si API échoue
  List<String> _getDefaultSuggestions() {
    return [
      "Montre-moi les smartphones tendance",
      "J'ai 60 000 FCFA, quels téléphones tu proposes ?",
      "Quels sont les meilleurs ordinateurs ?",
      "Je cherche un cadeau à moins de 50 000 FCFA",
      "Montre-moi les promotions du jour",
      "Produits les mieux notés",
    ];
  }

  /// 🎯 Parser la réponse de l'IA
  ChatMessageModel parseResponse(Map<String, dynamic> response) {
    final productsData = response['items'] ?? response['products'];
    final metadata = <String, dynamic>{};

    if (response['intent'] != null) {
      metadata['intent'] = response['intent'];
    }
    if (response['intent_params'] != null) {
      metadata['intent_params'] = response['intent_params'];
    }
    if (response['action_result'] != null) {
      metadata['action_result'] = response['action_result'];
    }
    if (response['understood'] != null) {
      metadata['understood'] = response['understood'];
    }

    return ChatMessageModel(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      text: response['message'] ?? 'Aucune réponse',
      isUser: false,
      timestamp: DateTime.now(),
      suggestedProducts: productsData != null
          ? (productsData as List)
              .map((e) => ProductModel.fromJson(e))
              .toList()
          : null,
      metadata: metadata.isEmpty ? null : metadata,
    );
  }

  /// 📊 Logger l'interaction produit (click, add_to_cart, etc.)
  Future<void> logInteraction({
    required int productId,
    String type = 'click',
  }) async {
    try {
      await _apiService.post(
        ApiConfig.aiInteraction,
        {
          'product_id': productId,
          'type': type,
          'source': 'mobile',
        },
        requiresAuth: false,
      );
    } catch (e) {
      print('⚠️ [AI] Impossible de logger l\'interaction: $e');
    }
  }
}

