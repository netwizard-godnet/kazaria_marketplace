import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../models/banner_model.dart';

class BannerService {
  static final BannerService _instance = BannerService._internal();
  factory BannerService() => _instance;
  BannerService._internal();

  /// Récupérer les bannières actives
  Future<Map<String, dynamic>> getActiveBanners({String? type, String? placement}) async {
    try {
      String queryParam = '';
      if (type != null) {
        queryParam = '?type=$type';
      } else if (placement != null) {
        queryParam = '?placement=$placement';
      }
      final uri = Uri.parse('${ApiConfig.mobileBanners}$queryParam');

      print(
        '🎨 [BANNERS] Récupération des bannières${type != null ? " type: $type" : ""}',
      );

      final response = await http.get(uri);
      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final List<BannerModel> banners = [];

        for (var bannerJson in data['data'] ?? []) {
          try {
            banners.add(BannerModel.fromJson(bannerJson));
          } catch (e) {
            print('⚠️ Erreur parsing bannière: $e');
          }
        }

        return {'success': true, 'banners': banners};
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur',
          'banners': <BannerModel>[],
        };
      }
    } catch (e) {
      print('💥 [BANNERS] Exception: $e');
      return {
        'success': false,
        'message': e.toString(),
        'banners': <BannerModel>[],
      };
    }
  }

  /// Vérifier si une bannière doit être affichée
  Future<bool> shouldShowBanner(int bannerId, String displayFrequency) async {
    final prefs = await SharedPreferences.getInstance();
    final key = 'banner_$bannerId';

    if (displayFrequency == 'always') {
      return true;
    }

    if (displayFrequency == 'once') {
      final shown = prefs.getBool(key) ?? false;
      return !shown;
    }

    if (displayFrequency == 'daily') {
      final lastShown = prefs.getString('${key}_date');
      if (lastShown == null) return true;

      final lastDate = DateTime.parse(lastShown);
      final now = DateTime.now();

      // Vérifier si c'est un jour différent
      return lastDate.day != now.day ||
          lastDate.month != now.month ||
          lastDate.year != now.year;
    }

    return true;
  }

  /// Marquer une bannière comme vue
  Future<void> markAsShown(int bannerId) async {
    final prefs = await SharedPreferences.getInstance();
    final key = 'banner_$bannerId';

    await prefs.setBool(key, true);
    await prefs.setString('${key}_date', DateTime.now().toIso8601String());

    // Tracker dans le backend
    await trackView(bannerId);
  }

  /// Tracker la vue d'une bannière
  Future<void> trackView(int bannerId) async {
    try {
      await http.post(Uri.parse('${ApiConfig.baseUrl}/banners/$bannerId/view'));
    } catch (e) {
      print('⚠️ Erreur tracking view: $e');
    }
  }

  /// Tracker le clic sur une bannière
  Future<void> trackClick(int bannerId) async {
    try {
      await http.post(
        Uri.parse('${ApiConfig.baseUrl}/banners/$bannerId/click'),
      );
    } catch (e) {
      print('⚠️ Erreur tracking click: $e');
    }
  }
}
