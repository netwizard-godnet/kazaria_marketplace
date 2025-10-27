# Guide de Test - Bannières Page d'Accueil

## ✅ État actuel
- **2 bannières** dans la base de données
- **3 zones** configurées pour la page d'accueil
- **Interface organisée** par zones avec onglets

## 🧪 Tests à effectuer

### 1. Accès à la page
```
URL: http://127.0.0.1:8000/admin/banners
```
**Résultat attendu :**
- Page "Bannières de la Page d'Accueil"
- 3 onglets : Carousel principal, Zone haute, Sidebar droite
- Compteurs de bannières sur chaque onglet

### 2. Navigation entre onglets
- Cliquer sur "Carousel principal" → doit afficher 1 bannière
- Cliquer sur "Zone haute" → doit afficher 1 bannière  
- Cliquer sur "Sidebar droite" → doit afficher "Aucune bannière"

### 3. Création de bannière
- Cliquer sur "Ajouter une bannière" dans n'importe quel onglet
- Remplir le formulaire :
  - Titre : "Test Bannière"
  - Image : sélectionner une image
  - Zone : doit être pré-sélectionnée selon l'onglet
- Cliquer sur "Créer la bannière"

### 4. Actions sur bannières
- **Modifier** : cliquer sur l'icône crayon
- **Activer/Désactiver** : cliquer sur l'icône play/pause
- **Dupliquer** : cliquer sur l'icône copie
- **Supprimer** : cliquer sur l'icône poubelle

### 5. Vue classique
- Cliquer sur "Vue classique"
- Doit afficher un tableau avec les 2 bannières
- Formulaire de création à gauche

## 🔧 En cas de problème

### Cache
```bash
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### Vérification des routes
```bash
php artisan route:list --name=admin.banners
```

### Vérification des bannières
```bash
php artisan tinker
>>> App\Models\Banner::all()
```

## 📊 Zones configurées

| Zone | Code | Description | Bannières |
|------|------|-------------|-----------|
| Carousel principal | `homepage_carousel` | Bannières du carousel | 1 |
| Zone haute | `homepage_top` | Bannières en haut | 1 |
| Sidebar droite | `homepage_sidebar` | Bannières sidebar | 0 |

## 🎯 Objectif
Interface claire et organisée pour gérer uniquement les bannières de la page d'accueil, avec possibilité de basculer entre vue organisée (onglets) et vue classique (tableau).
