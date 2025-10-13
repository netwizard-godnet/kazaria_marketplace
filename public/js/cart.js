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
        'Accept': 'application/json'
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
window.addToCart = async function(productId, quantity = 1) {
    console.log('Ajout au panier - Produit ID:', productId, 'Quantité:', quantity);
    
    try {
        const headers = window.getHeaders();
        console.log('Headers:', headers);
        
        const response = await fetch('/api/cart/add', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        });

        console.log('Réponse status:', response.status);
        const data = await response.json();
        console.log('Données reçues:', data);
        
        if (data.success) {
            // Afficher notification
            window.showNotification('success', data.message);
            
            // Mettre à jour le compteur du panier
            window.updateCartCount(data.cart_count);
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
        const response = await fetch('/api/favorites/toggle', {
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
    const cartBadge = document.querySelector('.cart-count');
    if (cartBadge) {
        cartBadge.textContent = count || 0;
        
        // Animation de mise à jour
        cartBadge.classList.add('badge-pulse');
        setTimeout(() => cartBadge.classList.remove('badge-pulse'), 600);
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
    console.log('showNotification globale appelée:', type, message);
    
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
    console.log('✅ Alerte ajoutée au DOM');
    
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
            
            console.log('✅ États des favoris mis à jour:', favoriteIds.length, 'favoris');
        }
    } catch (error) {
        console.error('Erreur mise à jour boutons favoris:', error);
    }
};

// Charger les compteurs au chargement de la page
document.addEventListener('DOMContentLoaded', async function() {
    console.log('cart.js - DOMContentLoaded - Chargement des compteurs');
    
    try {
        // Charger le nombre d'articles dans le panier
        const cartResponse = await fetch('/api/cart/', {
            headers: window.getHeaders()
        });
        const cartData = await cartResponse.json();
        
        console.log('Panier chargé:', cartData);
        
        if (cartData.success) {
            window.updateCartCount(cartData.count);
        }
        
        // Charger le nombre de favoris
        const favoritesResponse = await fetch('/api/favorites/', {
            headers: window.getHeaders()
        });
        const favoritesData = await favoritesResponse.json();
        
        console.log('Favoris chargés:', favoritesData);
        
        if (favoritesData.success) {
            window.updateFavoritesCount(favoritesData.favorites.length);
        }
        
        // Mettre à jour l'état visuel de tous les boutons favoris
        await window.updateAllFavoriteButtons();
        
    } catch (error) {
        console.error('Erreur lors du chargement des compteurs:', error);
    }
});

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

