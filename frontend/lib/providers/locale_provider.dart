import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class LocaleProvider extends ChangeNotifier {
  Locale _locale = const Locale('fr'); // Français par défaut
  
  Locale get locale => _locale;
  
  LocaleProvider() {
    _loadLocale();
  }
  
  /// Charger la langue sauvegardée
  Future<void> _loadLocale() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final languageCode = prefs.getString('language_code') ?? 'fr';
      _locale = Locale(languageCode);
      notifyListeners();
    } catch (e) {
      print('Error loading locale: $e');
    }
  }
  
  /// Changer la langue
  Future<void> setLocale(Locale locale) async {
    if (_locale == locale) return;
    
    try {
      _locale = locale;
      notifyListeners();
      
      // Sauvegarder la préférence
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('language_code', locale.languageCode);
    } catch (e) {
      print('Error setting locale: $e');
    }
  }
  
  /// Changer la langue par code
  Future<void> setLanguageCode(String languageCode) async {
    await setLocale(Locale(languageCode));
  }
  
  /// Obtenir le nom de la langue actuelle
  String get currentLanguageName {
    switch (_locale.languageCode) {
      case 'fr':
        return 'Français';
      case 'en':
        return 'English';
      default:
        return 'Français';
    }
  }
  
  /// Obtenir la liste des langues disponibles
  List<Map<String, String>> get availableLanguages => [
    {'code': 'fr', 'name': 'Français', 'flag': '🇫🇷'},
    {'code': 'en', 'name': 'English', 'flag': '🇬🇧'},
  ];
}

