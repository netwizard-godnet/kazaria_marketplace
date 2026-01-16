# Différence entre OAuth Web et Mobile

## ✅ Sur le Web (Fonctionne)

**Configuration :**
- Utilise **Laravel Socialite** (package Laravel)
- Utilise directement l'**API OAuth de Google** (pas Firebase)
- Configuration dans `.env` :
  ```
  GOOGLE_CLIENT_ID=votre_client_id
  GOOGLE_CLIENT_SECRET=votre_client_secret
  GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
  ```
- Routes web : `/auth/google/redirect` et `/auth/google/callback`
- **Pas besoin de Firebase** ✅

**Flux :**
1. Utilisateur clique sur "Connexion Google"
2. Redirection vers Google OAuth
3. Google redirige vers `/auth/google/callback`
4. Laravel Socialite récupère les données utilisateur
5. Connexion/création de l'utilisateur

## ❌ Sur Mobile (Problème actuel)

**Configuration :**
- Utilise le package Flutter **`google_sign_in`**
- **Nécessite Firebase** pour Android (via `google-services.json`)
- Le package `google_sign_in` utilise Firebase en interne pour Android
- **Nécessite le SHA-1** configuré dans Firebase Console
- Même backend (`/api/auth/social/google`) mais tokens différents

**Pourquoi Firebase est nécessaire sur mobile ?**
- Sur Android, `google_sign_in` utilise Google Play Services
- Google Play Services nécessite Firebase pour valider l'application
- Le SHA-1 sert à vérifier que l'app est authentique
- Sans SHA-1 configuré → Erreur `ApiException: 10`

## 🔄 Solution : Deux Approches Possibles

### Option 1 : Utiliser Firebase (Recommandé pour mobile)
- ✅ Compatible avec l'approche actuelle
- ✅ Meilleure sécurité
- ✅ Support natif Android/iOS
- ⚠️ Nécessite configuration SHA-1

### Option 2 : Utiliser l'API OAuth directement (Sans Firebase)
- Modifier le code Flutter pour utiliser l'API OAuth directement
- Utiliser un WebView pour le flux OAuth
- Plus complexe à implémenter
- Moins sécurisé sur mobile

## 📝 Conclusion

**Oui, le développeur a raison** : sur le web, c'est bien l'API OAuth de Google directement (sans Firebase) via Laravel Socialite.

**Mais sur mobile**, même si le backend utilise les mêmes credentials OAuth, le package Flutter `google_sign_in` nécessite Firebase pour Android, d'où l'erreur `ApiException: 10` quand le SHA-1 n'est pas configuré.

**Solution** : Configurer le SHA-1 dans Firebase Console (voir `CONFIGURATION_GOOGLE_SIGNIN.md`)
