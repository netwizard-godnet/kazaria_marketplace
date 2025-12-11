# Résolution de l'erreur CodeSign iOS

## 🔴 Erreur rencontrée

```
Failed to build iOS app
Uncategorized (Xcode): Command CodeSign failed with a nonzero exit code
Could not build the application for the simulator.
```

## 🔍 Causes possibles

L'erreur de signature de code peut avoir plusieurs causes :

1. **Problème avec les frameworks CocoaPods** : Les pods récemment installés peuvent nécessiter une configuration de signature spécifique
2. **Problème de certificat/équipe de développement** : La signature automatique peut échouer
3. **Problème avec le simulateur** : Parfois, le simulateur nécessite une configuration spécifique
4. **Cache Xcode corrompu** : Les fichiers dérivés peuvent être corrompus

## ✅ Solutions (essayer dans l'ordre)

### Solution 1 : Nettoyer et reconstruire

```bash
cd frontend

# Nettoyer Flutter
flutter clean

# Nettoyer les pods
cd ios
rm -rf Pods Podfile.lock
rm -rf ~/Library/Developer/Xcode/DerivedData/*

# Réinstaller les pods
pod install --repo-update

# Retour au répertoire frontend
cd ..

# Réinstaller les dépendances Flutter
flutter pub get

# Relancer
flutter run
```

### Solution 2 : Configurer la signature pour le simulateur

Pour le simulateur, vous pouvez désactiver la signature de code. Modifiez le `Podfile` :

```ruby
post_install do |installer|
  installer.pods_project.targets.each do |target|
    flutter_additional_ios_build_settings(target)
    
    target.build_configurations.each do |config|
      config.build_settings['IPHONEOS_DEPLOYMENT_TARGET'] = '15.0'
      
      # Désactiver la signature pour le simulateur
      config.build_settings['CODE_SIGN_IDENTITY'] = ''
      config.build_settings['CODE_SIGNING_REQUIRED'] = 'NO'
      config.build_settings['CODE_SIGNING_ALLOWED'] = 'NO'
    end
  end
end
```

Puis réinstallez les pods :

```bash
cd frontend/ios
pod install
cd ..
flutter run
```

### Solution 3 : Vérifier la configuration dans Xcode

1. **Ouvrir le projet dans Xcode** :
   ```bash
   cd frontend/ios
   open Runner.xcworkspace
   ```

2. **Vérifier les paramètres de signature** :
   - Sélectionnez le projet "Runner" dans le navigateur
   - Allez dans l'onglet "Signing & Capabilities"
   - Pour le target "Runner" :
     - ✅ Cochez "Automatically manage signing"
     - Sélectionnez votre équipe de développement
   - Pour le target "RunnerTests" :
     - ✅ Cochez "Automatically manage signing"
     - Sélectionnez la même équipe

3. **Vérifier le Bundle Identifier** :
   - Assurez-vous que `com.example.kazariaApp` est unique
   - Si nécessaire, changez-le pour quelque chose comme `com.votrenom.kazariaApp`

4. **Nettoyer le build dans Xcode** :
   - Menu Product → Clean Build Folder (⇧⌘K)
   - Fermer Xcode

5. **Relancer depuis Flutter** :
   ```bash
   flutter run
   ```

### Solution 4 : Configurer la signature manuellement dans project.pbxproj

Si la solution automatique ne fonctionne pas, vous pouvez configurer manuellement. **ATTENTION** : Ne modifiez `project.pbxproj` que si vous savez ce que vous faites.

Pour le simulateur uniquement, vous pouvez ajouter cette configuration dans le `post_install` du Podfile (voir Solution 2).

### Solution 5 : Vérifier les frameworks des pods

Parfois, certains pods nécessitent une configuration spécifique. Vérifiez les logs détaillés :

```bash
cd frontend
flutter run -v 2>&1 | grep -i "codesign\|error" | head -20
```

### Solution 6 : Réinitialiser le simulateur

Si vous testez sur le simulateur :

```bash
# Lister les simulateurs
xcrun simctl list devices

# Effacer le simulateur iPhone 16 Pro
xcrun simctl erase "iPhone 16 Pro"

# Relancer
flutter run
```

### Solution 7 : Utiliser un appareil physique

Si le problème persiste avec le simulateur, essayez avec un appareil physique :

1. Connectez votre iPhone/iPad via USB
2. Faites confiance à l'ordinateur sur l'appareil
3. Dans Xcode, sélectionnez votre appareil comme destination
4. Configurez la signature avec votre compte Apple Developer
5. Lancez depuis Flutter :
   ```bash
   flutter run
   ```

## 🔧 Configuration recommandée pour le développement

### Pour le simulateur (développement local)

Dans `frontend/ios/Podfile`, ajoutez cette configuration dans `post_install` :

```ruby
post_install do |installer|
  installer.pods_project.targets.each do |target|
    flutter_additional_ios_build_settings(target)
    
    target.build_configurations.each do |config|
      config.build_settings['IPHONEOS_DEPLOYMENT_TARGET'] = '15.0'
      
      # Pour le simulateur uniquement
      if config.name == 'Debug'
        config.build_settings['CODE_SIGN_IDENTITY'] = ''
        config.build_settings['CODE_SIGNING_REQUIRED'] = 'NO'
        config.build_settings['CODE_SIGNING_ALLOWED'] = 'NO'
      end
    end
  end
end
```

### Pour les builds de production

Pour les builds Release (production), gardez la signature automatique activée dans Xcode.

## 📋 Checklist de dépannage

- [ ] Nettoyé Flutter (`flutter clean`)
- [ ] Supprimé Pods et Podfile.lock
- [ ] Nettoyé DerivedData de Xcode
- [ ] Réinstallé les pods (`pod install`)
- [ ] Vérifié la configuration dans Xcode
- [ ] Vérifié que l'équipe de développement est sélectionnée
- [ ] Vérifié que le Bundle Identifier est unique
- [ ] Essayé avec un simulateur différent
- [ ] Essayé avec un appareil physique
- [ ] Consulté les logs détaillés (`flutter run -v`)

## 🐛 Erreurs courantes et solutions

### "No signing certificate found"

**Solution** : Configurez votre compte Apple Developer dans Xcode :
1. Xcode → Preferences → Accounts
2. Ajoutez votre compte Apple ID
3. Sélectionnez votre équipe dans le projet

### "Bundle identifier is already in use"

**Solution** : Changez le Bundle Identifier dans Xcode :
- Project → Runner → General → Bundle Identifier
- Utilisez quelque chose comme `com.votrenom.kazariaApp`

### "Provisioning profile doesn't match"

**Solution** : 
1. Dans Xcode, allez dans Signing & Capabilities
2. Décochez et recochez "Automatically manage signing"
3. Sélectionnez votre équipe

### Erreur spécifique à un pod

Si l'erreur mentionne un pod spécifique (ex: `firebase_core`, `google_sign_in`), vérifiez que :
1. La version iOS minimale est 15.0 (déjà configuré)
2. Les pods sont à jour (`pod repo update`)
3. Le pod est compatible avec votre version de Xcode

## 📚 Ressources

- [Documentation Flutter iOS](https://docs.flutter.dev/deployment/ios)
- [Xcode Code Signing Guide](https://developer.apple.com/documentation/xcode/managing-signing-assets)
- [CocoaPods Troubleshooting](https://guides.cocoapods.org/using/troubleshooting)

---

**Note** : Si aucune de ces solutions ne fonctionne, partagez les logs complets avec `flutter run -v` pour un diagnostic plus approfondi.
