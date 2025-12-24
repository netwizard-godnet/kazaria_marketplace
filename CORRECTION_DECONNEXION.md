# Correction : Déconnexion lors de la navigation

## Problème identifié

**L'utilisateur est déconnecté lors de la navigation ou après plusieurs actualisations.**

## Cause racine

Le middleware `AuthenticateSession` de Laravel vérifie que `password_hash_web` dans la session correspond au hash du mot de passe de l'utilisateur. Si ça ne correspond pas, il déconnecte automatiquement l'utilisateur.

**Problèmes possibles :**
1. `password_hash_web` n'est pas stocké correctement après chaque requête
2. `password_hash_web` est perdu lors de certaines opérations de session
3. Le middleware `AuthenticateSession` n'est pas appliqué correctement
4. La session n'est pas sauvegardée correctement

## Solution appliquée

### 1. Création d'un middleware `EnsurePasswordHashInSession`

**Fichier : `app/Http/Middleware/EnsurePasswordHashInSession.php`**

Ce middleware s'assure que `password_hash_web` est **toujours présent** dans la session après chaque requête si l'utilisateur est connecté.

**Fonctionnement :**
- S'exécute **après** le traitement de la requête
- Vérifie si l'utilisateur est connecté
- Vérifie si `password_hash_web` existe dans la session
- Si absent ou incorrect, le met à jour avec le hash actuel du mot de passe

**Code :**
```php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);

    // Si l'utilisateur est connecté et que la session existe
    if ($request->hasSession() && Auth::guard('web')->check()) {
        $user = Auth::guard('web')->user();
        
        // S'assurer que password_hash_web est présent dans la session
        if ($user && $user->getAuthPassword()) {
            $session = $request->session();
            $passwordHashKey = 'password_hash_web';
            
            // Si password_hash_web n'existe pas ou ne correspond pas, le mettre à jour
            if (!$session->has($passwordHashKey) || 
                !hash_equals($session->get($passwordHashKey), $user->getAuthPassword())) {
                $session->put($passwordHashKey, $user->getAuthPassword());
            }
        }
    }

    return $response;
}
```

### 2. Application du middleware globalement

**Fichier : `bootstrap/app.php`**

Le middleware est ajouté au groupe `web` pour être appliqué sur toutes les routes web :

```php
$middleware->web(append: [
    \App\Http\Middleware\SeoMiddleware::class,
    \App\Http\Middleware\LandingPageMiddleware::class,
    \App\Http\Middleware\TrackPageVisits::class,
    \App\Http\Middleware\EnsurePasswordHashInSession::class, // ✅ Ajouté
]);
```

## Ordre d'exécution des middlewares

Pour les routes web, l'ordre est :
1. `EncryptCookies`
2. `AddQueuedCookiesToResponse`
3. `StartSession` ← Démarre la session
4. `ShareErrorsFromSession`
5. `ValidateCsrfToken`
6. `SubstituteBindings`
7. `AuthenticateSession` (si activé) ← Vérifie password_hash_web
8. `SeoMiddleware`
9. `LandingPageMiddleware`
10. `TrackPageVisits`
11. `EnsurePasswordHashInSession` ← ✅ Garantit que password_hash_web est présent

## Comment ça fonctionne

### Scénario 1 : Connexion normale

1. Utilisateur se connecte via `/api/login`
2. `Auth::login()` connecte l'utilisateur
3. `password_hash_web` est stocké dans la session
4. Cookie de session est envoyé au navigateur
5. Lors des requêtes suivantes :
   - `StartSession` démarre la session
   - `AuthenticateSession` vérifie `password_hash_web`
   - `EnsurePasswordHashInSession` s'assure qu'il est présent ✅

### Scénario 2 : Navigation après connexion

1. Utilisateur navigue sur le site
2. À chaque requête :
   - `StartSession` charge la session depuis le cookie
   - `AuthenticateSession` vérifie `password_hash_web`
   - Si absent ou incorrect, `EnsurePasswordHashInSession` le remet ✅
3. L'utilisateur reste connecté

### Scénario 3 : Actualisation de page

1. Utilisateur actualise la page (F5)
2. La session est rechargée depuis le cookie
3. `EnsurePasswordHashInSession` vérifie et met à jour `password_hash_web` si nécessaire ✅
4. L'utilisateur reste connecté

## Vérification

### Test 1 : Vérifier que password_hash_web est présent

**Dans les DevTools du navigateur :**
1. Ouvrir l'onglet Application > Cookies
2. Vérifier que le cookie de session est présent
3. Dans la console PHP (tinker) :
```php
$session = \DB::table('sessions')
    ->orderBy('last_activity', 'desc')
    ->first();
$data = unserialize(base64_decode($session->payload));
dd($data['password_hash_web']); // Doit retourner le hash
```

### Test 2 : Navigation après connexion

1. Se connecter
2. Naviguer sur plusieurs pages
3. Actualiser plusieurs fois (F5)
4. Vérifier que l'utilisateur reste connecté

### Test 3 : Vérifier les logs

Si le problème persiste, vérifier les logs Laravel :
```bash
tail -f storage/logs/laravel.log
```

Chercher les messages de déconnexion ou d'erreur de session.

## Problèmes potentiels restants

### Problème 1 : Le cookie de session expire

**Cause :** `SESSION_LIFETIME` est trop court

**Solution :** Vérifier `config/session.php` :
```php
'lifetime' => (int) env('SESSION_LIFETIME', 120), // 120 minutes
```

### Problème 2 : Le cookie n'est pas envoyé

**Cause :** Configuration `same_site` ou `secure` incorrecte

**Solution :** Vérifier `config/session.php` :
```php
'same_site' => 'lax', // ✅ OK
'secure' => env('SESSION_SECURE_COOKIE', null), // ✅ Auto-détection
```

### Problème 3 : La session est supprimée par le navigateur

**Cause :** Cookies tiers bloqués ou mode navigation privée

**Solution :** Vérifier les paramètres du navigateur

## Commandes utiles

### Vider le cache
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Vérifier les sessions dans la base de données
```bash
php artisan tinker
```

```php
// Voir toutes les sessions actives
\DB::table('sessions')
    ->orderBy('last_activity', 'desc')
    ->get()
    ->map(function($session) {
        $data = unserialize(base64_decode($session->payload));
        return [
            'id' => $session->id,
            'user_id' => $data['login_web_59ba36addc2b2f9401580f014c7f58ea4'] ?? null,
            'password_hash' => isset($data['password_hash_web']),
            'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
        ];
    });
```

## Résumé

✅ **Middleware créé** : `EnsurePasswordHashInSession`
✅ **Middleware appliqué** : Ajouté au groupe `web` dans `bootstrap/app.php`
✅ **Fonctionnement** : Garantit que `password_hash_web` est toujours présent après chaque requête
✅ **Résultat attendu** : L'utilisateur reste connecté lors de la navigation et des actualisations

