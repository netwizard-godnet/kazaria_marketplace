@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- BREADCRUMB -->
        <div class="container py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mon Panier</li>
                </ol>
            </nav>
        </div>

        <!-- SECTION PANIER -->
        <section class="container py-3">
            <div class="row gy-3">
                <!-- Articles du panier -->
                <div class="col-md-8">
                    <div class="bg-light rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-cart3 me-2"></i>
                                Mon Panier (<span id="cartItemsCount">0</span> article<span id="pluriel">s</span>)
                            </h5>
                            <button class="btn btn-sm btn-outline-danger" id="clearCartBtn" onclick="clearCart()" style="display: none;">
                                <i class="bi bi-trash me-1"></i>Vider le panier
                            </button>
                        </div>

                        <div id="cartItemsContainer">
                            <!-- Chargement en cours -->
                            <div class="text-center py-5" id="loadingCart">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                                <p class="mt-3 text-muted">Chargement de votre panier...</p>
                            </div>
                            <!-- Le contenu sera chargé dynamiquement via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Résumé du panier -->
                <div class="col-md-4">
                    <div class="bg-light p-3 rounded-3 position-sticky" style="top: 100px;">
                        <h6 class="mb-3 fw-bold text-uppercase">
                            <i class="bi bi-receipt me-2"></i>Résumé
                        </h6>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total:</span>
                            <span id="subtotal">0 FCFA</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Livraison:</span>
                            <span class="text-success">Gratuite</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold fs-5 orange-color" id="total">0 FCFA</span>
                        </div>

                        <button class="btn orange-bg text-white w-100 mb-2" id="checkoutBtn" style="display: none;" onclick="proceedToCheckout()">
                            <i class="bi bi-credit-card me-2"></i>Passer la commande
                        </button>
                        
                        <a href="{{ route('accueil') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left me-2"></i>Continuer mes achats
                        </a>

                        <!-- Codes promo -->
                        <div class="mt-3 p-3 border rounded">
                            <h6 class="mb-2 small fw-bold">Code promo</h6>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="Entrez votre code" id="promoCode">
                                <button class="btn orange-bg text-white" onclick="applyPromo()">
                                    <i class="bi bi-check"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Méthodes de paiement -->
                        <div class="mt-3">
                            <h6 class="small fw-bold mb-2">Paiement sécurisé</h6>
                            <div class="d-flex gap-2 align-items-center">
                                <img src="{{ asset('images/visa.jpg') }}" alt="Visa" height="30">
                                <img src="{{ asset('images/mastercard.jpg') }}" alt="Mastercard" height="30">
                                <i class="bi bi-phone-fill text-success" style="font-size: 1.5rem;" title="Mobile Money"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION END -->
    </main>

    <script>
        // Charger le panier au chargement de la page
        document.addEventListener('DOMContentLoaded', async function() {
            console.log('Page cart chargée');
            console.log('getHeaders est défini:', typeof getHeaders !== 'undefined');
            await loadCartPage();
        });

        // Fonction pour charger le panier
        async function loadCartPage() {
            try {
                console.log('loadCartPage - début');
                
                // Vérifier que getHeaders existe
                if (typeof getHeaders !== 'function') {
                    console.error('getHeaders n\'est pas définie! cart.js n\'est pas chargé');
                    showNotification('error', 'Erreur de chargement du script');
                    return;
                }
                
                const headers = getHeaders();
                console.log('Headers générés:', headers);
                
                const response = await fetch('/api/cart/', { headers });
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Data reçue:', data);
                
                const container = document.getElementById('cartItemsContainer');
                const loading = document.getElementById('loadingCart');
                const countSpan = document.getElementById('cartItemsCount');
                const pluriel = document.getElementById('pluriel');
                const clearBtn = document.getElementById('clearCartBtn');
                const checkoutBtn = document.getElementById('checkoutBtn');
                
                if (data.success && data.items.length > 0) {
                    loading.remove();
                    container.innerHTML = '';
                    
                    // Mettre à jour le compteur
                    countSpan.textContent = data.count;
                    pluriel.textContent = data.count > 1 ? 's' : '';
                    clearBtn.style.display = 'inline-block';
                    checkoutBtn.style.display = 'block';
                    
                    // Afficher chaque article
                    data.items.forEach(item => {
                        const product = item.product;
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'cart-item bg-white rounded-2 p-3 mb-3';
                        itemDiv.setAttribute('data-item-id', item.id);
                        
                        // Préparer l'URL de l'image
                        let imageUrl = '/images/produit.jpg'; // Par défaut
                        
                        if (product.image) {
                            if (product.image.startsWith('http')) {
                                imageUrl = product.image;
                            } else if (product.image.startsWith('/')) {
                                imageUrl = product.image;
                            } else {
                                imageUrl = '/' + product.image;
                            }
                        }
                        
                        itemDiv.innerHTML = `
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <img src="${imageUrl}" 
                                         class="img-fluid rounded" 
                                         style="max-height: 100px; object-fit: contain;" 
                                         alt="${product.name}"
                                         onerror="this.src='/images/produit.jpg'">
                                </div>
                                <div class="col-md-4">
                                    <a href="/produit/${product.slug}" class="text-decoration-none">
                                        <h6 class="mb-1 orange-color product-name-truncate" title="${product.name}">${product.name}</h6>
                                    </a>
                                    <p class="text-muted small mb-0">
                                        ${product.brand ? 'Marque: ' + product.brand : ''}
                                    </p>
                                </div>
                                <div class="col-md-2 text-center">
                                    ${product.old_price && product.old_price > product.price ? `
                                        <p class="mb-0 text-decoration-line-through text-muted small">${new Intl.NumberFormat('fr-FR').format(product.old_price)} FCFA</p>
                                        <p class="mb-0 fw-bold orange-color">${new Intl.NumberFormat('fr-FR').format(product.price)} FCFA</p>
                                    ` : `
                                        <p class="mb-0 fw-bold">${new Intl.NumberFormat('fr-FR').format(item.price)} FCFA</p>
                                    `}
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="input-group input-group-sm">
                                        <button class="btn orange-bg text-white" onclick="updateQuantity(${item.id}, -1)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="text" class="form-control text-center quantity-input" 
                                               id="quantity-${item.id}" 
                                               value="${item.quantity}" 
                                               readonly>
                                        <button class="btn orange-bg text-white" onclick="updateQuantity(${item.id}, 1)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <p class="mb-0 fw-bold item-total" id="item-total-${item.id}">
                                        ${new Intl.NumberFormat('fr-FR').format(item.price * item.quantity)} FCFA
                                    </p>
                                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="removeFromCart(${item.id})">
                                        <i class="bi bi-trash me-1"></i>Retirer
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        container.appendChild(itemDiv);
                    });
                    
                    // Mettre à jour les totaux
                    document.getElementById('subtotal').textContent = new Intl.NumberFormat('fr-FR').format(data.total) + ' FCFA';
                    document.getElementById('total').textContent = new Intl.NumberFormat('fr-FR').format(data.total) + ' FCFA';
                } else {
                    // Panier vide
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">Votre panier est vide</h5>
                            <p class="text-muted">Parcourez nos produits et ajoutez vos articles préférés !</p>
                            <a href="{{ route('accueil') }}" class="btn orange-bg text-white">
                                <i class="bi bi-shop me-2"></i>Continuer mes achats
                            </a>
                        </div>
                    `;
                    
                    // Réinitialiser le résumé à 0
                    const subtotal = document.getElementById('subtotal');
                    const total = document.getElementById('total');
                    const cartItemsCount = document.getElementById('cartItemsCount');
                    const pluriel = document.getElementById('pluriel');
                    
                    if (subtotal) {
                        subtotal.textContent = '0 FCFA';
                    }
                    
                    if (total) {
                        total.textContent = '0 FCFA';
                    }
                    
                    if (cartItemsCount) {
                        cartItemsCount.textContent = '0';
                    }
                    
                    if (pluriel) {
                        pluriel.textContent = '';
                    }
                    
                    // Cacher les boutons "Vider le panier" et "Passer la commande"
                    clearBtn.style.display = 'none';
                    checkoutBtn.style.display = 'none';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('loadingCart').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erreur lors du chargement du panier
                    </div>
                `;
            }
        }

        // Fonction pour mettre à jour la quantité
        async function updateQuantity(itemId, change) {
            console.log('updateQuantity appelée:', itemId, change);
            
            const quantityInput = document.getElementById('quantity-' + itemId);
            let currentQuantity = parseInt(quantityInput.value);
            let newQuantity = currentQuantity + change;
            
            console.log('Quantité actuelle:', currentQuantity, 'Nouvelle:', newQuantity);
            
            if (newQuantity < 1) {
                if (confirm('Voulez-vous retirer ce produit du panier ?')) {
                    removeFromCart(itemId);
                }
                return;
            }
            
            if (newQuantity > 100) {
                showNotification('error', 'Quantité maximale atteinte (100)');
                return;
            }
            
            try {
                const headers = getHeaders();
                console.log('Headers pour update:', headers);
                
                const response = await fetch(`/api/cart/update/${itemId}`, {
                    method: 'PUT',
                    headers: headers,
                    body: JSON.stringify({ quantity: newQuantity })
                });

                console.log('Status response:', response.status);
                const data = await response.json();
                console.log('Data reçue:', data);
                
                if (data.success) {
                    // Mettre à jour l'affichage
                    quantityInput.value = newQuantity;
                    
                    const itemTotal = document.getElementById('item-total-' + itemId);
                    const subtotal = document.getElementById('subtotal');
                    const total = document.getElementById('total');
                    
                    if (itemTotal) {
                        itemTotal.textContent = new Intl.NumberFormat('fr-FR').format(data.item_total) + ' FCFA';
                    }
                    
                    if (subtotal) {
                        subtotal.textContent = new Intl.NumberFormat('fr-FR').format(data.cart_total) + ' FCFA';
                    }
                    
                    if (total) {
                        total.textContent = new Intl.NumberFormat('fr-FR').format(data.cart_total) + ' FCFA';
                    }
                    
                    showNotification('success', 'Quantité mise à jour');
                } else {
                    showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Erreur complète:', error);
                showNotification('error', 'Erreur: ' + error.message);
            }
        }

        // Fonction pour retirer un article
        async function removeFromCart(itemId) {
            console.log('removeFromCart appelée:', itemId);
            
            try {
                const headers = getHeaders();
                console.log('Headers pour remove:', headers);
                
                const response = await fetch(`/api/cart/remove/${itemId}`, {
                    method: 'DELETE',
                    headers: headers
                });

                console.log('Status response:', response.status);
                const data = await response.json();
                console.log('Data reçue:', data);
                
                if (data.success) {
                    // Retirer l'élément du DOM
                    const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                    if (itemElement) {
                        itemElement.remove();
                    }
                    
                    // Mettre à jour les totaux
                    const cartItemsCount = document.getElementById('cartItemsCount');
                    const subtotal = document.getElementById('subtotal');
                    const total = document.getElementById('total');
                    const pluriel = document.getElementById('pluriel');
                    
                    if (cartItemsCount) {
                        cartItemsCount.textContent = data.cart_count;
                    }
                    
                    if (pluriel) {
                        pluriel.textContent = data.cart_count > 1 ? 's' : '';
                    }
                    
                    if (subtotal) {
                        subtotal.textContent = new Intl.NumberFormat('fr-FR').format(data.cart_total) + ' FCFA';
                    }
                    
                    if (total) {
                        total.textContent = new Intl.NumberFormat('fr-FR').format(data.cart_total) + ' FCFA';
                    }
                    
                    // Mettre à jour le compteur du header
                    updateCartCount(data.cart_count);
                    
                    showNotification('success', data.message);
                    
                    // Si le panier est vide, recharger la page
                    if (data.cart_count === 0) {
                        await loadCartPage();
                    }
                } else {
                    showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Erreur complète:', error);
                showNotification('error', 'Erreur: ' + error.message);
            }
        }

        // Fonction pour vider le panier
        async function clearCart() {
            if (!confirm('Êtes-vous sûr de vouloir vider votre panier ?')) {
                return;
            }
            
            try {
                const response = await fetch('/api/cart/clear', {
                    method: 'DELETE',
                    headers: getHeaders()
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('success', data.message);
                    await loadCartPage();
                    updateCartCount(0);
                } else {
                    showNotification('error', data.message);
                }
            } catch (error) {
                showNotification('error', 'Erreur de connexion');
            }
        }
        
        // Les fonctions getHeaders, showToast et showNotification sont maintenant globales via cart.js
        
        // Fonction pour procéder au checkout
        function proceedToCheckout() {
            const token = localStorage.getItem('auth_token');
            
            if (!token) {
                // Utilisateur non connecté, rediriger vers la page de connexion
                if (confirm('Vous devez vous connecter pour passer commande. Se connecter maintenant ?')) {
                    // Sauvegarder l'intention de checkout
                    localStorage.setItem('redirect_after_login', 'checkout');
                    window.location.href = '/authentification';
                }
                return;
            }
            
            // Utilisateur connecté, aller au checkout
            window.location.href = '/checkout?token=' + token;
        }
        
        // Fonction pour appliquer un code promo
        function applyPromo() {
            const promoCode = document.getElementById('promoCode').value;
            
            if (!promoCode) {
                showNotification('error', 'Veuillez entrer un code promo');
                return;
            }
            
            // TODO: Implémenter la logique de validation de code promo
            showNotification('error', 'Code promo invalide');
        }
    </script>
@endsection
