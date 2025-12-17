# 🔧 Dépannage Rapide - Menus Admin Manquants

## 🎯 Problème : Certains menus ne s'affichent pas

### ✅ Solution en 3 étapes

#### Étape 1️⃣ : Exécuter le diagnostic
```bash
php diagnostic-permissions.php
```

Ce script va vous indiquer exactement ce qui ne va pas.

---

#### Étape 2️⃣ : Appliquer les corrections

**Option A : Script automatique (RECOMMANDÉ)**

Double-cliquez sur :
```
appliquer-permissions.bat
```

**Option B : Commandes manuelles**

```bash
# 1. Charger le helper
composer dump-autoload

# 2. Créer/mettre à jour les permissions
php artisan db:seed --class=PermissionSeeder

# 3. Mettre à jour les rôles
php artisan db:seed --class=RoleSeeder

# 4. Vider les caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

#### Étape 3️⃣ : Vérifier et se reconnecter

1. Fermez votre navigateur complètement
2. Rouvrez et reconnectez-vous au dashboard admin
3. Vérifiez que tous les menus s'affichent

---

## 📋 Menus Qui Devraient Apparaître

### Si vous avez le rôle **Super Admin** :
Vous devriez voir **TOUS** les menus :
- ✅ Dashboard
- ✅ Utilisateurs
- ✅ Produits
- ✅ Commandes
- ✅ Boutiques
- ✅ **Messages** ← Nouveau
- ✅ **Paiements** ← Nouveau
- ✅ Catégories
- ✅ Sous-catégories
- ✅ Attributs
- ✅ Bannières
- ✅ Newsletter
- ✅ Pop-ups
- ✅ Carousel Principal
- ✅ Marques
- ✅ Rapports
- ✅ Statistiques
- ✅ Paramètres
- ✅ Codes promo
- ✅ Rôles & Permissions

### Si vous avez le rôle **Moderator** :
Vous devriez voir :
- ✅ Dashboard
- ✅ Utilisateurs
- ✅ Produits
- ✅ Commandes
- ✅ Boutiques
- ✅ **Messages**
- ✅ **Paiements**
- ✅ Catégories
- ✅ Sous-catégories
- ✅ Attributs
- ✅ Bannières
- ✅ Carousel Principal
- ✅ Marques
- ✅ Codes promo
- ✅ Statistiques

### Si vous avez le rôle **Support** :
Vous devriez voir :
- ✅ Dashboard
- ✅ Commandes
- ✅ **Messages**
- ✅ Statistiques

---

## ⚠️ Problèmes Fréquents

### ❌ Aucun menu ne s'affiche (sauf Dashboard)

**Cause** : L'utilisateur n'a pas de rôle assigné ou le rôle n'a pas de permissions.

**Solution** :
```bash
php artisan tinker
```
```php
// Trouver votre utilisateur
$admin = App\Models\User::where('email', 'votre@email.com')->first();

// Vérifier son rôle
echo $admin->role ? $admin->role->name : 'AUCUN ROLE';

// Si pas de rôle, en assigner un
$superAdmin = App\Models\Role::where('slug', 'super-admin')->first();
$admin->role_id = $superAdmin->id;
$admin->save();
echo "✅ Rôle assigné : Super Admin";
```

---

### ❌ Erreur "Call to undefined function canAccess()"

**Cause** : Le helper n'est pas chargé.

**Solution** :
```bash
composer dump-autoload
php artisan cache:clear
```

---

### ❌ Erreur 403 ou "Permission denied"

**Cause** : Vous n'avez pas la permission nécessaire.

**Solution** : Vérifiez votre rôle et ses permissions :
```bash
php artisan tinker
```
```php
$admin = App\Models\User::where('email', 'votre@email.com')->first();
$admin->load('role.permissions');

// Afficher toutes vos permissions
$admin->role->permissions->pluck('slug')->toArray();
```

---

### ❌ Les menus Messages et Paiements n'apparaissent pas

**Cause** : Permissions manquantes ou sidebar pas à jour.

**Solution** :
1. Vérifiez que vous avez accepté les modifications de la sidebar
2. Videz le cache : `php artisan view:clear`
3. Vérifiez vos permissions :
```bash
php artisan tinker
```
```php
$admin = App\Models\User::where('email', 'votre@email.com')->first();
echo $admin->role->hasPermission('manage_messages') ? '✅ Messages OK' : '❌ Pas de permission Messages';
echo "\n";
echo $admin->role->hasPermission('manage_payments') ? '✅ Paiements OK' : '❌ Pas de permission Paiements';
```

---

### ❌ Erreur de connexion à MySQL

**Cause** : Laragon n'est pas démarré ou MySQL est arrêté.

**Solution** :
1. Ouvrez Laragon
2. Cliquez sur "Démarrer tout"
3. Attendez que tous les voyants soient verts
4. Réessayez les commandes

---

## 🔍 Tests de Vérification

### Test 1 : Vérifier les permissions dans la base
```bash
php artisan tinker
```
```php
// Compter les permissions
App\Models\Permission::count(); // Devrait être 25

// Vérifier qu'elles existent toutes
$required = ['manage_messages', 'manage_payments', 'view_statistics', 'manage_banners', 'manage_carousel', 'manage_brands', 'manage_coupons', 'manage_subcategories', 'manage_attributes'];

foreach ($required as $perm) {
    echo App\Models\Permission::where('slug', $perm)->exists() ? "✅ $perm\n" : "❌ $perm MANQUANT\n";
}
```

### Test 2 : Vérifier les rôles
```bash
php artisan tinker
```
```php
// Super Admin devrait avoir 25 permissions
$superAdmin = App\Models\Role::where('slug', 'super-admin')->first();
echo $superAdmin->permissions()->count() . " / 25 permissions\n";

// Moderator devrait avoir ~17 permissions
$moderator = App\Models\Role::where('slug', 'moderator')->first();
echo $moderator->permissions()->count() . " permissions\n";

// Support devrait avoir ~3 permissions
$support = App\Models\Role::where('slug', 'support')->first();
echo $support->permissions()->count() . " permissions\n";
```

### Test 3 : Vérifier le helper
Créez un fichier test : `test-helper.php`
```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo function_exists('canAccess') ? "✅ canAccess()\n" : "❌ canAccess() MANQUANT\n";
echo function_exists('canAccessAny') ? "✅ canAccessAny()\n" : "❌ canAccessAny() MANQUANT\n";
echo function_exists('isSuperAdmin') ? "✅ isSuperAdmin()\n" : "❌ isSuperAdmin() MANQUANT\n";
```

Exécutez : `php test-helper.php`

---

## 📞 Aide Supplémentaire

Si le problème persiste après avoir suivi ce guide :

1. Consultez `CORRECTION_MENUS_MANQUANTS.md` pour plus de détails
2. Exécutez `diagnostic-permissions.php` et notez les erreurs
3. Vérifiez les logs : `storage/logs/laravel.log`

---

## ✅ Checklist Finale

Avant de déclarer que tout fonctionne :

- [ ] Laragon est démarré (voyants verts)
- [ ] `composer dump-autoload` exécuté
- [ ] `php artisan db:seed --class=PermissionSeeder` exécuté
- [ ] `php artisan db:seed --class=RoleSeeder` exécuté
- [ ] Caches vidés (cache:clear, view:clear, config:clear)
- [ ] Navigateur fermé et rouvert
- [ ] Reconnexion au dashboard admin
- [ ] Tous les menus appropriés s'affichent

---

**Bon courage ! 🚀**

