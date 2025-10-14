# ⚡ Commandes Rapides KAZARIA

## 🚨 URGENCE - Correction Rapide

```bash
# Si vous avez une erreur 500 ou des images cassées
cd /chemin/vers/votre/projet
php artisan permissions:fix
php artisan storage:fix --force
sudo chown -R www-data:www-data storage bootstrap/cache
php artisan optimize:clear
```

---

## 🔧 Déploiement

### Installation Initiale
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
bash fix-permissions.sh
php artisan storage:fix --force
php artisan optimize
```

### Mise à Jour
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

---

## 🔍 Diagnostic

```bash
# Vérifier tout
php artisan storage:check
php artisan permissions:fix

# Logs
tail -f storage/logs/laravel.log
tail -f /var/log/apache2/error.log  # ou nginx
```

---

## 🗑️ Cache

```bash
# Tout nettoyer
php artisan optimize:clear

# Recréer le cache
php artisan optimize

# Nettoyer spécifiquement
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📁 Permissions

```bash
# Script automatique (RECOMMANDÉ)
bash fix-permissions.sh

# Commande Artisan
php artisan permissions:fix

# Manuel
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Test d'écriture
php -r "file_put_contents('storage/logs/test.txt', 'test');"
```

---

## 🖼️ Storage

```bash
# Vérifier
php artisan storage:check

# Réparer
php artisan storage:fix --force

# Lien symbolique
php artisan storage:link

# Vérifier le lien
ls -la public/storage
```

---

## 🗄️ Base de Données

```bash
# État des migrations
php artisan migrate:status

# Exécuter les migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Fresh (DANGER !)
php artisan migrate:fresh --seed
```

---

## 🔐 Permissions par Type de Serveur

### Ubuntu/Debian
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### CentOS/RHEL
```bash
sudo chown -R apache:apache storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### cPanel / Hébergement Partagé
```bash
chown -R votreuser:votreuser storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

---

## 🎯 Tests Rapides

```bash
# Tester l'API
curl https://kazaria-ci.com/api/categories

# Tester la page d'accueil
curl https://kazaria-ci.com

# Vérifier PHP
php -v
php -m  # Modules installés

# Vérifier Composer
composer --version

# Vérifier Laravel
php artisan --version
```

---

## 🔄 SELinux (si applicable)

```bash
# Vérifier
getenforce

# Ajuster les contextes
sudo semanage fcontext -a -t httpd_sys_rw_content_t "storage(/.*)?"
sudo restorecon -Rv storage

# Désactiver temporairement (déconseillé)
sudo setenforce 0
```

---

## 📊 Informations Système

```bash
# Info serveur
uname -a
php -v
composer --version

# Utilisateur web
ps aux | grep -E '(apache|httpd|nginx)' | head -5

# Espace disque
df -h
du -sh storage/

# Permissions storage
ls -la storage/framework/views
stat storage/framework/views
```

---

## 🚀 Optimisation Production

```bash
# Cache complet
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Autoloader optimisé
composer dump-autoload --optimize --classmap-authoritative

# Tout en une commande
php artisan optimize
```

---

## 🔧 Maintenance

```bash
# Nettoyer vieux logs (30 jours)
find storage/logs -name "*.log" -mtime +30 -delete

# Nettoyer vieux fichiers cache
find storage/framework/cache -mtime +7 -delete

# Backup base de données
mysqldump -u user -p kazaria > backup_$(date +%Y%m%d).sql

# Backup storage
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public
```

---

## 🆘 Résolution de Problèmes

### Erreur 500
```bash
tail -f storage/logs/laravel.log
php artisan permissions:fix
php artisan optimize:clear
```

### Images cassées
```bash
php artisan storage:check
php artisan storage:fix --force
ls -la public/storage
```

### Page blanche
```bash
# Activer debug temporairement
echo "APP_DEBUG=true" >> .env
php artisan config:clear
tail -f storage/logs/laravel.log
```

### Lenteur
```bash
php artisan optimize
composer dump-autoload --optimize
# Vérifier les logs pour slow queries
```

---

## 📝 Fichiers Importants

```bash
# Vérifier .env
cat .env | grep -v "PASSWORD\|KEY"

# Vérifier .gitignore
cat .gitignore

# Vérifier composer.json
cat composer.json

# Vérifier les routes
php artisan route:list

# Vérifier la config
php artisan config:show
```

---

## 🔒 Sécurité

```bash
# Protéger .env
chmod 600 .env

# Vérifier permissions public
chmod 755 public

# Vérifier que .env n'est pas accessible
curl https://kazaria-ci.com/.env  # Devrait retourner 404

# Scanner les vulnérabilités
composer audit
```

---

## 🎨 Frontend

```bash
# Build assets (si utilisation de Vite/Mix)
npm install
npm run build

# Pour dev
npm run dev
```

---

## 📧 Email & Queues

```bash
# Tester email
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com'); });

# Worker queue (si utilisé)
php artisan queue:work --daemon

# Redémarrer queue
php artisan queue:restart
```

---

## 🔄 Git

```bash
# Status
git status

# Pull dernières modifications
git pull origin main

# Tag version
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

---

## ⚡ One-Liners Utiles

```bash
# Tout réparer en une commande
bash fix-permissions.sh && php artisan storage:fix --force && php artisan optimize

# Vérification complète
php artisan storage:check && php artisan permissions:fix && echo "✅ OK"

# Déploiement rapide
git pull && composer install --no-dev && php artisan migrate --force && php artisan optimize

# Nettoyage complet
php artisan optimize:clear && php artisan storage:fix --force && php artisan optimize
```

---

*Commandes rapides pour KAZARIA - Gardez ce fichier à portée de main !*
