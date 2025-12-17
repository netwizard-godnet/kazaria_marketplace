# Option : Protéger le Dashboard avec une Permission

## 📋 Si vous voulez protéger le Dashboard

Par défaut, le Dashboard est accessible à tous les admins. Si vous voulez le protéger avec une permission spécifique, suivez ces étapes :

---

## Étape 1 : Ajouter la permission dans PermissionSeeder.php

Ajoutez cette ligne dans le tableau `$permissions` :

```php
// Dashboard
['name' => 'Voir le dashboard', 'slug' => 'view_dashboard', 'description' => 'Peut voir le dashboard principal', 'module' => 'dashboard'],
```

---

## Étape 2 : Protéger le menu Dashboard dans la sidebar

Remplacez dans `resources/views/admin/layouts/sidebar.blade.php` :

**Avant** (ligne 27-32) :
```blade
<!-- Dashboard -->
<li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <a href="{{ route('admin.dashboard') }}">
        <i class="fas fa-home"></i>
        <p>Dashboard</p>
    </a>
</li>
```

**Après** :
```blade
<!-- Dashboard -->
@if(canAccess('view_dashboard'))
<li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <a href="{{ route('admin.dashboard') }}">
        <i class="fas fa-home"></i>
        <p>Dashboard</p>
    </a>
</li>
@endif
```

---

## Étape 3 : Mettre à jour le RoleSeeder.php

Dans `database/seeders/RoleSeeder.php`, ajoutez 'dashboard' aux modules :

**Pour Moderator** (ligne 56-60) :
```php
$moderatorPermissions = $allPermissions->filter(function($permission) {
    return in_array($permission->module, [
        'dashboard', // ← AJOUTER
        'users', 'products', 'orders', 'stores', 'categories', 'subcategories',
        'messages', 'statistics', 'banners', 'carousel', 'brands', 'coupons',
        'attributes', 'payments'
    ]);
});
```

**Pour Support** (ligne 65-67) :
```php
$supportPermissions = $allPermissions->filter(function($permission) {
    return in_array($permission->module, [
        'dashboard', // ← AJOUTER
        'orders', 'messages', 'statistics'
    ]);
});
```

---

## Étape 4 : Appliquer les changements

```bash
# 1. Créer la nouvelle permission
php artisan db:seed --class=PermissionSeeder

# 2. Assigner aux rôles
php artisan db:seed --class=RoleSeeder

# 3. Vider les caches
php artisan cache:clear
php artisan view:clear
```

---

## ⚠️ Important

Si vous faites cela, **TOUS** les admins devront avoir la permission `view_dashboard` pour accéder au dashboard. Assurez-vous que tous vos rôles l'ont !

---

## 💡 Notre Recommandation

**NE PAS protéger le Dashboard**. C'est la page d'accueil de l'admin et devrait être accessible à tous les admins. C'est une pratique standard dans la plupart des systèmes admin.

Les permissions sont là pour protéger les **fonctionnalités sensibles**, pas la page d'accueil.

