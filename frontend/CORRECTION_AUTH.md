# 🔧 Correction du Problème d'Authentification Mobile

## Problème Identifié

L'erreur `Session store not set on request` se produisait lors de la vérification du code de connexion depuis l'application mobile Flutter.

### Cause
La méthode `verifyLoginCode` dans `AuthController.php` essayait d'utiliser des sessions Laravel pour les requêtes API mobiles, alors que les applications mobiles doivent utiliser des tokens Sanctum.

## Solution Implémentée

### 1. Modification de `AuthController::verifyLoginCode`

La méthode détecte maintenant automatiquement si la requête provient d'une application mobile et utilise des tokens Sanctum au lieu de sessions.

**Détection des requêtes API mobiles :**
- Vérifie si la route commence par `/api/*`
- Vérifie le header `Accept: application/json`
- Vérifie le header `User-Agent` pour détecter Flutter/Dart/okhttp

**Code modifié :**
```php
// Détecter si c'est une requête API mobile
$isApiRequest = $request->is('api/*') 
    || $request->routeIs('api.*') 
    || $request->expectsJson()
    || ($request->hasHeader('Accept') && str_contains($request->header('Accept'), 'application/json'))
    || ($request->hasHeader('User-Agent') && (
        str_contains($request->header('User-Agent'), 'Dart') || 
        str_contains($request->header('User-Agent'), 'Flutter') ||
        str_contains($request->header('User-Agent'), 'okhttp')
    ));

// Pour les applications mobiles : toujours créer un token Sanctum
if ($isApiRequest) {
    $token = $user->createToken('mobile-app')->plainTextToken;
    
    return response()->json([
        'success' => true,
        'message' => 'Connexion réussie',
        'token' => $token,
        'token_type' => 'Bearer',
        'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'is_verified'])
    ]);
}
```

## Fichiers Modifiés

1. **`app/Http/Controllers/AuthController.php`**
   - Méthode `verifyLoginCode()` modifiée pour détecter les requêtes API
   - Création de token Sanctum pour les requêtes mobiles
   - Fallback sur sessions pour les requêtes web

## Flux d'Authentification Mobile

1. **Login** : L'utilisateur entre son email/password
   - Backend génère un code de 8 chiffres
   - Code envoyé par email
   - Retourne `success: true, requires_code: true`

2. **Verify Code** : L'utilisateur entre le code
   - Backend vérifie le code
   - Si valide, crée un token Sanctum
   - Retourne `success: true, token: "...", user: {...}`
   - Flutter sauvegarde le token dans SharedPreferences

3. **Requêtes suivantes** : Flutter inclut le token dans le header
   - `Authorization: Bearer {token}`
   - Backend authentifie l'utilisateur via Sanctum

## Vérification

Pour vérifier que tout fonctionne :

1. **Dans Flutter** : Les logs devraient afficher :
   ```
   ✅ [AUTH_PROVIDER] Token reçu et sauvegardé
   ✅ [AUTH_PROVIDER] User sauvegardé
   ```

2. **Dans Laravel** : Vérifier les logs pour voir :
   - La détection de la requête API
   - La création du token
   - Pas d'erreur de session

## Test

Pour tester manuellement :

```bash
# 1. Faire un login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 2. Vérifier le code (remplacer CODE par le code reçu)
curl -X POST http://localhost:8000/api/verify-login-code \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "User-Agent: Flutter/1.0.0" \
  -d '{"email":"test@example.com","code":"12345678"}'

# Devrait retourner :
# {
#   "success": true,
#   "token": "...",
#   "token_type": "Bearer",
#   "user": {...}
# }
```

## Notes Importantes

- Les requêtes web continuent d'utiliser les sessions Laravel
- Les requêtes API mobiles utilisent uniquement des tokens Sanctum
- Le token est valide jusqu'à expiration (configurable dans `config/sanctum.php`)
- Le token est stocké localement dans l'application Flutter

## Prochaines Étapes

- [ ] Tester la connexion depuis l'app Flutter
- [ ] Vérifier que le token est bien sauvegardé
- [ ] Tester les requêtes authentifiées avec le token
- [ ] Configurer l'expiration des tokens si nécessaire

