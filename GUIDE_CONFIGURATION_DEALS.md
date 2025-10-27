# Guide de Configuration des "Deals du jour"

## Paramètres configurables

Le système "Deals du jour" est entièrement configurable via la base de données.

### Paramètres disponibles

#### 1. Durée du countdown (`deals_countdown_duration`)
- **Description**: Durée du countdown avant la fin des deals
- **Format**: Minutes (entier)
- **Défaut**: `60` (1 heure)
- **Exemple**: `30` pour 30 minutes

#### 2. Catégories (`deals_categories`)
- **Description**: Filtre par catégories spécifiques
- **Format**: IDs séparés par des virgules
- **Défaut**: `''` (vide = toutes les catégories)
- **Exemple**: `'1,2,3'` pour afficher uniquement les catégories 1, 2 et 3

#### 3. Sous-catégories (`deals_subcategories`)
- **Description**: Filtre par sous-catégories spécifiques
- **Format**: IDs séparés par des virgules
- **Défaut**: `''` (vide = toutes les sous-catégories)
- **Exemple**: `'5,6,7'` pour afficher uniquement les sous-catégories 5, 6 et 7

#### 4. Pourcentage de remise minimum (`deals_min_discount`)
- **Description**: Pourcentage de remise minimum pour un produit à afficher
- **Format**: Pourcentage (entier)
- **Défaut**: `'10'` (10%)
- **Exemple**: `'15'` pour afficher uniquement les produits avec au moins 15% de remise

#### 5. Pourcentage de remise maximum (`deals_max_discount`)
- **Description**: Pourcentage de remise maximum pour un produit à afficher
- **Format**: Pourcentage (entier)
- **Défaut**: `'25'` (25%)
- **Exemple**: `'50'` pour afficher les produits avec jusqu'à 50% de remise

## Comment configurer

### Via Tinker

```php
// Modifier la durée du countdown à 30 minutes
\App\Helpers\SettingHelper::setSetting('deals_countdown_duration', '30');

// Filtrer par catégories spécifiques
\App\Helpers\SettingHelper::setSetting('deals_categories', '1,2,3');

// Filtrer par sous-catégories spécifiques
\App\Helpers\SettingHelper::setSetting('deals_subcategories', '5,6,7');

// Définir la fourchette de remise (15% à 30%)
\App\Helpers\SettingHelper::setSetting('deals_min_discount', '15');
\App\Helpers\SettingHelper::setSetting('deals_max_discount', '30');
```

### Via Interface Admin (si disponible)

Allez dans les paramètres du système et modifiez les valeurs dans la section "Deals du jour".

## Exemples de configuration

### Exemple 1: Deals sur tous les produits avec 10% à 25% de remise
```php
deals_categories = ''
deals_subcategories = ''
deals_min_discount = '10'
deals_max_discount = '25'
```

### Exemple 2: Deals uniquement sur les téléphones avec 20% à 40% de remise
```php
deals_categories = '1' // ID de la catégorie Téléphones
deals_subcategories = ''
deals_min_discount = '20'
deals_max_discount = '40'
```

### Exemple 3: Deals sur une sous-catégorie spécifique
```php
deals_categories = ''
deals_subcategories = '10' // ID de la sous-catégorie
deals_min_discount = '15'
deals_max_discount = '50'
```

## Remarques importantes

1. **Countdown**: Le countdown se réinitialise à chaque chargement de la page
2. **Filtres combinés**: Vous pouvez combiner catégories ET sous-catégories
3. **Vide = toutes**: Laisser un paramètre vide signifie qu'il n'y a pas de restriction
4. **Produits actifs**: Seuls les produits actifs et en stock sont affichés

## Vérification des paramètres

```php
// Via Tinker
\App\Helpers\SettingHelper::get('deals_countdown_duration'); // '60'
\App\Helpers\SettingHelper::get('deals_categories'); // '1,2' ou ''
\App\Helpers\SettingHelper::get('deals_min_discount'); // '10'
```

## Note

Les modifications des paramètres nécessitent un rafraîchissement de la page pour prendre effet.
