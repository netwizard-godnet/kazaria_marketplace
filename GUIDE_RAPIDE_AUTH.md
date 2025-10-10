# 🚀 Guide Rapide - Installation du Système d'Authentification

## ⚡ Installation en 5 étapes

### 1️⃣ Exécuter les migrations
```bash
php artisan migrate
```

### 2️⃣ Configurer l'email dans `.env`
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.votre-service.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@exemple.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kazaria.com
MAIL_FROM_NAME="KAZARIA"
```

💡 **Pour les tests** : Utilisez [Mailtrap](https://mailtrap.io) (gratuit)

### 3️⃣ Nettoyer le cache
```bash
php artisan config:clear
php artisan cache:clear
```

### 4️⃣ Tester l'envoi d'email
```bash
php artisan tinker
```
Puis :
```php
Mail::raw('Test KAZARIA', function($m) { 
    $m->to('votre-email@test.com')->subject('Test'); 
});
exit
```

### 5️⃣ Accéder aux pages
- **Inscription** : http://localhost:8000/register
- **Connexion** : http://localhost:8000/login
- **Mot de passe oublié** : http://localhost:8000/forgot-password

---

## 📋 Fonctionnalités Disponibles

✅ **Inscription** avec vérification d'email  
✅ **Connexion** avec code à 8 chiffres par email  
✅ **Réinitialisation** de mot de passe  
✅ **Validation** des emails  
✅ **Messages** en français  

---

## 🔍 Test Rapide

### Créer un compte test :
1. Aller sur `/register`
2. Remplir le formulaire
3. Vérifier l'email reçu (ou Mailtrap)
4. Cliquer sur le lien de vérification

### Se connecter :
1. Aller sur `/login`
2. Entrer email et mot de passe
3. Récupérer le code à 8 chiffres par email
4. Entrer le code
5. ✅ Connecté !

---

## 🐛 Problèmes Courants

### Emails non reçus ?
- Vérifier le dossier spam
- Vérifier la configuration dans `.env`
- Regarder les logs : `storage/logs/laravel.log`
- Si Mailtrap : vérifier le dashboard Mailtrap

### Erreur 419 (CSRF) ?
```bash
php artisan config:clear
php artisan cache:clear
```

### Code expiré ?
- Code valide 15 minutes
- Cliquer sur "Renvoyer le code"

---

## 📖 Documentation Complète

Pour plus de détails, consultez **README_AUTH.md**

---

## ⚙️ Configuration Recommandée (Production)

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

Utilisez un vrai service SMTP : SendGrid, Mailgun, Amazon SES, etc.

---

## 🎯 URLs Importantes

| Page | URL | Accès |
|------|-----|-------|
| Inscription | `/register` | Public |
| Connexion | `/login` | Public |
| Vérif. code | `/verify-code` | Après login |
| Mot de passe oublié | `/forgot-password` | Public |
| Profil | `/mon-profil` | Authentifié |

---

**Besoin d'aide ?** Consultez README_AUTH.md pour la documentation complète !

