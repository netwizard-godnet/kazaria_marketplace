# Guide API pour Flutter & Dart - KAZARIA

## Base URL

```
https://votre-domaine.com/api
```

## Authentification

### Format du token

Toutes les requêtes authentifiées doivent inclure le token dans l'en-tête :

```
Authorization: Bearer {token}
```

---

## 1. Authentification

### 1.1 Inscription

**Endpoint :** `POST /api/register`

**Body :**
```json
{
  "nom": "Dupont",
  "prenoms": "Jean",
  "email": "jean@example.com",
  "telephone": "+225 07 12 34 56 78",
  "password": "motdepasse123",
  "password_confirmation": "motdepasse123",
  "termes_condition": true,
  "newsletter": false
}
```

**Réponse (succès) :**
```json
{
  "success": true,
  "message": "Compte créé avec succès ! Vous pouvez maintenant vous connecter. Un email de vérification a été envoyé à votre adresse email pour devenir un utilisateur vérifié.",
  "user": {
    "id": 1,
    "nom": "Dupont",
    "prenoms": "Jean",
    "email": "jean@example.com"
  }
}
```

### 1.2 Connexion

**Endpoint :** `POST /api/login`

**Body :**
```json
{
  "email": "jean@example.com",
  "password": "motdepasse123"
}
```

**Réponse (succès, sans 2FA) :**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "user": {
    "id": 1,
    "nom": "Dupont",
    "prenoms": "Jean",
    "email": "jean@example.com",
    "telephone": "+225 07 12 34 56 78",
    "two_factor_enabled": false
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "requires_code": false
}
```

**Réponse (2FA activé) :**
```json
{
  "success": true,
  "message": "Code de connexion envoyé à votre email",
  "email": "jean@example.com",
  "requires_code": true
}
```

### 1.3 Vérification du code 2FA

**Endpoint :** `POST /api/verify-login-code`

**Body :**
```json
{
  "email": "jean@example.com",
  "code": "12345678"
}
```

**Réponse (succès) :**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "user": {
    "id": 1,
    "nom": "Dupont",
    "prenoms": "Jean",
    "email": "jean@example.com",
    "telephone": "+225 07 12 34 56 78",
    "two_factor_enabled": true
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### 1.4 Informations utilisateur

**Endpoint :** `GET /api/me`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "nom": "Dupont",
    "prenoms": "Jean",
    "email": "jean@example.com",
    "telephone": "+225 07 12 34 56 78",
    "is_verified": true
  }
}
```

### 1.5 Déconnexion

**Endpoint :** `POST /api/logout`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

### 1.6 Déconnexion de tous les appareils

**Endpoint :** `POST /api/logout-all-devices`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Tous les appareils ont été déconnectés avec succès"
}
```

---

## 2. Profil

### 2.1 Mettre à jour le profil

**Endpoint :** `POST /api/profile/update`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "prenoms": "Jean",
  "nom": "Dupont",
  "email": "jean@example.com",
  "telephone": "+225 07 12 34 56 78",
  "adresse": "123 Rue Example",
  "code_postal": "01 BP 1234",
  "ville": "Abidjan",
  "pays": "CI",
  "bio": "Ma bio"
}
```

### 2.2 Changer le mot de passe

**Endpoint :** `POST /api/profile/change-password`

**Headers :**
```
Authorization: Bearer {token}
```

**Body :**
```json
{
  "current_password": "ancienmotdepasse",
  "new_password": "nouveaumotdepasse",
  "new_password_confirmation": "nouveaumotdepasse"
}
```

### 2.3 Mettre à jour la photo de profil

**Endpoint :** `POST /api/profile/update-photo`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body (FormData) :**
```
photo: [fichier image]
```

**Réponse :**
```json
{
  "success": true,
  "message": "Photo de profil mise à jour avec succès",
  "photo_url": "https://votre-domaine.com/storage/profiles/profile_1_1234567890.jpg",
  "user": {
    "id": 1,
    "nom": "Dupont",
    "prenoms": "Jean",
    "email": "jean@example.com",
    "profile_pic_url": "storage/profiles/profile_1_1234567890.jpg"
  }
}
```

### 2.4 Demander la vérification d'email

**Endpoint :** `POST /api/profile/request-email-verification`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Email de vérification envoyé avec succès. Veuillez vérifier votre boîte de réception."
}
```

### 2.5 Activité récente

**Endpoint :** `GET /api/activity/recent`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "activities": [
    {
      "type": "order",
      "title": "Nouvelle commande",
      "description": "Commande #KAZ-20250115-ABC123 pour 150 000 FCFA",
      "date": "Il y a 2 heures",
      "icon": "bag"
    },
    {
      "type": "favorite",
      "title": "Produit ajouté aux favoris",
      "description": "iPhone 15 Pro Max",
      "date": "Il y a 1 jour",
      "icon": "heart"
    }
  ]
}
```

---

## 3. Panier

### 3.1 Ajouter au panier

**Endpoint :** `POST /api/cart/add`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "product_id": 1,
  "quantity": 2,
  "attributes": {
    "couleur": "Rouge",
    "taille": "L"
  }
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Produit ajouté au panier",
  "cart_count": 3
}
```

### 3.2 Obtenir le panier

**Endpoint :** `GET /api/cart/items`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "cart_items": [
    {
      "id": 1,
      "product_id": 1,
      "product_name": "iPhone 15 Pro Max",
      "product_image": "https://votre-domaine.com/images/products/iphone15.jpg",
      "quantity": 2,
      "price": 750000,
      "total": 1500000,
      "attributes": {
        "couleur": "Rouge",
        "taille": "L"
      }
    }
  ],
  "total": 1500000,
  "count": 1
}
```

### 3.3 Mettre à jour la quantité

**Endpoint :** `PUT /api/cart/update/{id}`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "quantity": 3
}
```

### 3.4 Retirer du panier

**Endpoint :** `DELETE /api/cart/remove/{id}`

**Headers :**
```
Authorization: Bearer {token}
```

### 3.5 Vider le panier

**Endpoint :** `DELETE /api/cart/clear`

**Headers :**
```
Authorization: Bearer {token}
```

---

## 4. Favoris

### 4.1 Liste des favoris

**Endpoint :** `GET /api/favorites`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "favorites": [
    {
      "id": 1,
      "product_id": 1,
      "product": {
        "id": 1,
        "name": "iPhone 15 Pro Max",
        "price": 750000,
        "image": "https://votre-domaine.com/images/products/iphone15.jpg"
      }
    }
  ]
}
```

### 4.2 Ajouter/Retirer des favoris

**Endpoint :** `POST /api/favorites/toggle`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "product_id": 1
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Ajouté aux favoris",
  "is_favorite": true,
  "favorites_count": 5
}
```

---

## 5. Commandes

### 5.1 Créer une commande

**Endpoint :** `POST /api/orders/create`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "shipping_name": "Jean Dupont",
  "shipping_email": "jean@example.com",
  "shipping_phone": "+225 07 12 34 56 78",
  "shipping_address": "123 Rue Example, Cocody",
  "shipping_city": "Abidjan",
  "shipping_postal_code": "01 BP 1234",
  "shipping_country": "CI",
  "payment_method": "cash_on_delivery",
  "customer_notes": "Livrer le matin si possible"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Commande créée avec succès",
  "order_id": 1,
  "order_number": "KAZ-20250115-ABC123",
  "redirect": "https://votre-domaine.com/order/invoice/KAZ-20250115-ABC123"
}
```

### 5.2 Mes commandes

**Endpoint :** `GET /api/orders/my-orders?status=all&date=month`

**Headers :**
```
Authorization: Bearer {token}
```

**Query Parameters :**
- `status` : `all`, `pending`, `processing`, `shipped`, `delivered`, `cancelled`
- `date` : `today`, `week`, `month`, `3months`, `year`

**Réponse :**
```json
{
  "success": true,
  "orders": [
    {
      "id": 1,
      "order_number": "KAZ-20250115-ABC123",
      "status": "pending",
      "total": 1500000,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "items": [
        {
          "id": 1,
          "product_name": "iPhone 15 Pro Max",
          "quantity": 2,
          "price": 750000,
          "total": 1500000
        }
      ]
    }
  ]
}
```

### 5.3 Détails d'une commande

**Endpoint :** `GET /api/orders/{orderNumber}`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "order": {
    "id": 1,
    "order_number": "KAZ-20250115-ABC123",
    "status": "pending",
    "payment_status": "pending",
    "payment_method": "cash_on_delivery",
    "subtotal": 1500000,
    "shipping_cost": 5000,
    "discount": 0,
    "total": 1505000,
    "shipping_name": "Jean Dupont",
    "shipping_email": "jean@example.com",
    "shipping_phone": "+225 07 12 34 56 78",
    "shipping_address": "123 Rue Example, Cocody",
    "shipping_city": "Abidjan",
    "orderItems": [
      {
        "id": 1,
        "product_name": "iPhone 15 Pro Max",
        "quantity": 2,
        "price": 750000,
        "total": 1500000
      }
    ]
  }
}
```

### 5.4 Annuler une commande

**Endpoint :** `POST /api/orders/{orderNumber}/cancel`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body (optionnel) :**
```json
{
  "reason": "Commande annulée par le client"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Commande annulée avec succès. Le stock a été libéré.",
  "order": {
    "id": 1,
    "status": "cancelled",
    ...
  }
}
```

---

## 6. Avis

### 6.1 Liste des avis d'un produit

**Endpoint :** `GET /api/products/{productId}/reviews?sort=recent&page=1&per_page=10`

**Query Parameters :**
- `sort` : `recent`, `helpful`, `rating_high`, `rating_low`
- `page` : Numéro de page
- `per_page` : Nombre d'avis par page

**Réponse :**
```json
{
  "success": true,
  "reviews": {
    "data": [
      {
        "id": 1,
        "user": {
          "id": 1,
          "nom": "Dupont",
          "prenoms": "Jean"
        },
        "rating": 5,
        "title": "Excellent produit",
        "comment": "Très satisfait de mon achat",
        "is_verified_purchase": true,
        "helpful_count": 10,
        "created_at": "2025-01-15T10:30:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 10,
    "total": 25
  },
  "stats": {
    "average_rating": 4.5,
    "total_reviews": 25,
    "distribution": {
      "5": 15,
      "4": 7,
      "3": 2,
      "2": 1,
      "1": 0
    }
  }
}
```

### 6.2 Ajouter un avis

**Endpoint :** `POST /api/reviews`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "product_id": 1,
  "rating": 5,
  "title": "Excellent produit",
  "comment": "Très satisfait de mon achat, je recommande !"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Votre avis a été publié avec succès",
  "review": {
    "id": 1,
    "rating": 5,
    "title": "Excellent produit",
    "comment": "Très satisfait de mon achat, je recommande !",
    "is_verified_purchase": true,
    "user": {
      "id": 1,
      "nom": "Dupont",
      "prenoms": "Jean"
    }
  }
}
```

### 6.3 Voter pour un avis

**Endpoint :** `POST /api/reviews/{reviewId}/vote`

**Body :**
```json
{
  "helpful": true
}
```

---

## 7. Boutique (Vendeur)

### 7.1 Statistiques

**Endpoint :** `GET /api/store/stats`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "stats": {
    "total_products": 25,
    "total_orders": 150,
    "pending_orders": 5,
    "total_sales": 50000000,
    "total_revenue": 47500000
  }
}
```

### 7.2 Commandes récentes

**Endpoint :** `GET /api/store/recent-orders`

**Headers :**
```
Authorization: Bearer {token}
```

### 7.3 Liste des produits

**Endpoint :** `GET /api/store/products`

**Headers :**
```
Authorization: Bearer {token}
```

### 7.4 Créer un produit

**Endpoint :** `POST /api/store/products`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body (FormData) :**
```
name: "Nouveau produit"
description: "Description du produit"
price: 50000
stock: 100
category_id: 1
subcategory_id: 5
images[]: [fichier 1]
images[]: [fichier 2]
```

### 7.5 Modifier un produit

**Endpoint :** `PUT /api/store/products/{id}`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### 7.6 Supprimer un produit

**Endpoint :** `DELETE /api/store/products/{id}`

**Headers :**
```
Authorization: Bearer {token}
```

### 7.7 Liste des commandes

**Endpoint :** `GET /api/store/orders?status=pending&date_from=2025-01-01&date_to=2025-01-31`

**Headers :**
```
Authorization: Bearer {token}
```

**Query Parameters :**
- `status` : `pending`, `processing`, `shipped`, `delivered`, `cancelled`
- `date_from` : Date de début (format: YYYY-MM-DD)
- `date_to` : Date de fin (format: YYYY-MM-DD)
- `search` : Recherche par numéro de commande
- `sort_by` : `created_at`, `total`, `status`
- `sort_order` : `asc`, `desc`

### 7.8 Détails d'une commande vendeur

**Endpoint :** `GET /api/store/orders/{orderNumber}`

**Headers :**
```
Authorization: Bearer {token}
```

### 7.9 Mettre à jour le statut d'une commande

**Endpoint :** `PUT /api/store/orders/{orderNumber}/status`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "status": "processing"
}
```

### 7.10 Marquer comme expédié

**Endpoint :** `POST /api/store/orders/{orderNumber}/ship`

**Headers :**
```
Authorization: Bearer {token}
```

### 7.11 Marquer comme livré

**Endpoint :** `POST /api/store/orders/{orderNumber}/deliver`

**Headers :**
```
Authorization: Bearer {token}
```

### 7.12 Annuler une commande (vendeur)

**Endpoint :** `POST /api/store/orders/{orderNumber}/cancel`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body :**
```json
{
  "reason": "Stock insuffisant"
}
```

---

## 8. Autres

### 8.1 Mot de passe oublié

**Endpoint :** `POST /api/forgot-password`

**Body :**
```json
{
  "email": "jean@example.com"
}
```

### 8.2 Réinitialiser le mot de passe

**Endpoint :** `POST /api/reset-password`

**Body :**
```json
{
  "token": "reset_token_64_caracteres",
  "password": "nouveaumotdepasse",
  "password_confirmation": "nouveaumotdepasse"
}
```

### 8.3 Renvoyer le code de vérification

**Endpoint :** `POST /api/resend-verification-code`

**Body :**
```json
{
  "email": "jean@example.com",
  "type": "login"
}
```

### 8.4 Appliquer un coupon

**Endpoint :** `POST /api/coupons/apply`

**Body :**
```json
{
  "code": "PROMO2025"
}
```

### 8.5 Vérifier le statut vendeur

**Endpoint :** `GET /api/check-seller-status`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "is_seller": true,
  "has_store": true,
  "store_status": "active"
}
```

---

## 9. Gestion des erreurs

### 9.1 Codes de statut HTTP

- `200` : Succès
- `201` : Créé avec succès
- `400` : Requête invalide
- `401` : Non authentifié (token invalide ou expiré)
- `403` : Accès refusé (permissions insuffisantes)
- `404` : Ressource non trouvée
- `422` : Erreur de validation
- `500` : Erreur serveur

### 9.2 Format des erreurs

**Erreur de validation (422) :**
```json
{
  "success": false,
  "message": "Veuillez corriger 2 erreur(s) dans le formulaire",
  "errors": {
    "email": ["L'adresse email est obligatoire"],
    "password": ["Le mot de passe doit contenir au moins 8 caractères"]
  }
}
```

**Erreur d'authentification (401) :**
```json
{
  "success": false,
  "message": "Utilisateur non authentifié"
}
```

**Erreur générique :**
```json
{
  "success": false,
  "message": "Message d'erreur explicite"
}
```

### 9.3 Gestion du token expiré

Si vous recevez une erreur 401 :
1. Supprimer le token stocké
2. Rediriger vers la page de connexion
3. Demander à l'utilisateur de se reconnecter

---

## 10. Exemple de code Dart

### 10.1 Service d'authentification

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AuthService {
  final String baseUrl = 'https://votre-domaine.com/api';
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  
  // Connexion
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );
    
    final data = jsonDecode(response.body);
    
    if (data['success'] == true) {
      if (data['requires_code'] == true) {
        // 2FA activé
        return {
          'requires_code': true,
          'email': data['email'],
        };
      } else if (data['token'] != null) {
        // Connexion réussie, stocker le token
        await _storage.write(key: 'auth_token', value: data['token']);
        return {
          'success': true,
          'user': data['user'],
        };
      }
    }
    
    throw Exception(data['message'] ?? 'Erreur de connexion');
  }
  
  // Vérifier le code 2FA
  Future<Map<String, dynamic>> verifyCode(String email, String code) async {
    final response = await http.post(
      Uri.parse('$baseUrl/verify-login-code'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'code': code,
      }),
    );
    
    final data = jsonDecode(response.body);
    
    if (data['success'] == true && data['token'] != null) {
      await _storage.write(key: 'auth_token', value: data['token']);
      return {
        'success': true,
        'user': data['user'],
      };
    }
    
    throw Exception(data['message'] ?? 'Code invalide');
  }
  
  // Obtenir les headers avec token
  Future<Map<String, String>> getHeaders() async {
    final token = await _storage.read(key: 'auth_token');
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }
  
  // Déconnexion
  Future<void> logout() async {
    final headers = await getHeaders();
    await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: headers,
    );
    await _storage.delete(key: 'auth_token');
  }
  
  // Vérifier si connecté
  Future<bool> isLoggedIn() async {
    final token = await _storage.read(key: 'auth_token');
    return token != null;
  }
}
```

### 10.2 Service de panier

```dart
class CartService {
  final String baseUrl = 'https://votre-domaine.com/api';
  final AuthService _authService = AuthService();
  
  // Ajouter au panier
  Future<Map<String, dynamic>> addToCart(int productId, int quantity, Map<String, dynamic>? attributes) async {
    final headers = await _authService.getHeaders();
    
    final response = await http.post(
      Uri.parse('$baseUrl/cart/add'),
      headers: headers,
      body: jsonEncode({
        'product_id': productId,
        'quantity': quantity,
        'attributes': attributes ?? {},
      }),
    );
    
    final data = jsonDecode(response.body);
    
    if (data['success'] == true) {
      return data;
    }
    
    throw Exception(data['message'] ?? 'Erreur lors de l\'ajout au panier');
  }
  
  // Obtenir le panier
  Future<Map<String, dynamic>> getCart() async {
    final headers = await _authService.getHeaders();
    
    final response = await http.get(
      Uri.parse('$baseUrl/cart/items'),
      headers: headers,
    );
    
    final data = jsonDecode(response.body);
    
    if (data['success'] == true) {
      return data;
    }
    
    throw Exception(data['message'] ?? 'Erreur lors de la récupération du panier');
  }
}
```

### 10.3 Intercepteur HTTP (Gestion automatique des erreurs 401)

```dart
class ApiInterceptor {
  final AuthService _authService = AuthService();
  
  Future<http.Response> request(
    String method,
    Uri url,
    Map<String, String>? headers,
    Object? body,
  ) async {
    // Ajouter le token si disponible
    final authHeaders = await _authService.getHeaders();
    final finalHeaders = {...?headers, ...authHeaders};
    
    http.Response response;
    
    switch (method.toUpperCase()) {
      case 'GET':
        response = await http.get(url, headers: finalHeaders);
        break;
      case 'POST':
        response = await http.post(url, headers: finalHeaders, body: body);
        break;
      case 'PUT':
        response = await http.put(url, headers: finalHeaders, body: body);
        break;
      case 'DELETE':
        response = await http.delete(url, headers: finalHeaders);
        break;
      default:
        throw Exception('Méthode HTTP non supportée: $method');
    }
    
    // Gérer les erreurs 401
    if (response.statusCode == 401) {
      await _authService.logout();
      // Rediriger vers la page de connexion
      // Navigator.pushReplacementNamed(context, '/login');
      throw Exception('Session expirée. Veuillez vous reconnecter.');
    }
    
    return response;
  }
}
```

---

## 11. Notes importantes

### 11.1 Tokens

- Les tokens Sanctum n'expirent **pas** par défaut (`expiration: null`)
- Stocker le token de manière sécurisée (FlutterSecureStorage)
- Inclure le token dans toutes les requêtes authentifiées
- Supprimer le token lors de la déconnexion

### 11.2 CSRF

- Les routes API n'utilisent **pas** de protection CSRF
- Pas besoin d'envoyer de token CSRF pour les requêtes API
- Le CSRF est uniquement pour les routes web

### 11.3 CORS

- Assurez-vous que CORS est configuré pour accepter les requêtes depuis Flutter
- Vérifier `config/cors.php`

### 11.4 Gestion des erreurs

- Toujours vérifier `success: false` dans les réponses
- Gérer les erreurs 401 (token expiré)
- Afficher les messages d'erreur à l'utilisateur

---

## 12. Checklist pour Flutter

- [ ] Implémenter le service d'authentification
- [ ] Stocker le token de manière sécurisée
- [ ] Gérer le 2FA (si activé)
- [ ] Implémenter le service de panier
- [ ] Implémenter le service de commandes
- [ ] Implémenter le service de profil
- [ ] Gérer les erreurs 401 (déconnexion automatique)
- [ ] Afficher les messages d'erreur
- [ ] Tester toutes les routes
- [ ] Configurer CORS si nécessaire

---

## Conclusion

Le système hybride est **NÉCESSAIRE** et **FONCTIONNEL** pour supporter à la fois :
- ✅ Application web (sessions)
- ✅ Application Flutter (tokens)

Toutes les routes API sont maintenant prêtes pour Flutter avec support des tokens Sanctum.

