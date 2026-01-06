# Guide de diagnostic : Problème de déconnexion

## 📋 Logs ajoutés pour le diagnostic

J'ai ajouté des logs détaillés dans plusieurs endroits pour identifier exactement quand et pourquoi la déconnexion se produit.

## 🔍 Middlewares de logging

### 1. `LogSessionActivity` (Nouveau)
**Fichier :** `app/Http/Middleware/LogSessionActivity.php`

**Ce qu'il log :**
- Début et fin de chaque requête
- État de la session (démarrée, ID, cookie présent)
- Changements d'ID de session pendant la requête
- État d'authentification avant et après

**Emoji dans les logs :** 📋

**Exemple de log :**
```
📋 [SESSION ACTIVITY] Début de requête
📋 [SESSION ACTIVITY] Fin de requête
🔄 [SESSION ACTIVITY] ID de session changé pendant la requête
```

### 2. `EnsurePasswordHashInSession` (Amélioré)
**Fichier :** `app/Http/Middleware/EnsurePasswordHashInSession.php`

**Ce qu'il log :**
- Mise à jour de `password_hash_web` avant la requête
- Mise à jour de `password_hash_web` après la requête
- Sauvegarde de session après mise à jour
- Erreurs lors de la sauvegarde

**Emoji dans les logs :** 🔧, ✅, 💾, ❌

**Exemple de log :**
```
🔧 [PASSWORD HASH] Mis à jour AVANT la requête
✅ [PASSWORD HASH] OK AVANT la requête
💾 [SESSION] Sauvegardée après mise à jour password_hash
```

### 3. `LogAuthenticateSession` (Amélioré)
**Fichier :** `app/Http/Middleware/LogAuthenticateSession.php`

**Ce qu'il log :**
- État AVANT `AuthenticateSession`
- État APRÈS `AuthenticateSession`
- **Déconnexions détectées** (très important !)
- Correspondance du hash de mot de passe

**Emoji dans les logs :** 🔍, ⚠️, ✅

**Exemple de log :**
```
🔍 [AUTH SESSION] AVANT AuthenticateSession
⚠️ [AUTH SESSION] DÉCONNEXION DÉTECTÉE par AuthenticateSession
✅ [AUTH SESSION] APRÈS AuthenticateSession - OK
```

### 4. `AuthController` (Amélioré)
**Fichier :** `app/Http/Controllers/AuthController.php`

**Ce qu'il log :**
- Démarrage de session lors de la connexion
- Régénération de session
- Création et envoi du cookie de session
- Sauvegarde de session après connexion

**Emoji dans les logs :** 🔐, 🔄, ✅, 🍪

**Exemple de log :**
```
🔐 [LOGIN] Session démarrée pour connexion
🔄 [LOGIN] Session régénérée après connexion
✅ [LOGIN] Connexion réussie - Session sauvegardée
🍪 [SESSION COOKIE] Cookie de session créé et envoyé
```

## 📊 Comment utiliser les logs pour diagnostiquer

### Étape 1 : Activer les logs
Les logs sont déjà activés. Vérifiez que le fichier `storage/logs/laravel.log` existe et est accessible.

### Étape 2 : Reproduire le problème
1. Connectez-vous
2. Naviguez sur plusieurs pages
3. Notez le moment exact où vous êtes déconnecté

### Étape 3 : Analyser les logs

#### A. Chercher les déconnexions
```bash
# Dans le terminal ou avec grep
grep "DÉCONNEXION DÉTECTÉE" storage/logs/laravel.log
```

**Ce que vous verrez :**
- Le moment exact de la déconnexion
- La route qui a causé la déconnexion
- L'état du `password_hash_web` avant la déconnexion

#### B. Vérifier les changements de session
```bash
grep "ID de session changé" storage/logs/laravel.log
```

**Ce que vous verrez :**
- Si l'ID de session change pendant une requête
- Cela peut indiquer une régénération non désirée

#### C. Vérifier les mises à jour de password_hash
```bash
grep "PASSWORD HASH" storage/logs/laravel.log
```

**Ce que vous verrez :**
- Quand `password_hash_web` est mis à jour
- Si le hash correspond avant et après

#### D. Suivre une session spécifique
```bash
# Remplacez SESSION_ID par l'ID de session (premiers caractères)
grep "SESSION_ID" storage/logs/laravel.log | grep "SESSION_ID"
```

## 🔎 Scénarios de diagnostic

### Scénario 1 : Déconnexion après quelques minutes
**Cherchez dans les logs :**
```
⚠️ [AUTH SESSION] DÉCONNEXION DÉTECTÉE
```

**Vérifiez :**
- `password_hash_matched: false` → Le hash ne correspondait pas
- `had_password_hash: false` → Le hash était absent
- La route qui a causé la déconnexion

**Cause probable :**
- `password_hash_web` perdu ou modifié entre deux requêtes
- Session non sauvegardée correctement

### Scénario 2 : Déconnexion lors de la navigation
**Cherchez dans les logs :**
```
🔄 [SESSION ACTIVITY] ID de session changé
```

**Vérifiez :**
- Si l'ID de session change sans raison
- Si cela se produit sur certaines routes spécifiques

**Cause probable :**
- Régénération de session non désirée
- Middleware qui crée une nouvelle session

### Scénario 3 : Déconnexion après une action spécifique
**Cherchez dans les logs :**
- La dernière action avant la déconnexion
- Les logs de cette route spécifique

**Cause probable :**
- Un middleware ou contrôleur qui modifie la session
- Une régénération de session sans mise à jour de `password_hash_web`

## 📝 Format des logs

Tous les logs incluent :
- **Emoji** pour identification rapide
- **Catégorie** entre crochets `[CATEGORIE]`
- **Timestamp** pour ordre chronologique
- **Informations détaillées** (user_id, session_id, route, etc.)

## 🎯 Points clés à surveiller

1. **Déconnexions détectées** : `⚠️ [AUTH SESSION] DÉCONNEXION DÉTECTÉE`
2. **Changements de session** : `🔄 [SESSION ACTIVITY] ID de session changé`
3. **Mises à jour de hash** : `🔧 [PASSWORD HASH] Mis à jour`
4. **Erreurs de sauvegarde** : `❌ [SESSION] Erreur lors de la sauvegarde`

## 🚀 Commandes utiles

### Voir les dernières déconnexions
```bash
tail -n 100 storage/logs/laravel.log | grep "DÉCONNEXION"
```

### Suivre les logs en temps réel
```bash
tail -f storage/logs/laravel.log | grep -E "SESSION|AUTH|PASSWORD"
```

### Compter les déconnexions
```bash
grep -c "DÉCONNEXION DÉTECTÉE" storage/logs/laravel.log
```

### Voir toutes les activités d'une session spécifique
```bash
# Remplacez abc123 par les premiers caractères de votre session_id
grep "abc123" storage/logs/laravel.log
```

## 📌 Prochaines étapes

1. **Reproduire le problème** avec les logs activés
2. **Analyser les logs** pour identifier le moment exact de la déconnexion
3. **Identifier la cause** en regardant les logs avant la déconnexion
4. **Corriger le problème** basé sur les informations des logs

## ⚠️ Note importante

Ces logs sont **temporaires** et doivent être **désactivés en production** pour des raisons de performance et de sécurité. Une fois le problème résolu, vous pouvez :

1. Supprimer ou désactiver `LogSessionActivity`
2. Réduire le niveau de logging dans `LogAuthenticateSession`
3. Garder seulement les logs d'erreur dans `EnsurePasswordHashInSession`

