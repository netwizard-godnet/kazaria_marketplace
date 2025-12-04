# 🎨 CHANGER L'ICÔNE DE L'APPLICATION KAZARIA

## 📋 Étapes pour appliquer la nouvelle icône

### 1️⃣ Installer le package (si pas déjà fait)

```bash
cd frontend
flutter pub get
```

### 2️⃣ Générer les icônes

```bash
flutter pub run flutter_launcher_icons
```

Cette commande va :
- ✅ Générer toutes les tailles d'icônes pour Android
- ✅ Générer toutes les tailles d'icônes pour iOS
- ✅ Créer les icônes adaptatives pour Android
- ✅ Remplacer les icônes par défaut

### 3️⃣ Nettoyer et reconstruire l'application

```bash
flutter clean
flutter pub get
flutter run
```

---

## 📱 Résultat

Votre application affichera maintenant l'icône KAZARIA :
- ✅ Sur l'écran d'accueil Android
- ✅ Sur l'écran d'accueil iOS
- ✅ Dans le tiroir d'applications
- ✅ Dans les paramètres système

---

## 🎨 Configuration appliquée

**Fichier source** : `assets/logo/Icone-KAZARIA.png`

**Plateformes** :
- ✅ Android (icône standard + adaptive)
- ✅ iOS (toutes les tailles)

**Fond adaptatif Android** : Blanc (#FFFFFF)

---

## 📝 Fichiers modifiés

### `pubspec.yaml`
```yaml
dev_dependencies:
  flutter_launcher_icons: ^0.13.1

flutter_launcher_icons:
  android: true
  ios: true
  image_path: "assets/logo/Icone-KAZARIA.png"
  adaptive_icon_background: "#FFFFFF"
  adaptive_icon_foreground: "assets/logo/Icone-KAZARIA.png"
  remove_alpha_ios: true
```

---

## ⚠️ Notes importantes

1. **Format de l'image** : PNG avec fond transparent ou blanc
2. **Taille recommandée** : 1024x1024 pixels minimum
3. **Icône adaptative Android** : L'icône sera automatiquement adaptée aux différentes formes (cercle, carré, squircle)
4. **iOS** : Le canal alpha sera supprimé automatiquement

---

## 🔧 Si vous voulez changer l'icône plus tard

1. Remplacez le fichier `assets/logo/Icone-KAZARIA.png`
2. Relancez : `flutter pub run flutter_launcher_icons`
3. Relancez l'application

---

## ✅ Vérification

Pour vérifier que l'icône a été changée :

### Android
- Regardez dans : `android/app/src/main/res/mipmap-*/ic_launcher.png`
- Vérifiez : `android/app/src/main/res/mipmap-anydpi-v26/ic_launcher.xml`

### iOS
- Regardez dans : `ios/Runner/Assets.xcassets/AppIcon.appiconset/`

---

**L'icône KAZARIA est maintenant configurée ! 🎉**

