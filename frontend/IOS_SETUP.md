# Configuration iOS - Guide de résolution des erreurs

## 🔴 Erreur : Version minimale iOS insuffisante

### Problème rencontré

Lors du lancement de l'application sur iOS, vous pouvez rencontrer cette erreur :

```
[!] CocoaPods could not find compatible versions for pod "firebase_core":
Specs satisfying the `firebase_core` dependency were found, but they required a higher minimum deployment target.

Error: The plugin "firebase_core" requires a higher minimum iOS deployment version than your application is targeting.
To build, increase your application's deployment target to at least 15.0
```

### Cause

Firebase Core (et d'autres plugins Firebase) nécessitent **iOS 15.0 minimum**. Si votre projet est configuré pour une version antérieure (par exemple iOS 13.0 ou 14.0), CocoaPods ne pourra pas installer les dépendances.

### ✅ Solution

La version minimale iOS a été mise à jour à **15.0** dans tous les fichiers de configuration nécessaires :

1. **Podfile** : `platform :ios, '15.0'`
2. **project.pbxproj** : `IPHONEOS_DEPLOYMENT_TARGET = 15.0` (pour toutes les configurations)
3. **AppFrameworkInfo.plist** : `MinimumOSVersion = 15.0`

### 📋 Étapes pour résoudre le problème

#### Option 1 : Si les fichiers ont déjà été corrigés automatiquement

1. **Nettoyer les dépendances CocoaPods** :
   ```bash
   cd frontend/ios
   rm -rf Pods Podfile.lock
   ```

2. **Réinstaller les pods** :
   ```bash
   pod install --repo-update
   ```

3. **Nettoyer le build Flutter** :
   ```bash
   cd ../..
   flutter clean
   flutter pub get
   ```

4. **Relancer l'application** :
   ```bash
   flutter run
   ```

#### Option 2 : Si vous devez corriger manuellement

1. **Mettre à jour le Podfile** (`frontend/ios/Podfile`) :
   ```ruby
   platform :ios, '15.0'  # Au lieu de '14.0' ou '13.0'
   ```

2. **Mettre à jour le post_install dans le Podfile** :
   ```ruby
   post_install do |installer|
     installer.pods_project.targets.each do |target|
       flutter_additional_ios_build_settings(target)
       target.build_configurations.each do |config|
         config.build_settings['IPHONEOS_DEPLOYMENT_TARGET'] = '15.0'
       end
     end
   end
   ```

3. **Mettre à jour project.pbxproj** (`frontend/ios/Runner.xcodeproj/project.pbxproj`) :
   - Rechercher toutes les occurrences de `IPHONEOS_DEPLOYMENT_TARGET = 13.0;` ou `IPHONEOS_DEPLOYMENT_TARGET = 14.0;`
   - Les remplacer par `IPHONEOS_DEPLOYMENT_TARGET = 15.0;`

4. **Mettre à jour AppFrameworkInfo.plist** (`frontend/ios/Flutter/AppFrameworkInfo.plist`) :
   ```xml
   <key>MinimumOSVersion</key>
   <string>15.0</string>
   ```

5. **Suivre les étapes de l'Option 1** pour nettoyer et réinstaller.

### 🔍 Vérification

Pour vérifier que la configuration est correcte :

```bash
# Vérifier le Podfile
grep "platform :ios" frontend/ios/Podfile

# Vérifier le project.pbxproj
grep "IPHONEOS_DEPLOYMENT_TARGET" frontend/ios/Runner.xcodeproj/project.pbxproj

# Vérifier AppFrameworkInfo.plist
grep "MinimumOSVersion" frontend/ios/Flutter/AppFrameworkInfo.plist
```

Tous doivent afficher **15.0**.

### ⚠️ Notes importantes

1. **Compatibilité des appareils** : iOS 15.0 est disponible sur :
   - iPhone 6s et plus récents
   - iPad (5e génération) et plus récents
   - iPad Air 2 et plus récents
   - iPad mini 4 et plus récents
   - iPod touch (7e génération)

2. **Si vous avez besoin de supporter iOS 13 ou 14** :
   - Vous devrez utiliser une version plus ancienne de Firebase Core
   - Ce n'est **pas recommandé** car vous perdrez les dernières fonctionnalités et corrections de sécurité
   - Consultez la [documentation Firebase](https://firebase.google.com/docs/ios/setup) pour les versions compatibles

3. **Xcode** : Assurez-vous d'utiliser une version de Xcode qui supporte iOS 15.0 :
   - Xcode 13.0 ou plus récent

### 🐛 Problèmes courants après la mise à jour

#### Erreur : "No such module 'FirebaseCore'"
```bash
cd frontend/ios
pod deintegrate
pod install
```

#### Erreur : "Command PhaseScriptExecution failed"
```bash
cd frontend
flutter clean
flutter pub get
cd ios
pod install
```

#### Erreur : "Unable to find a specification"
```bash
cd frontend/ios
pod repo update
pod install
```

### 📚 Ressources

- [Documentation Flutter iOS](https://docs.flutter.dev/deployment/ios)
- [Documentation Firebase iOS](https://firebase.google.com/docs/ios/setup)
- [CocoaPods Guide](https://guides.cocoapods.org/)

---

**Dernière mise à jour** : Configuration mise à jour pour iOS 15.0 minimum (requis par Firebase Core 12.4.0+)
