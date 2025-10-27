/**
 * Gestion de l'authentification côté client
 */

// Fonction pour initialiser les dropdowns Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser tous les dropdowns Bootstrap
    const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownElements.forEach(function(dropdownElement) {
        new bootstrap.Dropdown(dropdownElement);
    });
});

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

// Fonction pour obtenir l'ID de session
function getSessionId() {
    let sessionId = localStorage.getItem('guest_session_id');
    if (!sessionId) {
        sessionId = 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('guest_session_id', sessionId);
    }
    return sessionId;
}

// Fonction pour ajouter au panier
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

        const data = await response.json();
        
        if (data.success) {
            // Afficher notification
            if (window.showNotification) {
                window.showNotification('success', data.message);
            }
            
            // Mettre à jour le compteur du panier
            if (window.updateCartCount) {
                window.updateCartCount(data.cart_count);
            }
        } else {
            if (window.showNotification) {
                window.showNotification('error', data.message || 'Erreur lors de l\'ajout au panier');
            }
        }
    } catch (error) {
        console.error('Erreur complète:', error);
        if (window.showNotification) {
            window.showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
        }
    }
};

// Fonction pour basculer un favori
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
            const allButtons = document.querySelectorAll(`[data-product-id="${productId}"]`);
            allButtons.forEach(btn => {
                if (data.is_favorite) {
                    btn.classList.add('favorited');
                    btn.innerHTML = '<i class="fa-solid fa-heart"></i>';
                } else {
                    btn.classList.remove('favorited');
                    btn.innerHTML = '<i class="fa-regular fa-heart"></i>';
                }
            });
            
            // Mettre à jour le compteur des favoris
            if (window.updateFavoritesCount) {
                window.updateFavoritesCount(data.favorites_count);
            }
        }
    } catch (error) {
        console.error('Erreur lors du basculement du favori:', error);
    }
};

// Fonction pour mettre à jour le compteur du panier
window.updateCartCount = function(count) {
    const cartCountElements = document.querySelectorAll('.cart-count, .badge-cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
        element.classList.add('badge-pulse');
        setTimeout(() => element.classList.remove('badge-pulse'), 600);
    });
};

// Fonction pour aller aux favoris
window.goToFavorites = function() {
    window.location.href = '/profil#favorites';
};

// Fonction pour gérer la déconnexion
window.logout = function() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        // Créer un formulaire de déconnexion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
};

// Fonction pour vérifier l'état d'authentification
window.checkAuthStatus = function() {
    // Cette fonction peut être utilisée pour vérifier l'état d'authentification
    // Elle sera appelée par d'autres scripts si nécessaire
    return true;
};
