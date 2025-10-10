# 📐 Notes d'Architecture - KAZARIA Frontend

## 🎨 Votre Architecture Frontend Analysée

Ce document liste tous les éléments de votre architecture frontend que j'ai analysés et utilisés pour le système d'authentification.

---

## 🎯 Variables CSS Globales

### Fichier : `public/css/style.css`
```css
:root {
  --main-color: #f04e27;      /* Orange principal KAZARIA */
  --black-color: #000000;      /* Noir */
  --blue-color: #204fa1;       /* Bleu */
}
```

### Fichier : `public/css/boutique_officielle.css`
```css
:root {
    --kazaria-orange: #f04e26;
    --kazaria-white: #ffffff;
    --kazaria-dark: #2c3e50;
    --kazaria-light-gray: #f8f9fa;
    --kazaria-border: #e9ecef;
}
```

---

## 🏗️ Classes Utilitaires Personnalisées

### Couleurs (style.css)
```css
.orange-bg { background-color: var(--main-color) !important; }
.orange-color { color: var(--main-color) !important; }
.black-bg { background-color: var(--black-color) !important; }
.black-color { color: var(--black-color) !important; }
.blue-bg { background-color: var(--blue-color) !important; }
.blue-color { color: var(--blue-color) !important; }
```

### Tailles de Logo
```css
.logo-size-header { max-width: 150px; }
.logo-size-footer { max-width: 150px; }
```

### Tailles de Police
```css
.fs-8 { font-size: 12px; }  /* Utilisé pour petits textes */
```

### Largeurs Personnalisées
```css
.width-100 { width: 100px; }
.width-200 { width: 200px; }
.width-300 { width: 300px; }
.width-400 { width: 400px; }

/* Responsive */
@media (max-width: 768px) {
    .width-300 { width: 200px; }
    .width-400 { width: 300px; }
}

@media (max-width: 480px) {
    .width-300 { width: 100%; }
    .width-400 { width: 100%; }
}
```

### Hauteurs Personnalisées
```css
.h-100px { height: 100px; }
.h-200px { height: 200px; }
.h-300px { height: 300px; }
.h-400px { height: 400px; }
.h-500px { height: 500px; }
```

### Z-Index
```css
.z-index-5x { z-index: 99999 !important; }
.z-index-6x { z-index: 999999 !important; }
.z-index-7x { z-index: 9999999 !important; }
.z-index-8x { z-index: 99999999 !important; }
.z-index-9x { z-index: 999999999 !important; }
```

---

## 🎨 Composants Personnalisés

### Boutons (boutique_officielle.css)
```css
.btn-kazaria {
    background-color: var(--kazaria-orange);
    border-color: var(--kazaria-orange);
    color: white;
    font-weight: 500;
}

.btn-kazaria:hover {
    background-color: #e03d1a;
    border-color: #e03d1a;
    color: white;
}
```

### Cards Produits
```css
.product-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(240, 78, 38, 0.1);
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(240, 78, 38, 0.15);
    border-color: var(--kazaria-orange);
}
```

### Navigation Pills (profil.css)
```css
.nav-pills .nav-link {
    border-radius: 8px;
    margin-bottom: 0.5rem;
    color: #000000;
    font-weight: 500;
}

.nav-pills .nav-link.active {
    background-color: var(--main-color);  /* Orange */
    color: #ffffff;
}
```

### Form Check (boutique_officielle.css)
```css
.form-check-input:checked {
    background-color: var(--kazaria-orange);
    border-color: var(--kazaria-orange);
}
```

---

## 📦 Stack Technique

### Bootstrap
- **Version** : 5.3.3
- **CDN** : https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css

### Icônes
- **Bootstrap Icons** : 1.10.0
- **Font Awesome** : 7.0.1 (fa-solid)

### Polices
```html
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
```
- **Principale** : "Open Sans"
- **Secondaire** : "Inter" (dans boutique_officielle.css)

### JavaScript
- **jQuery** : 3.6.0
- **Bootstrap JS** : 5.3.8
- **Custom** : `main.js`, `carousel.js`

---

## 🏛️ Structure HTML Standard

### Header
```html
<div class="container-fluid orange-bg py-2">
    <!-- Barre de promo en haut -->
</div>

<header class="z-index-9x shadow">
    <div class="container-fluid blue-bg py-0">
        <!-- Logo + Recherche + Menu -->
    </div>
</header>
```

### Footer
```html
<footer class="container-fluid pt-2">
    <!-- Liens et infos -->
</footer>

<!-- Footer mobile sticky -->
<footer class="bg-light px-2 py-4 d-sm-none container-fluid shadow" style="position: sticky; bottom: 0;">
    <!-- Navigation mobile -->
</footer>
```

### Layout Standard
```html
@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- Contenu de la page -->
    </main>
@endsection
```

---

## 🎨 Patterns de Design

### 1. Sections avec Titre
```html
<section class="py-5">
    <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
        <h5 class="mb-0 me-4">Titre de la section</h5>
    </div>
    <!-- Contenu -->
</section>
```

### 2. Cards Produit
```html
<div class="col-6 col-sm-4 col-md-2">
    <a class="px-1 text-decoration-none" href="#">
        <div class="position-relative">
            <img src="..." class="w-100 h-200px object-fit-contain">
            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">
                -18%
            </span>
        </div>
        <div class="py-1">
            <div class="d-flex align-items-center justify-content-start fs-7">
                <span class="fs-7 text-danger fw-bold text-nowrap me-2">50.000 FCFA</span>
                <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
            </div>
            <p class="fs-7 my-2 orange-color">Nom produit</p>
            <div class="hstack gap-1">
                <i class="fa-solid fa-star text-warning fs-8"></i>
                <!-- ... étoiles -->
            </div>
        </div>
    </a>
</div>
```

### 3. Formulaires avec Form-Floating
```html
<div class="form-floating mb-4">
    <input type="email" class="form-control form-control-sm" 
           id="email" placeholder="name@example.com">
    <label for="email" class="small">
        <i class="bi bi-envelope me-1"></i>Adresse email
    </label>
</div>
```

### 4. Badges et Status
```html
<!-- Badge orange -->
<span class="orange-bg px-3 fs-7 text-white">Fin dans 00:00:00</span>

<!-- Badge officiel -->
<span class="badge orange-bg text-white">
    <i class="bi bi-shield-check me-1"></i>Vérifié
</span>
```

---

## 📱 Responsive Design

### Breakpoints
```css
/* Tablette */
@media (max-width: 768px) { }

/* Mobile */
@media (max-width: 480px) { }

/* Très petit mobile */
@media (max-width: 576px) { }
```

### Classes Grid Bootstrap Utilisées
```html
<div class="col-6 col-sm-4 col-md-2">     <!-- Produits -->
<div class="col-md-3">                     <!-- Sidebar -->
<div class="col-md-8">                     <!-- Contenu principal -->
<div class="col-12 col-md-6">             <!-- Formulaires -->
```

### Display Responsive
```html
<div class="d-none d-sm-block">          <!-- Caché sur mobile -->
<div class="d-sm-none">                  <!-- Visible sur mobile uniquement -->
```

---

## 🎯 Conventions de Nommage

### IDs
- camelCase : `loginForm`, `registerEmail`, `acceptTerms`

### Classes
- kebab-case Bootstrap : `form-control`, `btn-primary`
- Classes custom : `.orange-bg`, `.blue-color`, `.logo-size-header`

### Variables CSS
- kebab-case : `--main-color`, `--blue-color`
- Namespace : `--kazaria-orange`, `--kazaria-dark`

---

## 🔗 Assets et Chemins

### Images
```
public/images/
├── logo-orange.png     (Logo principal)
├── logo.png           (Logo alternatif)
├── favicon.png        (Favicon)
├── mockup.png         (App mockup)
└── produit.jpg        (Image produit exemple)
```

### CSS
```
public/css/
├── style.css                   (Styles globaux)
├── boutique_officielle.css    (Styles boutique)
├── carousel.css               (Carousel)
└── profil.css                 (Profil)
```

### JavaScript
```
public/js/
├── main.js         (Scripts principaux)
└── carousel.js     (Carousel multi-items)
```

---

## ✅ Checklist d'Intégration

Quand vous créez une nouvelle page dans KAZARIA :

- [ ] Utiliser Bootstrap 5.3.3
- [ ] Inclure `css/style.css`
- [ ] Utiliser les variables CSS (`--main-color`, `--blue-color`)
- [ ] Appliquer les classes personnalisées (`.orange-bg`, `.blue-bg`)
- [ ] Respecter `.logo-size-header` = 150px max
- [ ] Utiliser `.fs-8` pour petits textes (12px)
- [ ] Boutons avec `.blue-bg` ou `.orange-bg`
- [ ] Bootstrap Icons pour les icônes
- [ ] Police "Open Sans"
- [ ] Structure responsive avec grid Bootstrap
- [ ] Form-floating pour les formulaires
- [ ] Cards avec `shadow-lg border-0`
- [ ] Transitions : `transition: all 0.3s ease`

---

## 📝 Notes Importantes

1. **Deux fichiers CSS de couleurs** :
   - `style.css` : `--main-color`, `--blue-color`
   - `boutique_officielle.css` : `--kazaria-orange` (même couleur)
   - Utilisez `.blue-bg` et `.orange-bg` pour cohérence

2. **Tailles de logo** :
   - Header : 150px max
   - Footer : 150px max
   - **PAS 80px** comme dans certains exemples génériques

3. **Classes de police** :
   - `.fs-8` : 12px (valeur absolue)
   - `.fs-7` : utilisée mais pas définie dans style.css (vient de Bootstrap probablement)

4. **Form-check orange** :
   - Les checkboxes deviennent orange quand cochées
   - Automatique avec `form-check-input`

5. **Z-index élevés** :
   - Header : `.z-index-9x`
   - Menus déroulants : `.z-index-9x`
   - Utiliser ces classes pour superposition

---

Cette architecture a été entièrement respectée dans le système d'authentification !

