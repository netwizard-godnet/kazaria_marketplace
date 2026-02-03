import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';

class PopupModel {
  final int id;
  final String? title;
  final String? content;
  final String? ctaText;
  final String? ctaUrl;
  final String? image;
  final String? imageUrl;
  final int width;
  final int height;
  final String layout;
  final String frequency;
  final int delaySeconds;
  final String slug;

  PopupModel({
    required this.id,
    this.title,
    this.content,
    this.ctaText,
    this.ctaUrl,
    this.image,
    this.imageUrl,
    this.width = 300,
    this.height = 300,
    this.layout = 'stacked',
    this.frequency = 'once_per_session',
    this.delaySeconds = 0,
    required this.slug,
  });

  factory PopupModel.fromJson(Map<String, dynamic> json) {
    return PopupModel(
      id: json['id'] as int,
      title: json['title'] as String?,
      content: json['content'] as String?,
      ctaText: json['cta_text'] as String?,
      ctaUrl: json['cta_url'] as String?,
      image: json['image'] as String?,
      imageUrl: json['image_url'] as String?,
      width: (json['width'] as int?) ?? 300,
      height: (json['height'] as int?) ?? 300,
      layout: json['layout'] as String? ?? 'stacked',
      frequency: json['frequency'] as String? ?? 'once_per_session',
      delaySeconds: (json['delay_seconds'] as int?) ?? 0,
      slug: json['slug'] as String? ?? '',
    );
  }
}

class PopupService {
  static const String _popupsEndpoint = '${ApiConfig.baseUrl}/popups/active';

  /// Get active popups for mobile app
  Future<List<PopupModel>> getActivePopups() async {
    try {
      print('📱 [POPUP_SERVICE] Fetching active popups...');
      
      final response = await http.get(
        Uri.parse(_popupsEndpoint),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-App-Source': 'mobile',
        },
      ).timeout(
        const Duration(seconds: 10),
        onTimeout: () => throw Exception('Popup fetch timeout'),
      );

      print('📱 [POPUP_SERVICE] Response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final jsonData = jsonDecode(response.body) as Map<String, dynamic>;
        
        if (jsonData['success'] == true) {
          final popupList = (jsonData['data'] as List<dynamic>?)
              ?.map((item) => PopupModel.fromJson(item as Map<String, dynamic>))
              .toList() ?? [];
          
          print('✅ [POPUP_SERVICE] Found ${popupList.length} active popups');
          return popupList;
        }
      }
      
      print('⚠️ [POPUP_SERVICE] No popups available');
      return [];
    } catch (e) {
      print('❌ [POPUP_SERVICE] Error fetching popups: $e');
      return [];
    }
  }

  /// Track popup impression
  Future<bool> trackImpression(int popupId) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/popups/$popupId/impression'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(
        const Duration(seconds: 5),
        onTimeout: () => throw Exception('Impression tracking timeout'),
      );

      return response.statusCode == 200;
    } catch (e) {
      print('❌ [POPUP_SERVICE] Error tracking impression: $e');
      return false;
    }
  }

  /// Track popup click
  Future<bool> trackClick(int popupId) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/popups/$popupId/click'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(
        const Duration(seconds: 5),
        onTimeout: () => throw Exception('Click tracking timeout'),
      );

      return response.statusCode == 200;
    } catch (e) {
      print('❌ [POPUP_SERVICE] Error tracking click: $e');
      return false;
    }
  }
}
