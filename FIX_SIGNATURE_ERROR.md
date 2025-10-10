# 🔧 Correction Erreur 403 Invalid Signature

## 🔍 Pourquoi Cette Erreur ?

L'erreur "403 Invalid signature" arrive quand :
1. **L'APP_URL dans .env ne correspond pas à l'URL de votre navigateur**
2. Le lien a été mal copié (tronqué)
3. Le lien a expiré (60 minutes)
4. L'APP_KEY a changé depuis la génération du lien

**Le problème #1 est le plus fréquent !**

---

## ✅ Solution 1 : Corriger APP_URL (RECOMMANDÉ)

### Étape 1 : Identifier Votre URL

**Regardez dans votre navigateur quand vous êtes sur votre site :**

```
http://kazaria-laravel-v0.test        ← Avec Laragon (domaine .test)
http://localhost                       ← Sans port
http://localhost:8000                  ← Avec serveur PHP (php artisan serve)
http://127.0.0.1                       ← Adresse IP
```

**Copiez EXACTEMENT ce que vous voyez dans la barre d'adresse !**

### Étape 2 : Modifier .env

Ouvrez votre fichier `.env` et trouvez :

```env
APP_URL=http://localhost
```

**Changez-le pour correspondre à votre URL :**

#### Avec Laragon (domaine .test) :
```env
APP_URL=http://kazaria-laravel-v0.test
```

#### Avec localhost sans port :
```env
APP_URL=http://localhost
```

#### Avec serveur PHP (php artisan serve) :
```env
APP_URL=http://localhost:8000
```

### Étape 3 : Nettoyer le Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Étape 4 : Vérifier

```bash
php artisan tinker
```

Puis :
```php
config('app.url')
// Doit afficher la même URL que dans votre navigateur
exit
```

### Étape 5 : Recommencer l'Inscription

1. Inscrivez-vous à nouveau (avec un nouvel email)
2. Exécutez `php get-verification-link.php`
3. Le lien généré contiendra maintenant la bonne URL
4. Copiez et collez le lien
5. ✅ Ça devrait fonctionner !

---

## ✅ Solution 2 : Modifier le Lien Manuellement

Si le lien dans le log contient :
```
http://localhost/email/verify/1/abc123?expires=...&signature=...
```

Mais que vous accédez au site via :
```
http://kazaria-laravel-v0.test
```

**Remplacez manuellement :**
```
http://kazaria-laravel-v0.test/email/verify/1/abc123?expires=...&signature=...
```

⚠️ **Attention : Cela ne fonctionnera PAS car la signature est calculée avec l'URL de base.**

**Vous DEVEZ corriger APP_URL dans .env !**

---

## ✅ Solution 3 : Désactiver Temporairement la Vérification (TESTS UNIQUEMENT)

**⚠️ UNIQUEMENT pour le développement, JAMAIS en production !**

### Modifier routes/web.php

Trouvez cette ligne (ligne 53-55) :
```php
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
```

Changez en :
```php
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['throttle:6,1'])  // Retiré 'signed'
    ->name('verification.verify');
```

**Nettoyez le cache :**
```bash
php artisan route:clear
```

**Maintenant les liens fonctionneront même avec une mauvaise signature.**

**⚠️ N'OUBLIEZ PAS de remettre 'signed' avant de déployer en production !**

---

## 🧪 Tester Que C'est Corrigé

### Test 1 : Vérifier APP_URL

```bash
php artisan tinker
config('app.url')
exit
```

La valeur affichée doit être **EXACTEMENT** la même que dans votre navigateur.

### Test 2 : Inscription Complète

1. Allez sur votre site (notez l'URL exacte)
2. Inscrivez-vous avec un nouvel email
3. Exécutez : `php get-verification-link.php`
4. Vérifiez que le lien commence par la même URL
5. Copiez le lien complet
6. Collez dans votre navigateur
7. ✅ Devrait fonctionner !

---

## 🔍 Diagnostic Détaillé

### Vérifier l'URL du Lien Généré

Après inscription, ouvrez `storage/logs/laravel.log` et cherchez le lien.

**Si le lien contient :**
```
http://localhost/email/verify/...
```

**Mais que vous utilisez :**
```
http://kazaria-laravel-v0.test
```

**→ PROBLÈME ! Changez APP_URL dans .env**

### Vérifier que le Lien N'est Pas Expiré

Les liens expirent après **60 minutes**.

Si vous avez généré le lien il y a plus d'une heure :
1. Faites une nouvelle inscription
2. Utilisez le nouveau lien immédiatement

### Vérifier que APP_KEY N'a Pas Changé

Si vous avez exécuté `php artisan key:generate` après avoir généré le lien :
- ✅ Les nouveaux liens fonctionneront
- ❌ Les anciens liens sont invalides

**Solution :** Générez un nouveau lien (nouvelle inscription)

---

## 📝 Configuration .env Complète

Voici un exemple de configuration complète pour Laragon :

```env
APP_NAME=KAZARIA
APP_ENV=local
APP_KEY=base64:VotreCléGénérée...
APP_DEBUG=true
APP_URL=http://kazaria-laravel-v0.test

# Email (mode log pour développement)
MAIL_MAILER=log

# OU avec Mailtrap
# MAIL_MAILER=smtp
# MAIL_HOST=sandbox.smtp.mailtrap.io
# MAIL_PORT=2525
# MAIL_USERNAME=votre_username
# MAIL_PASSWORD=votre_password
# MAIL_ENCRYPTION=tls
# MAIL_FROM_ADDRESS=noreply@kazaria.com
# MAIL_FROM_NAME="${APP_NAME}"

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kazaria_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## ⚡ Solution Ultra-Rapide

**Si vous voulez juste que ça marche MAINTENANT :**

1. **Ouvrez .env**
2. **Trouvez APP_URL**
3. **Regardez l'URL dans votre navigateur**
4. **Mettez la MÊME chose dans APP_URL**
5. **Sauvegardez**
6. **Exécutez :** `php artisan config:clear`
7. **Réinscrivez-vous** (nouvel email)
8. **Récupérez le lien :** `php get-verification-link.php`
9. **Copiez-collez le lien**
10. ✅ **Ça marche !**

---

## 🎯 Quelle URL Utiliser Selon Votre Configuration ?

### Avec Laragon (Votre Cas)

Laragon crée automatiquement un domaine `.test` :

```env
APP_URL=http://kazaria-laravel-v0.test
```

**Pour le trouver :**
1. Cliquez droit sur l'icône Laragon
2. Cliquez sur "www" → "kazaria laravel v0"
3. Le navigateur s'ouvre avec l'URL
4. C'est cette URL qu'il faut mettre dans APP_URL

### Avec PHP Artisan Serve

Si vous lancez `php artisan serve` :

```env
APP_URL=http://localhost:8000
```

Ou si un autre port :
```env
APP_URL=http://localhost:XXXX
```

### Avec Apache/Nginx Direct

```env
APP_URL=http://localhost
```

---

## ✅ Checklist de Vérification

Avant de tester :
- [ ] APP_URL dans .env correspond à l'URL du navigateur
- [ ] Cache nettoyé (`php artisan config:clear`)
- [ ] Nouvelle inscription (nouvel email)
- [ ] Lien récupéré avec `php get-verification-link.php`
- [ ] Lien copié ENTIÈREMENT (y compris signature)
- [ ] Lien utilisé dans les 60 minutes

---

## 🎉 Une Fois Corrigé

Après avoir corrigé APP_URL, **tout fonctionnera** :
- ✅ Vérification d'email
- ✅ Code à 8 chiffres pour la connexion
- ✅ Réinitialisation de mot de passe
- ✅ Tous les liens signés

**C'est un problème à résoudre une seule fois !**

---

Besoin d'aide pour trouver votre URL Laragon ? Demandez-moi !

