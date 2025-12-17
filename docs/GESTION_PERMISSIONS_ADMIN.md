# Gestion des Permissions Admin - Documentation

## 🔧 Problèmes Corrigés

### Avant les corrections :
1. ❌ **Sidebar visible pour tous** : Tous les liens du menu admin étaient visibles pour tous les utilisateurs admin, peu importe leurs permissions réelles
2. ❌ **Routes non protégées** : Plusieurs routes importantes n'avaient pas de middleware de protection (Statistics, Payments, Banners, Carousel, Brands, Coupons, Subcategories, Attributes, Reports)
3. ❌ **Super admins contournaient les permissions** : Les utilisateurs avec `is_admin = true` avaient accès à TOUT sans vérification de permissions
4. ❌ **Permissions manquantes** : Plusieurs modules n'avaient pas de permissions définies dans la base de données

### Après les corrections :
1. ✅ **Sidebar dynamique** : Les liens sont affichés uniquement si l'utilisateur a les permissions nécessaires
2. ✅ **Routes protégées** : Toutes les routes admin sont maintenant protégées par des middlewares de permission
3. ✅ **Système de permissions unifié** : Même les super admins doivent avoir les permissions appropriées via leur rôle
4. ✅ **Permissions complètes** : Toutes les sections admin ont maintenant des permissions définies

---

## 📋 Nouvelles Permissions Ajoutées

Les permissions suivantes ont été ajoutées au système :

| Permission | Description | Module |
|-----------|-------------|---------|
| `view_statistics` | Voir les statistiques et le dashboard | statistics |
| `manage_banners` | Gérer les bannières et publicités | banners |
| `manage_carousel` | Gérer le carousel principal | carousel |
| `manage_brands` | Gérer les marques | brands |
| `manage_coupons` | Gérer les codes promo | coupons |
| `manage_subcategories` | Gérer les sous-catégories | subcategories |
| `manage_attributes` | Gérer les attributs de produits | attributes |

---

## 🎭 Rôles et Permissions

### Super Admin
- Possède **TOUTES** les permissions
- Accès complet à toutes les fonctionnalités

### Moderator
- Gestion : Utilisateurs, Produits, Commandes, Boutiques
- Contenu : Catégories, Sous-catégories, Attributs, Bannières, Carousel, Marques, Coupons
- Données : Messages, Statistiques, Paiements

### Support
- Gestion : Commandes, Messages
- Visualisation : Statistiques

---

## 🔨 Helper de Permissions

Un nouveau helper a été créé pour faciliter la vérification des permissions dans les vues :

### `canAccess($permission)`
Vérifie si l'utilisateur actuel a une permission spécifique.

```php
@if(canAccess('view_users'))
    <a href="{{ route('admin.users.index') }}">Voir les utilisateurs</a>
@endif
```

### `canAccessAny($permissions)`
Vérifie si l'utilisateur a au moins une des permissions listées.

```php
@if(canAccessAny(['view_users', 'view_products', 'view_orders']))
    <li class="nav-section">Gestion</li>
@endif
```

### `isSuperAdmin()`
Vérifie si l'utilisateur est un super admin sans rôle spécifique (ancien système).

```php
@if(isSuperAdmin())
    <div class="admin-only-content">Contenu pour super admin uniquement</div>
@endif
```

---

## 📦 Installation / Migration

Pour appliquer les changements, exécutez les commandes suivantes dans l'ordre :

```bash
# 1. Charger le nouveau helper
composer dump-autoload

# 2. Exécuter le seeder des permissions (crée les nouvelles permissions)
php artisan db:seed --class=PermissionSeeder

# 3. Mettre à jour les rôles avec les nouvelles permissions
php artisan db:seed --class=RoleSeeder
```

---

## 🛡️ Routes Protégées

Toutes les routes admin sont maintenant protégées par le middleware `admin` (vérification de base) ET par des middlewares de permission spécifiques :

### Exemple : Routes des utilisateurs
```php
Route::prefix('users')->name('users.')->middleware('permission:view_users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    
    Route::middleware('permission:create_users')->group(function () {
        Route::post('/', [UserController::class, 'store'])->name('store');
    });
    
    Route::middleware('permission:edit_users')->group(function () {
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
    });
    
    Route::middleware('permission:delete_users')->group(function () {
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});
```

---

## 👥 Gestion des Utilisateurs Admin

### Créer un nouvel admin avec un rôle spécifique

```php
// Dans tinker ou un seeder
$user = User::create([
    'nom' => 'Dupont',
    'prenoms' => 'Jean',
    'email' => 'jean.dupont@example.com',
    'password' => bcrypt('password'),
    'is_admin' => true,
    'is_verified' => true,
    'email_verified_at' => now(),
]);

// Assigner un rôle
$moderatorRole = Role::where('slug', 'moderator')->first();
$user->role_id = $moderatorRole->id;
$user->save();
```

### Modifier les permissions d'un rôle

```php
// Récupérer le rôle
$role = Role::where('slug', 'support')->first();

// Ajouter une permission
$permission = Permission::where('slug', 'manage_payments')->first();
$role->permissions()->attach($permission->id);

// Retirer une permission
$role->permissions()->detach($permission->id);

// Remplacer toutes les permissions
$permissions = Permission::whereIn('slug', [
    'view_orders', 
    'manage_orders', 
    'manage_messages'
])->get();
$role->permissions()->sync($permissions->pluck('id'));
```

---

## ⚠️ Important

1. **Compatibilité ascendante** : Les utilisateurs avec `is_admin = true` SANS `role_id` conservent l'accès complet (ancien système)
2. **Nouveaux admins** : Tous les nouveaux admins DOIVENT avoir un rôle assigné
3. **Middleware CheckPermission** : Les super admins avec un rôle défini sont maintenant soumis aux permissions de leur rôle
4. **Sidebar dynamique** : Les liens non autorisés sont automatiquement cachés

---

## 🔄 Migration Progressive

Pour migrer progressivement vos admins existants vers le nouveau système :

1. **Identifier les admins actuels**
```bash
php artisan tinker
User::where('is_admin', true)->whereNull('role_id')->get();
```

2. **Leur assigner un rôle approprié**
```php
$admins = User::where('is_admin', true)->whereNull('role_id')->get();
$superAdminRole = Role::where('slug', 'super-admin')->first();

foreach ($admins as $admin) {
    $admin->role_id = $superAdminRole->id;
    $admin->save();
}
```

---

## 🐛 Dépannage

### Un admin ne peut plus accéder à certaines pages

1. Vérifier qu'il a bien un rôle assigné :
```php
$user = User::find($userId);
echo $user->role ? $user->role->name : 'Aucun rôle';
```

2. Vérifier les permissions de son rôle :
```php
$user->role->permissions->pluck('slug');
```

3. Ajouter les permissions manquantes si nécessaire

### La sidebar ne se met pas à jour

1. Vider le cache :
```bash
php artisan cache:clear
php artisan view:clear
```

2. Vérifier que le helper est chargé :
```bash
composer dump-autoload
```

---

## 📞 Support

Pour toute question ou problème, consultez cette documentation ou contactez l'équipe de développement.

