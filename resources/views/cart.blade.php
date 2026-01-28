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
                            <span id="subtotal">0 {{ $settings['currency_symbol'] ?? 'FCFA' }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Livraison:</span>
                            <span id="shippingCost" class="text-success"></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2" id="promoRow" style="display:none;">
                            <span>Réduction (code):</span>
                            <span id="promoDiscount">- 0 {{ $settings['currency_symbol'] ?? 'FCFA' }}</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold fs-5 orange-color" id="total">0 {{ $settings['currency_symbol'] ?? 'FCFA' }}</span>
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
                
                const response = await fetch('/cart/get', { headers });
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Data reçue:', data);
                // Debug des attributs
                if (data.success && data.items && data.items.length > 0) {
                    data.items.forEach((item, index) => {
                        console.log(`Item ${index} - Product:`, item.product?.name);
                        console.log(`Item ${index} - Attributes:`, item.attributes);
                        console.log(`Item ${index} - Attributes type:`, typeof item.attributes);
                        console.log(`Item ${index} - Attributes is array:`, Array.isArray(item.attributes));
                        if (item.attributes && typeof item.attributes === 'object') {
                            console.log(`Item ${index} - Attributes keys:`, Object.keys(item.attributes));
                            console.log(`Item ${index} - Attributes entries:`, Object.entries(item.attributes));
                        }
                    });
                }
                
                const container = document.getElementById('cartItemsContainer');
                const loading = document.getElementById('loadingCart');
                const countSpan = document.getElementById('cartItemsCount');
                const pluriel = document.getElementById('pluriel');
                const clearBtn = document.getElementById('clearCartBtn');
                const checkoutBtn = document.getElementById('checkoutBtn');
                
                // Vérifier que les éléments essentiels existent
                if (!container) {
                    console.error('cartItemsContainer non trouvé');
                    return;
                }
                
                if (data.success && data.items.length > 0) {
                    // Retirer l'élément de chargement s'il existe
                    if (loading) {
                        loading.remove();
                    }
                    container.innerHTML = '';
                    
                    // Mettre à jour le compteur
                    if (countSpan) {
                        countSpan.textContent = data.count;
                    }
                    if (pluriel) {
                        pluriel.textContent = data.count > 1 ? 's' : '';
                    }
                    if (clearBtn) {
                        clearBtn.style.display = 'inline-block';
                    }
                    if (checkoutBtn) {
                        checkoutBtn.style.display = 'block';
                    }
                    
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
                                    ${(() => {
                                        let html = '';
                                        
                                        // Afficher les informations de la variation si elle existe
                                        if (item.variation && item.variation.attribute_values && item.variation.attribute_values.length > 0) {
                                            // Grouper les valeurs d'attributs par nom d'attribut
                                            const groupedAttrs = {};
                                            item.variation.attribute_values.forEach(attrValue => {
                                                const attrName = attrValue.attribute?.name || 'Attribut';
                                                if (!groupedAttrs[attrName]) {
                                                    groupedAttrs[attrName] = [];
                                                }
                                                groupedAttrs[attrName].push(attrValue.value);
                                            });
                                            
                                            html += '<div class="mt-2">';
                                            Object.entries(groupedAttrs).forEach(([attrName, values]) => {
                                                html += `
                                                    <div class="mb-1">
                                                        <small class="text-muted fw-bold">${attrName}:</small>
                                                        <small class="text-primary"> ${values.join(', ')}</small>
                                                    </div>
                                                `;
                                            });
                                            html += '</div>';
                                        } else if (item.attributes) {
                                            // Fallback : afficher les attributs stockés dans le champ attributes
                                            if (Array.isArray(item.attributes) && item.attributes.length === 0) {
                                                return '';
                                            }
                                            if (typeof item.attributes === 'object' && Object.keys(item.attributes).length === 0) {
                                                return '';
                                            }
                                            
                                            const attrs = Array.isArray(item.attributes) ? {} : item.attributes;
                                            const entries = Array.isArray(item.attributes) ? [] : Object.entries(attrs);
                                            
                                            if (entries.length > 0) {
                                                html += '<div class="mt-2">';
                                                entries.forEach(([attrName, values]) => {
                                                    html += `
                                                        <div class="mb-1">
                                                            <small class="text-muted fw-bold">${attrName}:</small>
                                                            <small class="text-primary"> ${Array.isArray(values) ? values.join(', ') : values}</small>
                                                        </div>
                                                    `;
                                                });
                                                html += '</div>';
                                            }
                                        }
                                        
                                        return html;
                                    })()}
                                </div>
                                <div class="col-md-2 text-center">
                                    ${product.old_price && product.old_price > product.price ? `
                                        <p class="mb-0 text-decoration-line-through text-muted small">${new Intl.NumberFormat('fr-FR').format(product.old_price)} ${settings.currencySymbol}</p>
                                        <p class="mb-0 fw-bold orange-color">${new Intl.NumberFormat('fr-FR').format(product.price)} ${settings.currencySymbol}</p>
                                    ` : `
                                        <p class="mb-0 fw-bold">${new Intl.NumberFormat('fr-FR').format(item.price)} ${settings.currencySymbol}</p>
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
                                        ${new Intl.NumberFormat('fr-FR').format(item.price * item.quantity)} ${settings.currencySymbol}
                                    </p>
                                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="removeFromCart(${item.id})">
                                        <i class="bi bi-trash me-1"></i>Retirer
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        container.appendChild(itemDiv);
                    });
                    
                    // Mettre à jour les totaux avec calcul de livraison
                    updateTotals(data.total);
                } else {
                    // Panier vide
                    // Retirer l'élément de chargement s'il existe
                    if (loading) {
                        loading.remove();
                    }
                    
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
                    updateTotals(0);
                    
                    if (countSpan) {
                        countSpan.textContent = '0';
                    }
                    
                    if (pluriel) {
                        pluriel.textContent = '';
                    }
                    
                    // Cacher les boutons "Vider le panier" et "Passer la commande"
                    if (clearBtn) {
                        clearBtn.style.display = 'none';
                    }
                    if (checkoutBtn) {
                        checkoutBtn.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                const loadingElement = document.getElementById('loadingCart');
                const containerElement = document.getElementById('cartItemsContainer');
                
                if (loadingElement) {
                    loadingElement.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erreur lors du chargement du panier
                        </div>
                    `;
                } else if (containerElement) {
                    containerElement.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erreur lors du chargement du panier
                        </div>
                    `;
                } else {
                    showNotification('error', 'Erreur lors du chargement du panier');
                }
            }
        }

        // Configuration des paramètres ({{ now() }})
        console.log('Debug $shippingSettings:', {
            isset: {{ isset($shippingSettings) ? 'true' : 'false' }},
            is_array: {{ isset($shippingSettings) && is_array($shippingSettings) ? 'true' : 'false' }},
            content: @json($shippingSettings ?? 'UNDEFINED')
        });
        
        @if(isset($shippingSettings) && is_array($shippingSettings))
            console.log('ShippingSettings reçus:', @json($shippingSettings));
            const settings = {
                minOrderQuantity: {{ $shippingSettings['min_order_quantity'] ?? 1 }},
                currencySymbol: '{{ $shippingSettings['currency_symbol'] ?? 'FCFA' }}',
                shippingCost: {{ $shippingSettings['shipping_cost'] ?? 0 }},
                freeShippingThreshold: {{ $shippingSettings['free_shipping_threshold'] ?? 0 }}
            };
        @else
            console.error('ShippingSettings non définis ou invalides');
            const settings = {
                minOrderQuantity: 1,
                currencySymbol: 'FCFA',
                shippingCost: 0,
                freeShippingThreshold: 0
            };
        @endif
        
        // Debug: Afficher les paramètres dans la console
        console.log('Paramètres de livraison chargés:', settings);

        // Fonction pour calculer la livraison
        function calculateShipping(subtotal) {
            console.log('Calcul de livraison:', {
                subtotal: subtotal,
                freeShippingThreshold: settings.freeShippingThreshold,
                shippingCost: settings.shippingCost,
                comparison: subtotal + ' >= ' + settings.freeShippingThreshold + ' = ' + (subtotal >= settings.freeShippingThreshold)
            });
            
            if (subtotal >= settings.freeShippingThreshold) {
                console.log('Livraison GRATUITE car subtotal >= seuil');
                return { cost: 0, isFree: true };
            } else {
                console.log('Livraison PAYANTE:', settings.shippingCost);
                return { cost: settings.shippingCost, isFree: false };
            }
        }

        // Etat courant promo
        let appliedPromo = null; // { code, percent }
        let currentSubtotal = 0;

        // Fonction pour mettre à jour les totaux avec livraison
        function updateTotals(subtotal) {
            console.log('updateTotals appelée avec subtotal:', subtotal);
            currentSubtotal = subtotal;
            const shipping = calculateShipping(subtotal);
            let discountAmount = 0;
            
            const promoRow = document.getElementById('promoRow');
            const promoDiscount = document.getElementById('promoDiscount');
            const subtotalElement = document.getElementById('subtotal');
            const shippingElement = document.getElementById('shippingCost');
            const totalElement = document.getElementById('total');
            
            if (appliedPromo && appliedPromo.percent > 0) {
                discountAmount = Math.round(subtotal * appliedPromo.percent / 100);
                if (promoRow) {
                    promoRow.style.display = 'flex';
                }
                if (promoDiscount) {
                    promoDiscount.textContent = '- ' + new Intl.NumberFormat('fr-FR').format(discountAmount) + ' ' + settings.currencySymbol;
                }
            } else {
                if (promoRow) {
                    promoRow.style.display = 'none';
                }
            }
            const total = Math.max(0, subtotal - discountAmount) + shipping.cost;
            
            // Mettre à jour le sous-total
            if (subtotalElement) {
                subtotalElement.textContent = new Intl.NumberFormat('fr-FR').format(subtotal) + ' ' + settings.currencySymbol;
            }
            
            // Mettre à jour la livraison
            if (shippingElement) {
                if (shipping.isFree) {
                    shippingElement.textContent = 'Gratuite';
                    shippingElement.className = 'text-success';
                } else {
                    shippingElement.textContent = new Intl.NumberFormat('fr-FR').format(shipping.cost) + ' ' + settings.currencySymbol;
                    shippingElement.className = 'text-muted';
                }
            }
            
            // Mettre à jour le total
            if (totalElement) {
                totalElement.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' ' + settings.currencySymbol;
            }
        }

        // Fonction pour mettre à jour la quantité
        async function updateQuantity(itemId, change) {
            console.log('updateQuantity appelée:', itemId, change);
            
            const quantityInput = document.getElementById('quantity-' + itemId);
            let currentQuantity = parseInt(quantityInput.value);
            let newQuantity = currentQuantity + change;
            
            console.log('Quantité actuelle:', currentQuantity, 'Nouvelle:', newQuantity);
            
            if (newQuantity < settings.minOrderQuantity) {
                showNotification('error', `Quantité minimale de commande : ${settings.minOrderQuantity}`);
                return;
            }
            
            if (newQuantity > 100) {
                showNotification('error', 'Quantité maximale atteinte (100)');
                return;
            }
            
            try {
                const headers = getHeaders();
                console.log('Headers pour update:', headers);
                
                const response = await fetch(`/cart/update`, {
                    method: 'PUT',
                    headers: headers,
                    body: JSON.stringify({ 
                        item_id: itemId,
                        quantity: newQuantity 
                    })
                });

                console.log('Status response:', response.status);
                const data = await response.json();
                console.log('Data reçue:', data);
                
                if (data.success) {
                    // Mettre à jour l'affichage
                    quantityInput.value = newQuantity;
                    
                    const itemTotal = document.getElementById('item-total-' + itemId);
                    
                    if (itemTotal) {
                        itemTotal.textContent = new Intl.NumberFormat('fr-FR').format(data.item_total) + ' ' + settings.currencySymbol;
                    }
                    
                    // Mettre à jour les totaux avec calcul de livraison
                    updateTotals(data.cart_total);
                    
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
                
                const response = await fetch(`/cart/remove`, {
                    method: 'DELETE',
                    headers: headers,
                    body: JSON.stringify({
                        item_id: itemId
                    })
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
                    const pluriel = document.getElementById('pluriel');
                    
                    if (cartItemsCount) {
                        cartItemsCount.textContent = data.cart_count;
                    }
                    
                    if (pluriel) {
                        pluriel.textContent = data.cart_count > 1 ? 's' : '';
                    }
                    
                    // Mettre à jour les totaux avec calcul de livraison
                    updateTotals(data.cart_total);
                    
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
                const headers = getHeaders();
                console.log('Headers pour clearCart:', headers);
                
                const response = await fetch('/cart/clear', {
                    method: 'DELETE',
                    headers: headers
                });

                console.log('Response status:', response.status);
                
                // Vérifier si la réponse est OK
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Erreur serveur:', response.status, errorText);
                    showNotification('error', `Erreur ${response.status}: ${errorText || 'Erreur lors de la suppression du panier'}`);
                    return;
                }

                const data = await response.json();
                console.log('Data reçue:', data);
                
                if (data.success) {
                    showNotification('success', data.message);
                    await loadCartPage();
                    updateCartCount(0);
                } else {
                    showNotification('error', data.message || 'Erreur lors de la suppression du panier');
                }
            } catch (error) {
                console.error('Erreur complète:', error);
                showNotification('error', 'Erreur de connexion: ' + error.message);
            }
        }
        
        // Fonction globale pour obtenir le session_id (doit être accessible partout)
        window.getSessionId = function() {
            let sessionId = localStorage.getItem('guest_session_id');
            if (!sessionId) {
                sessionId = 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('guest_session_id', sessionId);
            }
            return sessionId;
        };
        
        // S'assurer que getHeaders est disponible localement si cart.js n'est pas chargé
        if (typeof window.getHeaders === 'undefined') {
            window.getHeaders = function() {
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                };
                
                const token = localStorage.getItem('auth_token');
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                } else {
                    headers['X-Session-ID'] = window.getSessionId();
                }
                
                return headers;
            };
        }
        
        // Fonction pour procéder au checkout
        function proceedToCheckout() {
            const token = localStorage.getItem('auth_token');
            const sessionId = window.getSessionId();
            
            // Construire l'URL avec le session_id pour les invités
            let checkoutUrl = '/checkout';
            if (!token && sessionId) {
                // Pour les invités, passer le session_id en paramètre
                checkoutUrl += '?session_id=' + encodeURIComponent(sessionId);
            } else if (token) {
                // Pour les utilisateurs connectés, passer le token
                checkoutUrl += '?token=' + encodeURIComponent(token);
            }
            
            window.location.href = checkoutUrl;
        }
        
        // Fonction pour appliquer un code promo
        async function applyPromo() {
            const promoCode = document.getElementById('promoCode').value.trim();
            if (!promoCode) {
                showNotification('error', 'Veuillez entrer un code promo');
                return;
            }
            try {
                const response = await fetch('/api/coupons/apply', {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ code: promoCode, subtotal: currentSubtotal })
                });
                const data = await response.json();
                if (data.success) {
                    appliedPromo = { code: data.code, percent: data.discount_percent };
                    updateTotals(currentSubtotal);
                    showNotification('success', `Code appliqué (${data.discount_percent}%)`);
                } else {
                    showNotification('error', data.message || 'Code promo invalide');
                }
            } catch (e) {
                showNotification('error', 'Erreur lors de la vérification du code');
            }
        }
    </script>
@endsection
