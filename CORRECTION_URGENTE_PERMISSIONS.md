# 🚨 CORRECTION URGENTE - Erreur de Permissions

## ❌ Erreur Rencontrée

```
ErrorException
HTTP 500 Internal Server Error
file_put_contents(/home/kazaria_dev/web/kazaria-ci.com/public_html/storage/framework/views/587cbea8cbdb3f585a578b91f2cde9c1.php): Failed to open stream: Permission denied
```

## 🎯 Cause

Le serveur web n'a pas les droits d'écriture dans le dossier `storage/framework/views` (et probablement d'autres dossiers storage également).

---

## ✅ SOLUTION RAPIDE (5 minutes)

### **Méthode 1 : Script Automatique** ⚡ (RECOMMANDÉ)

Connectez-vous à votre serveur via SSH et exécutez :

```bash
# 1. Aller dans le dossier du projet
cd /home/kazaria_dev/web/kazaria-ci.com/public_html

# 2. Rendre le script exécutable
chmod +x fix-permissions.sh

# 3. Exécuter le script
bash fix-permissions.sh

# OU si vous avez les droits sudo
sudo bash fix-permissions.sh
```

Le script va automatiquement :
- ✅ Créer tous les dossiers manquants
- ✅ Ajuster toutes les permissions
- ✅ Configurer le propriétaire correct
- ✅ Nettoyer le cache
- ✅ Vérifier que tout fonctionne

---

### **Méthode 2 : Commande Artisan** 🔧

```bash
# Se connecter au serveur via SSH
cd /home/kazaria_dev/web/kazaria-ci.com/public_html

# Exécuter la commande de correction
php artisan permissions:fix

# Puis ajuster le propriétaire (nécessite sudo)
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

### **Méthode 3 : Commandes Manuelles** 🔨

Si les méthodes ci-dessus ne fonctionnent pas :

```bash
# 1. Se connecter au serveur
cd /home/kazaria_dev/web/kazaria-ci.com/public_html

# 2. Créer les dossiers manquants
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p storage/app/public/stores/{logos,banners}
mkdir -p storage/app/public/{products,profiles}
mkdir -p bootstrap/cache

# 3. Ajuster les permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 4. Ajuster le propriétaire (remplacez USERNAME par votre utilisateur)
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache

# OU si vous n'êtes pas sur Apache/Nginx standard
sudo chown -R kazaria_dev:kazaria_dev storage
sudo chown -R kazaria_dev:kazaria_dev bootstrap/cache

# 5. Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 6. Créer le lien symbolique
php artisan storage:link
```

---

## 🔍 Vérification

Après avoir appliqué l'une des solutions, vérifiez :

```bash
# 1. Vérifier les permissions
ls -la storage/framework/views
# Devrait afficher : drwxrwxr-x (775)

# 2. Vérifier le propriétaire
ls -la storage
# Devrait afficher : www-data www-data (ou votre utilisateur web)

# 3. Tester l'écriture
php artisan permissions:fix

# 4. Vérifier les logs
tail -f storage/logs/laravel.log
```

---

## 📋 Détection de l'Utilisateur Web

Pour trouver le bon utilisateur web sur votre serveur :

```bash
# Méthode 1 : Voir les processus web
ps aux | grep -E '(apache|httpd|nginx|php-fpm)' | grep -v root | head -5

# Méthode 2 : Vérifier la configuration Apache
cat /etc/apache2/envvars | grep APACHE_RUN_USER

# Méthode 3 : Vérifier la configuration Nginx
cat /etc/nginx/nginx.conf | grep user

# Méthode 4 : Via PHP
php -r "echo exec('whoami');"
```

Les utilisateurs web communs sont :
- **Ubuntu/Debian** : `www-data`
- **CentOS/RHEL** : `apache` ou `nginx`
- **cPanel** : Votre nom d'utilisateur (ex: `kazaria_dev`)

---

## ⚠️ Pour cPanel / Hébergements Partagés

Si vous êtes sur un hébergement partagé (cPanel, Plesk, etc.) :

```bash
# 1. L'utilisateur web est probablement votre nom d'utilisateur
# Remplacez USERNAME par votre nom d'utilisateur
chown -R USERNAME:USERNAME storage bootstrap/cache

# 2. Permissions spéciales pour hébergements partagés
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 3. Si les erreurs persistent, essayez 777 (temporairement)
chmod -R 777 storage/framework
chmod -R 777 bootstrap/cache

# ⚠️ ATTENTION : 777 est moins sécurisé, à utiliser temporairement
# Une fois que ça fonctionne, revenez à 775
```

---

## 🔐 Configuration SELinux (si applicable)

Si votre serveur utilise SELinux (CentOS/RHEL) :

```bash
# 1. Vérifier si SELinux est actif
getenforce

# 2. Si SELinux est actif, ajuster les contextes
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/home/kazaria_dev/web/kazaria-ci.com/public_html/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/home/kazaria_dev/web/kazaria-ci.com/public_html/bootstrap/cache(/.*)?"

# 3. Appliquer les contextes
sudo restorecon -Rv /home/kazaria_dev/web/kazaria-ci.com/public_html/storage
sudo restorecon -Rv /home/kazaria_dev/web/kazaria-ci.com/public_html/bootstrap/cache

# 4. OU désactiver temporairement SELinux (déconseillé)
sudo setenforce 0
```

---

## 🎯 Solution Spécifique à Votre Erreur

Basé sur votre chemin `/home/kazaria_dev/web/kazaria-ci.com/public_html`, voici les commandes exactes :

```bash
# Connectez-vous via SSH à votre serveur

# 1. Aller dans votre projet
cd /home/kazaria_dev/web/kazaria-ci.com/public_html

# 2. Créer les dossiers spécifiques
mkdir -p storage/framework/views
chmod 775 storage/framework/views

# 3. Ajuster le propriétaire (essayez ces options dans l'ordre)
# Option A : Si vous avez sudo et utilisez Apache/Nginx
sudo chown -R www-data:www-data storage/framework/views

# Option B : Si vous êtes sur cPanel ou hébergement partagé
chown -R kazaria_dev:kazaria_dev storage/framework/views

# Option C : En dernier recours (temporaire)
chmod 777 storage/framework/views

# 4. Nettoyer le cache des vues
php artisan view:clear

# 5. Tester en visitant votre boutique
```

---

## ✅ Vérification Finale

Une fois corrigé, testez :

1. **Visitez une boutique** sur votre site
2. **Vérifiez qu'il n'y a plus d'erreur 500**
3. **Essayez d'uploader une image** de produit
4. **Vérifiez les logs** : `tail -f storage/logs/laravel.log`

---

## 🆘 Si Rien ne Fonctionne

1. **Vérifiez les logs serveur** :
   ```bash
   # Apache
   tail -f /var/log/apache2/error.log
   
   # Nginx
   tail -f /var/log/nginx/error.log
   ```

2. **Contactez votre hébergeur** pour qu'il ajuste les permissions

3. **Solution temporaire** (le temps que l'hébergeur réponde) :
   ```bash
   chmod -R 777 storage
   chmod -R 777 bootstrap/cache
   ```
   ⚠️ **Attention** : Moins sécurisé, à utiliser temporairement

---

## 📞 Support Rapide

**Commandes de diagnostic à envoyer si vous demandez de l'aide** :

```bash
# Informations système
uname -a
php -v

# Permissions actuelles
ls -la storage/framework/views
ls -la storage

# Propriétaire
stat storage/framework/views

# Utilisateur PHP
php -r "echo exec('whoami');"

# Processus web
ps aux | grep -E '(apache|httpd|nginx)' | grep -v root
```

---

## 🎉 Résultat Attendu

Après correction, vous devriez voir :

```bash
$ ls -la storage/framework/views
drwxrwxr-x 2 www-data www-data 4096 Oct 14 15:30 .
```

Et votre site devrait fonctionner sans erreur 500 ! ✅

---

*Document créé pour résoudre l'erreur de permissions KAZARIA*
