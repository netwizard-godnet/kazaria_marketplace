// Gestionnaire de panier et favoris KAZARIA

// Obtenir ou créer un ID de session pour les invités
function getSessionId() {
    let sessionId = localStorage.getItem('guest_session_id');
    if (!sessionId) {
        sessionId = 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('guest_session_id', sessionId);
    }
    return sessionId;
}

// Obtenir les headers avec authentification ou session (fonction globale)
window.getHeaders = function() {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    };
    
    // Ajouter le token si l'utilisateur est connecté
    const token = localStorage.getItem('auth_token');
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    } else {
        // Ajouter l'ID de session pour les invités
        headers['X-Session-ID'] = getSessionId();
    }
    
    return headers;
};

// Fonction pour ajouter un produit au panier (globale)
window.addToCart = async function(productId, quantity = 1, attributes = {}) {
    try {
        const headers = window.getHeaders();
        
        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
                attributes: attributes
            })
        });
        
        // Si erreur 419, rafraîchir le token CSRF et réessayer
        if (response.status === 419) {
            // Ne pas recharger automatiquement pour éviter les boucles
            window.showNotification('error', 'Erreur de session. Veuillez recharger la page et réessayer.');
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Afficher notification
            window.showNotification('success', data.message);
            
            // Mettre à jour le compteur du panier
            window.updateCartCount(data.cart_count);
            
            // Démarrer le timer de rappel panier (affichera le pop-up après 5 secondes)
            if (window.startCartReminderTimer) {
                window.startCartReminderTimer();
            }
        } else {
            window.showNotification('error', data.message || 'Erreur lors de l\'ajout au panier');
        }
    } catch (error) {
        console.error('Erreur complète:', error);
        window.showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    }
};

// Fonction pour basculer un favori (globale)
window.toggleFavorite = async function(productId, button) {
    try {
        const response = await fetch('/favorites/toggle', {
            method: 'POST',
            headers: window.getHeaders(),
            body: JSON.stringify({
                product_id: productId
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Mettre à jour TOUS les boutons favoris de ce produit sur la page
            document.querySelectorAll(`.favorite-btn[data-product-id="${productId}"]`).forEach(btn => {
                const icon = btn.querySelector('i');
                if (data.is_favorite) {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    btn.classList.add('text-danger');
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    btn.classList.remove('text-danger');
                }
            });
            
            // Afficher notification
            window.showNotification('success', data.message);
            
            // Mettre à jour le compteur des favoris
            window.updateFavoritesCount(data.favorites_count);
        } else {
            window.showNotification('error', data.message || 'Erreur lors de l\'ajout aux favoris');
        }
    } catch (error) {
        console.error('Erreur:', error);
        window.showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    }
};

// Mettre à jour le compteur du panier dans le header (globale)
window.updateCartCount = function(count) {
    const countValue = count || 0;
    
    // Mettre à jour tous les badges avec la classe cart-count
    const cartBadges = document.querySelectorAll('.cart-count');
    cartBadges.forEach(badge => {
        badge.textContent = countValue;
        // Animation de mise à jour
        badge.classList.add('badge-pulse');
        setTimeout(() => badge.classList.remove('badge-pulse'), 600);
    });
    
    // Mettre à jour aussi le badge dans le footer mobile par ID
    const cartBadgeFooter = document.getElementById('cartBadge');
    if (cartBadgeFooter) {
        cartBadgeFooter.textContent = countValue;
        if (countValue > 0) {
            cartBadgeFooter.style.display = 'block';
        } else {
            cartBadgeFooter.style.display = 'none';
        }
    }
};

// Mettre à jour le compteur des favoris dans le header (globale)
window.updateFavoritesCount = function(count) {
    const favoritesBadge = document.querySelector('.favorites-count');
    if (favoritesBadge) {
        favoritesBadge.textContent = count || 0;
        
        // Animation de mise à jour
        favoritesBadge.classList.add('badge-pulse');
        setTimeout(() => favoritesBadge.classList.remove('badge-pulse'), 600);
    }
};

// Fonction globale pour afficher une notification
window.showNotification = function(type, message) {
    
    const alertContainer = document.getElementById('alertContainer');
    
    if (!alertContainer) {
        console.error('alertContainer non trouvé!');
        alert((type === 'success' ? '✅ ' : '❌ ') + message);
        return;
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    
    const icon = type === 'success' 
        ? '<i class="bi bi-check-circle-fill me-2"></i>' 
        : '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
    
    alertDiv.innerHTML = `
        ${icon}
        <strong>${type === 'success' ? 'Succès!' : 'Erreur!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Supprimer après 5 secondes
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
};

// Alias pour compatibilité
window.showToast = window.showNotification;

// Vérifier si un produit est dans les favoris
window.checkFavoriteStatus = async function(productId) {
    try {
        const favoritesResponse = await fetch('/api/favorites/', {
            headers: window.getHeaders()
        });
        const favoritesData = await favoritesResponse.json();
        
        if (favoritesData.success) {
            const favoriteIds = favoritesData.favorites.map(f => f.product_id);
            return favoriteIds.includes(productId);
        }
        return false;
    } catch (error) {
        console.error('Erreur vérification favori:', error);
        return false;
    }
};

// Mettre à jour l'état visuel de tous les boutons favoris
window.updateAllFavoriteButtons = async function() {
    try {
        const favoritesResponse = await fetch('/api/favorites/', {
            headers: window.getHeaders()
        });
        const favoritesData = await favoritesResponse.json();
        
        if (favoritesData.success) {
            const favoriteIds = favoritesData.favorites.map(f => f.product_id);
            
            // Parcourir tous les boutons favoris sur la page
            document.querySelectorAll('.favorite-btn').forEach(button => {
                const productId = parseInt(button.dataset.productId);
                const icon = button.querySelector('i');
                
                if (favoriteIds.includes(productId)) {
                    // Produit est dans les favoris → Cœur plein rouge
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    button.classList.add('text-danger');
                } else {
                    // Produit n'est pas dans les favoris → Cœur vide
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    button.classList.remove('text-danger');
                }
            });
            
        }
    } catch (error) {
        console.error('Erreur mise à jour boutons favoris:', error);
    }
};

// Charger les compteurs au chargement de la page
document.addEventListener('DOMContentLoaded', async function() {
    try {
        // Charger le nombre d'articles dans le panier
        const cartResponse = await fetch('/cart/get', {
            headers: window.getHeaders()
        });
        const cartData = await cartResponse.json();
        
        if (cartData.success) {
            window.updateCartCount(cartData.count);
        }
        
        // Charger le nombre de favoris
        const favoritesResponse = await fetch('/favorites/', {
            headers: window.getHeaders()
        });
        const favoritesData = await favoritesResponse.json();
        
        if (favoritesData.success) {
            window.updateFavoritesCount(favoritesData.favorites.length);
        }
        
        // Mettre à jour l'état visuel de tous les boutons favoris
        await window.updateAllFavoriteButtons();
        
    } catch (error) {
        console.error('Erreur lors du chargement des compteurs:', error);
    }
});

// Fonction pour aller aux favoris
window.goToFavorites = function() {
    window.location.href = '/profil#favorites';
};

// Animation pour le badge
const style = document.createElement('style');
style.textContent = `
    @keyframes badge-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    .badge-pulse {
        animation: badge-pulse 0.6s ease-in-out;
    }
`;
document.head.appendChild(style);

// ==============================
// Rappel panier après ajout de produit
// ==============================
(function() {
    const REMINDER_DELAY_MS = 2 * 60 * 1000; // 2 minutes après l'ajout d'un produit
    const MIN_INTERVAL_BETWEEN_REMINDERS_MS = 2 * 60 * 1000; // 2 minutes entre deux rappels
    let reminderTimer = null;

    function isOnCartOrCheckout() {
        const path = window.location.pathname;
        return path.startsWith('/panier') || path.includes('/cart') || path.includes('/checkout');
    }

    async function fetchCartCountSafe() {
        try {
            const response = await fetch('/cart/get', { headers: window.getHeaders ? window.getHeaders() : {} });
            if (!response.ok) return 0;
            const data = await response.json();
            return data && data.success ? (data.count || 0) : 0;
        } catch (e) {
            return 0;
        }
    }

    async function fetchCartItemsSafe() {
        try {
            const response = await fetch('/cart/get', { headers: window.getHeaders ? window.getHeaders() : {} });
            if (!response.ok) return [];
            const data = await response.json();
            // La méthode getCart retourne 'items' et non 'cart_items'
            return data && data.success ? (data.items || []) : [];
        } catch (e) {
            return [];
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XOF',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price).replace('XOF', 'FCFA');
    }

    function renderCartProducts(products) {
        const container = document.getElementById('cartReminderProducts');
        if (!container) return;

        if (!products || products.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">Aucun produit dans le panier</p>';
            return;
        }

        const productsHTML = products.map(item => {
            // Gérer différentes structures de données possibles
            const product = item.product || item.product_id ? { 
                image: item.product?.image || item.product_image || '',
                name: item.product?.name || item.product_name || 'Produit'
            } : null;
            
            const imageUrl = product?.image 
                ? (product.image.startsWith('http') ? product.image : 
                   (product.image.startsWith('/') ? product.image : `/storage/${product.image}`))
                : '/images/placeholder.png';
            const productName = product?.name || item.product_name || 'Produit';
            const quantity = item.quantity || 1;
            // Le prix peut être dans item.price ou item.product.price
            const price = item.price || item.product?.price || 0;
            const totalPrice = price * quantity;

            return `
                <div class="cart-reminder-product-item">
                    <img src="${imageUrl}" alt="${productName}" class="cart-reminder-product-image" onerror="this.src='/images/placeholder.png'">
                    <div class="cart-reminder-product-info">
                        <p class="cart-reminder-product-name">${productName}</p>
                        <p class="cart-reminder-product-details">Quantité: ${quantity}</p>
                    </div>
                    <div class="cart-reminder-product-price">
                        ${formatPrice(totalPrice)}
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = productsHTML;
    }

    function shouldShowReminder() {
        try {
            const lastShown = localStorage.getItem('cart_reminder_last_shown');
            if (!lastShown) return true;
            const last = parseInt(lastShown, 10) || 0;
            return (Date.now() - last) > MIN_INTERVAL_BETWEEN_REMINDERS_MS;
        } catch (e) {
            return true;
        }
    }

    function markReminderShown() {
        try {
            localStorage.setItem('cart_reminder_last_shown', String(Date.now()));
        } catch (e) {}
    }

    function hideCartReminderPopup() {
        const popup = document.getElementById('cartReminderPopup');
        if (popup) {
            popup.classList.add('hiding');
            setTimeout(() => {
                popup.style.display = 'none';
                popup.classList.remove('hiding');
            }, 300);
            // Annuler le timer si le pop-up est fermé manuellement
            if (reminderTimer) {
                clearTimeout(reminderTimer);
                reminderTimer = null;
            }
        }
    }

    // Rendre la fonction accessible globalement
    window.hideCartReminderPopup = hideCartReminderPopup;

    function showCartReminderPopup() {
        const popup = document.getElementById('cartReminderPopup');
        if (popup) {
            popup.style.display = 'block';
            // Vibration légère sur mobile si disponible
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }
        }
    }

    async function checkCartAndRemind() {
        if (isOnCartOrCheckout()) {
            // Si on est sur le panier/checkout, annuler le timer
            if (reminderTimer) {
                clearTimeout(reminderTimer);
                reminderTimer = null;
            }
            return;
        }
        
        const count = await fetchCartCountSafe();
        if (count > 0 && shouldShowReminder()) {
            // Charger les produits du panier
            const products = await fetchCartItemsSafe();
            
            // Mettre à jour le compteur
            const countBadge = document.getElementById('cartReminderCount');
            if (countBadge) {
                countBadge.textContent = count;
            }
            
            // Rendre les produits
            renderCartProducts(products);
            
            // Afficher le pop-up
            showCartReminderPopup();
            
            // Marquer comme affiché
            markReminderShown();
        }
        
        // Réinitialiser le timer pour qu'il ne soit plus actif
        reminderTimer = null;
    }

    // Démarrer le timer après l'ajout d'un produit au panier
    function startCartReminderTimer() {
        // Annuler tout timer existant
        if (reminderTimer) {
            clearTimeout(reminderTimer);
        }
        
        // Ne pas afficher le rappel si on est déjà sur le panier/checkout
        if (isOnCartOrCheckout()) {
            return;
        }
        
        // Démarrer le timer
        reminderTimer = setTimeout(checkCartAndRemind, REMINDER_DELAY_MS);
    }

    // Fonction globale pour démarrer le timer (appelée après ajout au panier)
    window.startCartReminderTimer = startCartReminderTimer;

    // Vérifier au chargement si le panier a des produits et démarrer le timer si nécessaire
    async function checkCartOnLoad() {
        if (isOnCartOrCheckout()) return;
        
        const count = await fetchCartCountSafe();
        if (count > 0 && shouldShowReminder()) {
            // Démarrer le timer si le panier contient des produits
            startCartReminderTimer();
        }
    }

    // Initialisation après chargement DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier le panier au chargement de la page
        checkCartOnLoad();
        
        // Gestion de la fermeture du pop-up
        const closeBtn = document.getElementById('closeCartReminder');
        if (closeBtn) {
            closeBtn.addEventListener('click', hideCartReminderPopup);
        }

        // Fermer le pop-up si on clique en dehors (optionnel, peut être retiré)
        const popup = document.getElementById('cartReminderPopup');
        if (popup) {
            popup.addEventListener('click', function(e) {
                if (e.target === popup) {
                    hideCartReminderPopup();
                }
            });
        }
    });
})();

