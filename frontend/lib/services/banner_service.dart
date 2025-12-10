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

      print('🎨 [BANNERS] Récupération des bannières');
      print('   - URL: $uri');
      print('   - Type: $type');
      print('   - Placement: $placement');

      final response = await http.get(uri);
      print('📡 [BANNERS] Status code: ${response.statusCode}');
      print('📡 [BANNERS] Response body: ${response.body}');
      
      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final List<BannerModel> banners = [];
        final rawData = data['data'] ?? [];
        print('📊 [BANNERS] Nombre de bannières brutes reçues: ${rawData.length}');

        for (var bannerJson in rawData) {
          try {
            final banner = BannerModel.fromJson(bannerJson);
            banners.add(banner);
            print('   ✅ Bannière parsée: ID=${banner.id}, Ordre=${banner.priority}, Image=${banner.image.isNotEmpty ? "OUI" : "NON"}');
          } catch (e, stackTrace) {
            print('⚠️ [BANNERS] Erreur parsing bannière: $e');
            print('Stack trace: $stackTrace');
            print('   JSON: $bannerJson');
          }
        }

        print('✅ [BANNERS] Total bannières parsées: ${banners.length}');
        return {'success': true, 'banners': banners};
      } else {
        print('❌ [BANNERS] Erreur API: ${data['message'] ?? 'Erreur inconnue'}');
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur',
          'banners': <BannerModel>[],
        };
      }
    } catch (e, stackTrace) {
      print('💥 [BANNERS] Exception: $e');
      print('Stack trace: $stackTrace');
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

  /// Récupérer les bannières publicitaires boutique (boutique_pub_1 à boutique_pub_5)
  Future<Map<String, dynamic>> getBoutiquePubBanners() async {
    try {
      final uri = Uri.parse('${ApiConfig.baseUrl}/mobile/stores/boutique-pub-banners');
      
      print('🎨 [BANNERS] Récupération des bannières publicitaires boutique');
      print('   - URL: $uri');

      final response = await http.get(uri);
      print('📡 [BANNERS] Status code: ${response.statusCode}');
      print('📡 [BANNERS] Response body: ${response.body}');
      
      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final List<BannerModel> banners = [];
        final rawData = data['data'] ?? [];
        print('📊 [BANNERS] Nombre de bannières pub boutique reçues: ${rawData.length}');

        for (var bannerJson in rawData) {
          try {
            final banner = BannerModel.fromJson(bannerJson);
            banners.add(banner);
            print('   ✅ Bannière pub parsée: ID=${banner.id}, Type=${banner.type}, Image=${banner.image.isNotEmpty ? "OUI" : "NON"}');
          } catch (e, stackTrace) {
            print('⚠️ [BANNERS] Erreur parsing bannière pub: $e');
            print('Stack trace: $stackTrace');
            print('   JSON: $bannerJson');
          }
        }

        print('✅ [BANNERS] Total bannières pub parsées: ${banners.length}');
        return {'success': true, 'banners': banners};
      } else {
        print('❌ [BANNERS] Erreur API: ${data['message'] ?? 'Erreur inconnue'}');
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur',
          'banners': <BannerModel>[],
        };
      }
    } catch (e, stackTrace) {
      print('💥 [BANNERS] Exception: $e');
      print('Stack trace: $stackTrace');
      return {
        'success': false,
        'message': e.toString(),
        'banners': <BannerModel>[],
      };
    }
  }
}
