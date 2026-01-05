# Diagnostic : Déconnexion persistante

## Problème

L'utilisateur est toujours déconnecté après plusieurs actualisations malgré les corrections.

## Logs ajoutés pour diagnostic

### 1. Middleware `LogAuthenticateSession`

**Fichier :** `app/Http/Middleware/LogAuthenticateSession.php`

Ce middleware log toutes les informations importantes avant et après `AuthenticateSession` :
- `user_id`
- `session_id`
- Présence de `password_hash_web`
- Correspondance du hash
- État de l'authentification après

**À vérifier dans les logs :**
```bash
tail -f storage/logs/laravel.log | grep LogAuthenticateSession
```

### 2. Middleware `EnsurePasswordHashInSession` amélioré

**Fichier :** `app/Http/Middleware/EnsurePasswordHashInSession.php`

Logs ajoutés pour :
- Quand `password_hash_web` est mis à jour
- Quand l'utilisateur est connecté mais n'a pas de mot de passe
- Quand la session contient un `user_id` mais `Auth::check()` retourne `false`

**À vérifier dans les logs :**
```bash
tail -f storage/logs/laravel.log | grep EnsurePasswordHashInSession
```

## Points à vérifier

### 1. Vérifier que le cookie est envoyé et reçu

**Dans les DevTools du navigateur :**
1. Onglet Network
2. Se connecter
3. Vérifier la requête `/api/login` :
   - Response Headers : Doit contenir `Set-Cookie: kazaria-laravel-session=...`
4. Actualiser la page
5. Vérifier la requête suivante :
   - Request Headers : Doit contenir `Cookie: kazaria-laravel-session=...`

**Si le cookie n'est pas envoyé :**
- Vérifier `config/session.php` : `same_site`, `secure`, `domain`
- Vérifier que le navigateur accepte les cookies

**Si le cookie n'est pas reçu :**
- Vérifier que le domaine correspond
- Vérifier que `same_site` n'est pas `strict`
- Vérifier que `secure` correspond à HTTP/HTTPS

### 2. Vérifier que la session persiste dans la base de données

```bash
php artisan tinker
```

```php
// Voir toutes les sessions actives
\DB::table('sessions')
    ->orderBy('last_activity', 'desc')
    ->limit(5)
    ->get()
    ->map(function($session) {
        $data = unserialize(base64_decode($session->payload));
        return [
            'id' => $session->id,
            'user_id' => $data['login_web_59ba36addc2b2f9401580f014c7f58ea4'] ?? null,
            'password_hash_web' => isset($data['password_hash_web']) ? 'PRESENT' : 'ABSENT',
            'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
        ];
    });
```

**Si la session n'existe pas :**
- Le cookie n'est pas envoyé ou reçu
- La session est supprimée trop rapidement

**Si `password_hash_web` est absent :**
- Le middleware `EnsurePasswordHashInSession` ne fonctionne pas
- `AuthenticateSession` déconnecte l'utilisateur

### 3. Vérifier les logs Laravel

**Commandes :**
```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Filtrer les logs de déconnexion
tail -f storage/logs/laravel.log | grep -i "logout\|authenticate\|password_hash"

# Filtrer les logs de nos middlewares
tail -f storage/logs/laravel.log | grep -E "LogAuthenticateSession|EnsurePasswordHashInSession"
```

**Ce qu'il faut chercher :**
- Messages de `LogAuthenticateSession` montrant que `password_hash_web` ne correspond pas
- Messages de `EnsurePasswordHashInSession` montrant que le hash est mis à jour
- Messages d'erreur de session

### 4. Vérifier l'ordre d'exécution des middlewares

**Ordre attendu pour les routes web :**
1. `EncryptCookies`
2. `AddQueuedCookiesToResponse`
3. `StartSession` ← Démarre la session et lit le cookie
4. `ShareErrorsFromSession`
5. `ValidateCsrfToken`
6. `SubstituteBindings`
7. `AuthenticateSession` (si activé) ← Vérifie password_hash_web
8. `SeoMiddleware`
9. `LandingPageMiddleware`
10. `TrackPageVisits`
11. `LogAuthenticateSession` ← Logging
12. `EnsurePasswordHashInSession` ← Garantit que password_hash_web est présent

**Problème potentiel :**
- Si `AuthenticateSession` s'exécute et déconnecte l'utilisateur AVANT que `EnsurePasswordHashInSession` ne puisse corriger le problème

**Solution :**
- `EnsurePasswordHashInSession` s'exécute APRÈS `AuthenticateSession` et met à jour `password_hash_web` si nécessaire
- Mais si `AuthenticateSession` déconnecte l'utilisateur, `EnsurePasswordHashInSession` ne peut plus le réparer

### 5. Vérifier si `AuthenticateSession` est activé

**Dans Laravel 11, `AuthenticateSession` est activé par défaut si :**
- `authenticatedSessions` est `true` dans la configuration

**Vérifier :**
```php
// Dans bootstrap/app.php, chercher :
->withAuthenticatedSessions()

// Ou vérifier dans vendor/laravel/framework/src/Illuminate/Foundation/Configuration/Middleware.php
// Ligne 492 : $this->authenticatedSessions ? 'auth.session' : null
```

**Si `AuthenticateSession` est activé :**
- Il vérifie `password_hash_web` à chaque requête
- Si le hash ne correspond pas, il déconnecte l'utilisateur
- C'est probablement la cause du problème

**Solution :**
- S'assurer que `EnsurePasswordHashInSession` met à jour `password_hash_web` AVANT que `AuthenticateSession` ne vérifie
- Mais comme `EnsurePasswordHashInSession` s'exécute APRÈS, il faut une autre approche

## Solution alternative : Désactiver temporairement AuthenticateSession

Si le problème persiste, on peut désactiver temporairement `AuthenticateSession` pour tester :

```php
// Dans bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(...)
    ->withMiddleware(function (Middleware $middleware): void {
        // Désactiver AuthenticateSession temporairement
        $middleware->web(remove: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
        // ... reste du code
    });
```

**⚠️ ATTENTION :** Cela réduit la sécurité. À utiliser uniquement pour tester.

## Solution recommandée : S'assurer que password_hash_web est toujours présent

Le problème est que `AuthenticateSession` s'exécute AVANT `EnsurePasswordHashInSession`. Il faut s'assurer que `password_hash_web` est présent AVANT que `AuthenticateSession` ne vérifie.

**Solution :** Modifier `EnsurePasswordHashInSession` pour qu'il s'exécute AVANT `AuthenticateSession` :

```php
// Dans bootstrap/app.php
$middleware->web(prepend: [
    \App\Http\Middleware\EnsurePasswordHashInSession::class, // AVANT AuthenticateSession
]);
```

Mais cela ne fonctionnera pas car `EnsurePasswordHashInSession` a besoin que la requête soit traitée pour vérifier l'utilisateur.

**Meilleure solution :** Modifier `EnsurePasswordHashInSession` pour qu'il s'exécute AVANT la requête :

```php
public function handle(Request $request, Closure $next): Response
{
    // S'assurer que password_hash_web est présent AVANT la requête
    if ($request->hasSession() && Auth::guard('web')->check()) {
        $user = Auth::guard('web')->user();
        if ($user && $user->getAuthPassword()) {
            $session = $request->session();
            $passwordHashKey = 'password_hash_web';
            
            if (!$session->has($passwordHashKey) || 
                !hash_equals($session->get($passwordHashKey), $user->getAuthPassword())) {
                $session->put($passwordHashKey, $user->getAuthPassword());
            }
        }
    }
    
    $response = $next($request);
    
    // S'assurer aussi APRÈS la requête
    // ... (code existant)
    
    return $response;
}
```

Et l'appliquer AVANT `AuthenticateSession` :

```php
$middleware->web(prepend: [
    \App\Http\Middleware\EnsurePasswordHashInSession::class,
]);
```

