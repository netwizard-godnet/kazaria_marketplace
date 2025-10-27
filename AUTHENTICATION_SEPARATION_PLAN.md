# 🔐 Plan de Séparation de l'Authentification KAZARIA

## 🎯 Objectif
Séparer complètement l'authentification web (sessions) et API (tokens) pour éviter les conflits.

## 📊 État Actuel - Problèmes Identifiés

### ❌ Conflits Majeurs
1. **ProfileController** : Mélange `auth()->user()` et `$request->user()`
2. **StoreController** : Mélange `auth()->user()` et `$request->user()`
3. **Routes incohérentes** : `auth:web` vs `auth` vs `client.auth`
4. **Middleware personnalisé** : `ClientAuth` interfère avec l'auth standard

## 🎯 Plan de Séparation

### 1. AUTHENTIFICATION WEB (Sessions)
**Usage** : Pages web, navigation, profil utilisateur
**Middleware** : `auth:web`
**Méthodes** : `auth()->user()`, `Auth::user()`, `@auth`

**Contrôleurs concernés** :
- ✅ ProfileController (partie web)
- ✅ StoreController (partie web)
- ✅ HomeController
- ✅ CartController (partie web)

**Routes concernées** :
- `/profile` → `auth:web`
- `/store/create` → `auth:web`
- `/store/dashboard` → `auth:web`
- `/logout` → `auth:web`

### 2. AUTHENTIFICATION API (Tokens)
**Usage** : API, AJAX, vendeurs, commandes
**Middleware** : `auth:sanctum`
**Méthodes** : `$request->user()`

**Contrôleurs concernés** :
- ✅ AuthController (API)
- ✅ OrderController (API)
- ✅ Seller/OrderController
- ✅ Seller/ProductController
- ✅ ReviewController (API)

**Routes concernées** :
- `/api/*` → `auth:sanctum`
- `/store/api/*` → `auth:sanctum`

### 3. AUTHENTIFICATION ADMIN (Sessions + Tokens)
**Usage** : Administration
**Middleware** : `auth:web` + vérification `is_admin`
**Méthodes** : `Auth::user()`, `Auth::check()`

## 🔧 Actions à Effectuer

### Phase 1 : Nettoyer les Contrôleurs
1. **ProfileController** : Utiliser uniquement `auth()->user()` pour web
2. **StoreController** : Séparer web (`auth()->user()`) et API (`$request->user()`)
3. **OrderController** : Séparer web et API

### Phase 2 : Corriger les Routes
1. **Routes web** : Utiliser `auth:web` uniquement
2. **Routes API** : Utiliser `auth:sanctum` uniquement
3. **Supprimer** : `client.auth` middleware

### Phase 3 : Nettoyer les Vues
1. **Header** : Utiliser `@auth` et `Auth::user()`
2. **API calls** : Utiliser tokens
3. **Séparer** : JavaScript web vs API

### Phase 4 : Tests
1. **Tester** : Navigation web (sessions)
2. **Tester** : API calls (tokens)
3. **Vérifier** : Pas de conflits entre les deux

## ✅ Résultat Attendu
- **Navigation web** : Fonctionne avec sessions
- **API calls** : Fonctionne avec tokens
- **Pas de conflits** entre les deux systèmes
- **Performance** améliorée
- **Sécurité** renforcée
