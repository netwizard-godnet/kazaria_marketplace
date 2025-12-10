# Authentification Sociale Mobile - Facebook et Google

## ✅ Implémentation Complète

L'authentification sociale (Facebook et Google) a été implémentée pour l'application mobile Flutter.

## 📁 Fichiers Créés/Modifiés

### Backend (Laravel)

1. **`routes/api.php`** :
   - Ajout de la route `/api/auth/social/{provider}` pour l'authentification mobile

2. **`app/Http/Controllers/Auth/SocialAuthController.php`** :
   - Ajout de la méthode `mobileAuth()` qui accepte les tokens sociaux et retourne un token Sanctum JSON
   - Gère la création/mise à jour des utilisateurs avec les données sociales
   - Télécharge automatiquement l'avatar depuis le provider

### Frontend (Flutter)

1. **`frontend/lib/services/social_auth_service.dart`** (NOUVEAU) :
   - Service pour gérer l'authentification Google et Facebook
   - Méthodes `signInWithGoogle()` et `signInWithFacebook()`
   - Gère les tokens et les données utilisateur

2. **`frontend/lib/providers/auth_provider.dart`** :
   - Ajout des méthodes `signInWithGoogle()` et `signInWithFacebook()`
   - Intégration avec le `SocialAuthService`

3. **`frontend/lib/config/api_config.dart`** :
   - Ajout de `socialAuth(String provider)` pour construire l'URL de l'endpoint

4. **`frontend/lib/screens/auth/login_screen.dart`** :
   - Connexion des boutons Google et Facebook aux méthodes d'authentification
   - Affichage des indicateurs de chargement

5. **`frontend/lib/screens/auth/register_screen.dart`** :
   - Connexion des boutons Google et Facebook aux méthodes d'authentification
   - Affichage des indicateurs de chargement

## 🔧 Configuration Requise

### Variables d'environnement (.env)

Les tokens suivants doivent être configurés dans votre fichier `.env` :

```env
GOOGLE_CLIENT_ID=votre_google_client_id
GOOGLE_CLIENT_SECRET=votre_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

FACEBOOK_CLIENT_ID=votre_facebook_app_id
FACEBOOK_CLIENT_SECRET=votre_facebook_app_secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback
```

### Configuration Android

Pour Google Sign-In sur Android, vous devez :

1. **Ajouter le SHA-1 de votre clé de signature** dans la console Google Cloud :
   ```bash
   keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android
   ```

2. **Configurer le fichier `google-services.json`** (si vous utilisez Firebase) :
   - Téléchargez-le depuis Firebase Console
   - Placez-le dans `frontend/android/app/`

3. **Vérifier `AndroidManifest.xml`** :
   - Les permissions Internet sont déjà configurées ✅

### Configuration iOS

Pour iOS, vous devez configurer :

1. **Google Sign-In** :
   - Ajouter l'URL scheme dans `Info.plist` :
   ```xml
   <key>CFBundleURLTypes</key>
   <array>
     <dict>
       <key>CFBundleTypeRole</key>
       <string>Editor</string>
       <key>CFBundleURLSchemes</key>
       <array>
         <string>com.googleusercontent.apps.VOTRE_CLIENT_ID</string>
       </array>
     </dict>
   </array>
   ```

2. **Facebook** :
   - Ajouter dans `Info.plist` :
   ```xml
   <key>CFBundleURLTypes</key>
   <array>
     <dict>
       <key>CFBundleURLSchemes</key>
       <array>
         <string>fbVOTRE_FACEBOOK_APP_ID</string>
       </array>
     </dict>
   </array>
   <key>FacebookAppID</key>
   <string>VOTRE_FACEBOOK_APP_ID</string>
   <key>FacebookDisplayName</key>
   <string>Kazaria Marketplace</string>
   ```

## 🚀 Utilisation

### Pour l'utilisateur

1. Sur l'écran de connexion ou d'inscription
2. Cliquer sur "Continuer avec Google" ou "Continuer avec Facebook"
3. Autoriser l'application à accéder aux informations
4. L'utilisateur est automatiquement connecté et redirigé vers l'accueil

### Flux Technique

1. **Flutter** : L'utilisateur clique sur le bouton social
2. **SDK Social** : Ouvre le dialogue d'authentification (Google/Facebook)
3. **SDK Social** : Retourne les données utilisateur (email, nom, avatar, token)
4. **Flutter** : Envoie les données au backend via `/api/auth/social/{provider}`
5. **Backend** : Vérifie/crée l'utilisateur et génère un token Sanctum
6. **Backend** : Retourne le token et les données utilisateur
7. **Flutter** : Sauvegarde le token et connecte l'utilisateur

## 📦 Packages Utilisés

Les packages suivants sont déjà dans `pubspec.yaml` :

- `google_sign_in: ^6.2.1` ✅
- `flutter_facebook_auth: ^7.0.1` ✅

## 🔍 Points Importants

1. **Création automatique de compte** : Si l'utilisateur n'existe pas, un compte est créé automatiquement
2. **Liaison de compte** : Si l'email existe déjà avec un autre provider, le compte est lié
3. **Avatar** : L'avatar est téléchargé automatiquement depuis le provider
4. **Token Sanctum** : Un token d'authentification est généré pour l'app mobile
5. **Pas de code de vérification** : L'authentification sociale bypass le système de code de vérification

## ⚠️ Notes

- Les tokens Facebook et Google doivent être valides dans le `.env`
- Pour la production, configurez les URLs de callback correctes
- Testez sur un appareil réel pour Google Sign-In (l'émulateur peut avoir des problèmes)
- Facebook nécessite une configuration dans le Facebook Developer Console

## 🐛 Dépannage

Si l'authentification ne fonctionne pas :

1. Vérifiez les logs dans la console Flutter
2. Vérifiez que les tokens sont corrects dans `.env`
3. Vérifiez les configurations Android/iOS
4. Vérifiez que les SHA-1/SHA-256 sont configurés dans les consoles développeur
