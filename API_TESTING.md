# 🧪 Guide de test des nouvelles API

## 🔐 Authentification

Toutes les requêtes nécessitant authentification doivent inclure :
```
Authorization: Bearer {votre_token}
```

---

## 1️⃣ Comparaison de produits

### Comparer des produits (sans sauvegarder)
```bash
POST /api/comparison/compare
Content-Type: application/json

{
  "product_ids": [1, 2, 3]
}
```

### Créer et sauvegarder une comparaison
```bash
POST /api/comparison
Content-Type: application/json

{
  "product_ids": [1, 2],
  "name": "iPhone vs Samsung"
}
```

### Obtenir l'historique des comparaisons
```bash
GET /api/comparison
```

### Obtenir une comparaison spécifique
```bash
GET /api/comparison/{id}
```

### Supprimer une comparaison
```bash
DELETE /api/comparison/{id}
```

---

## 2️⃣ Wishlists (Listes de souhaits)

### Créer une wishlist
```bash
POST /api/wishlists
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Ma liste de Noël",
  "description": "Cadeaux pour la famille",
  "is_public": false
}
```

### Obtenir toutes les wishlists
```bash
GET /api/wishlists
Authorization: Bearer {token}
```

### Obtenir une wishlist spécifique
```bash
GET /api/wishlists/{id}
Authorization: Bearer {token}
```

### Ajouter un produit à une wishlist
```bash
POST /api/wishlists/{id}/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 48,
  "priority": 5,
  "notes": "Couleur bleue préférée"
}
```

### Retirer un produit d'une wishlist
```bash
DELETE /api/wishlists/{id}/products/{productId}
Authorization: Bearer {token}
```

### Partager une wishlist (la rendre publique)
```bash
PUT /api/wishlists/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "is_public": true
}
```

### Voir une wishlist partagée (sans authentification)
```bash
GET /api/wishlists/shared/{share_token}
```

### Supprimer une wishlist
```bash
DELETE /api/wishlists/{id}
Authorization: Bearer {token}
```

---

## 3️⃣ Alertes de prix

### Créer une alerte de prix
```bash
POST /api/price-alerts
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 48,
  "target_price": 300000
}
```

### Obtenir toutes les alertes
```bash
GET /api/price-alerts
Authorization: Bearer {token}
```

### Supprimer une alerte
```bash
DELETE /api/price-alerts/{id}
Authorization: Bearer {token}
```

---

## 4️⃣ Historique des paiements

### Obtenir l'historique des paiements
```bash
GET /api/payments/history?page=1
Authorization: Bearer {token}
```

### Obtenir les détails d'un paiement
```bash
GET /api/payments/{id}
Authorization: Bearer {token}
```

---

## 5️⃣ Historique des factures

### Obtenir l'historique des factures
```bash
GET /api/invoices/history?page=1
Authorization: Bearer {token}
```

### Obtenir l'URL de téléchargement d'une facture
```bash
GET /api/invoices/{orderNumber}/download
Authorization: Bearer {token}

Réponse:
{
  "success": true,
  "invoice_url": "http://127.0.0.1:8000/order/download/CMD-000001",
  "order_number": "CMD-000001"
}
```

---

## 📱 Exemples de réponses

### Wishlist créée
```json
{
  "success": true,
  "message": "Wishlist créée avec succès",
  "wishlist": {
    "id": 1,
    "name": "Ma liste de Noël",
    "description": "Cadeaux pour la famille",
    "is_public": false,
    "share_token": "abc123...",
    "products_count": 0,
    "created_at": "2025-12-03T11:48:22.000000Z"
  }
}
```

### Comparaison de produits
```json
{
  "success": true,
  "products": [
    {
      "id": 48,
      "name": "iPhone 16",
      "price": 350000,
      "image": "http://...",
      "brand": "Apple",
      "attributes": {...}
    },
    {
      "id": 49,
      "name": "Samsung Galaxy S24",
      "price": 320000,
      ...
    }
  ]
}
```

### Alerte de prix créée
```json
{
  "success": true,
  "message": "Alerte de prix créée",
  "alert": {
    "id": 1,
    "product_id": 48,
    "target_price": 300000,
    "is_active": true,
    "created_at": "2025-12-03T11:48:22.000000Z",
    "product": {
      "id": 48,
      "name": "iPhone 16",
      "price": 350000,
      "image": "http://..."
    }
  }
}
```

---

## ✅ Checklist de test

- [ ] Créer une wishlist
- [ ] Ajouter 3 produits à la wishlist
- [ ] Partager la wishlist (rendre publique)
- [ ] Accéder à la wishlist via le token de partage
- [ ] Créer une alerte de prix pour un produit
- [ ] Comparer 2 produits
- [ ] Sauvegarder la comparaison
- [ ] Voir l'historique des comparaisons
- [ ] Voir l'historique des paiements
- [ ] Voir l'historique des factures
- [ ] Télécharger une facture

---

Généré le : 3 décembre 2025

