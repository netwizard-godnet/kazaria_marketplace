import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/review_model.dart';
import 'storage_service.dart';

class ReviewService {
  static final ReviewService _instance = ReviewService._internal();
  factory ReviewService() => _instance;
  ReviewService._internal();

  final StorageService _storageService = StorageService();

  /// Headers avec token d'authentification
  Future<Map<String, String>> _getHeaders() async {
    final token = await _storageService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  /// Récupérer les avis d'un produit
  Future<Map<String, dynamic>> getProductReviews(int productId) async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse('${ApiConfig.baseUrl}/products/$productId/reviews');

      print('🔄 [REVIEWS] Récupération des avis du produit $productId');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);

      print('📥 [REVIEWS] Réponse: ${response.statusCode}');
      print('📊 [REVIEWS] Clés disponibles: ${data?.keys}');

      if (response.statusCode == 200 && data['success'] == true) {
        // ✅ Le backend retourne directement reviews et stats (pas dans data['data'])
        final reviewsList = data['reviews'];
        final statsData = data['stats'];

        print('📦 [REVIEWS] Type reviews: ${reviewsList?.runtimeType}');
        print('📊 [REVIEWS] Stats: $statsData');

        // Parser les avis
        final List<ReviewModel> reviews = [];

        if (reviewsList != null) {
          // ✅ Support si reviewsList est une pagination Laravel
          final reviewsArray = reviewsList is Map
              ? (reviewsList['data'] as List? ?? [])
              : (reviewsList as List? ?? []);

          for (var reviewJson in reviewsArray) {
            try {
              reviews.add(
                ReviewModel.fromJson(reviewJson as Map<String, dynamic>),
              );
            } catch (e) {
              print('⚠️ Erreur parsing avis: $e');
            }
          }
        }

        return {
          'success': true,
          'data': {
            'reviews': reviews,
            'stats': statsData ?? {},
            'pagination': reviewsList is Map ? reviewsList : {},
          },
        };
      } else {
        return {
          'success': false,
          'message':
              data['message'] ?? 'Erreur lors de la récupération des avis',
          'data': {'reviews': <ReviewModel>[], 'stats': {}},
        };
      }
    } catch (e) {
      print('💥 [REVIEWS] Exception: $e');
      return {
        'success': false,
        'message': e.toString(),
        'data': {'reviews': <ReviewModel>[], 'stats': {}},
      };
    }
  }

  /// Soumettre un avis
  Future<Map<String, dynamic>> submitReview({
    required int productId,
    required int rating,
    required String comment,
    String? title,
    List<File>? images,
  }) async {
    try {
      final token = await _storageService.getToken();
      if (token == null) {
        return {
          'success': false,
          'message': 'Vous devez être connecté pour laisser un avis',
        };
      }

      final uri = Uri.parse('${ApiConfig.baseUrl}/reviews');

      print('🔄 [REVIEWS] Soumission d\'un avis pour le produit $productId');

      // Créer une requête multipart si des images sont présentes
      var request = http.MultipartRequest('POST', uri);
      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';

      // Ajouter les champs
      request.fields['product_id'] = productId.toString();
      request.fields['rating'] = rating.toString();
      request.fields['comment'] = comment;
      if (title != null && title.isNotEmpty) {
        request.fields['title'] = title;
      }

      // Ajouter les images si présentes
      if (images != null && images.isNotEmpty) {
        for (var i = 0; i < images.length; i++) {
          final file = images[i];
          request.files.add(
            await http.MultipartFile.fromPath('images[$i]', file.path),
          );
        }
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      final data = json.decode(response.body);

      print('📥 [REVIEWS] Réponse soumission: ${response.statusCode}');
      print('📊 [REVIEWS] Données réponse: $data');

      if ((response.statusCode == 200 || response.statusCode == 201) &&
          data['success'] == true) {
        return {
          'success': true,
          'message': data['message'] ?? 'Avis soumis avec succès',
          'review': data['review'] ?? data['data'],
        };
      } else {
        // ✅ Afficher les erreurs de validation détaillées
        String errorMessage =
            data['message'] ?? 'Erreur lors de la soumission de l\'avis';

        if (data['errors'] != null) {
          final errors = data['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            errorMessage = firstError[0].toString();
          }
        }

        print('❌ [REVIEWS] Erreur: $errorMessage');

        return {'success': false, 'message': errorMessage};
      }
    } catch (e) {
      print('💥 [REVIEWS] Exception soumission: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Voter sur un avis
  Future<Map<String, dynamic>> voteOnReview(
    int reviewId,
    bool isHelpful,
  ) async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse('${ApiConfig.baseUrl}/reviews/$reviewId/vote');

      print('🔄 [REVIEWS] Vote sur l\'avis $reviewId');

      final response = await http.post(
        uri,
        headers: headers,
        body: json.encode({'vote': isHelpful ? 'helpful' : 'not_helpful'}),
      );

      final data = json.decode(response.body);

      print('📥 [REVIEWS] Réponse vote: ${response.statusCode}');

      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': data['message'] ?? 'Vote enregistré',
          'data': data['data'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur lors du vote',
        };
      }
    } catch (e) {
      print('💥 [REVIEWS] Exception vote: $e');
      return {'success': false, 'message': e.toString()};
    }
  }
}
