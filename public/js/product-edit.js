/**
 * Système de modification de produit - VERSION REFACTORISÉE
 * Dashboard vendeur - Section produits
 */

// Variables globales
let currentProduct = null;
let editModal = null;

/**
 * Éditer un produit - Fonction principale
 */
window.editProductInternal = async function(id) {
    try {
        // Afficher un loader
        showNotification('info', 'Chargement du produit...');
        
        const response = await fetch(`/store/api/products/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Erreur lors de la récupération du produit');
        }
        
        const data = await response.json();
        
        if (data.success && data.product) {
            currentProduct = data.product;
            showEditProductModal(data.product);
        } else {
            throw new Error(data.message || 'Produit non trouvé');
        }
    } catch (error) {
        showNotification('error', error.message || 'Erreur lors du chargement du produit');
    }
}

/**
 * Afficher le modal d'édition - VERSION REFACTORISÉE
 */
function showEditProductModal(product) {
    
    // Supprimer l'ancien modal s'il existe
    const existingModal = document.getElementById('editProductModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade z-index-9x" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editProductModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Modifier le produit
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editProductForm" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="${product.id}">
                            
                            <!-- Informations de base -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-info-circle me-2"></i>Informations de base
                                    </h6>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Nom du produit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="${product.name || ''}" required>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="4" required>${product.description || ''}</textarea>
                                </div>
                            </div>
                            
                            <!-- Prix et stock -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-currency-exchange me-2"></i>Prix et stock
                                    </h6>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Prix normal (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price" value="${product.price || ''}" min="0" step="0.01" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Stock disponible <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="stock" value="${product.stock || ''}" min="0" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Prix promo (FCFA)</label>
                                    <input type="number" class="form-control" name="promo_price" value="${product.promo_price || ''}" min="0" step="0.01" placeholder="Prix après réduction">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Réduction (%)</label>
                                    <input type="number" class="form-control" name="discount" value="${product.discount || ''}" min="0" max="100" placeholder="Pourcentage de réduction">
                                </div>
                            </div>
                            
                            <!-- Détails du produit -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-tags me-2"></i>Détails du produit
                                    </h6>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Marque</label>
                                    <input type="text" class="form-control" name="brand" value="${product.brand || ''}" placeholder="Ex: Samsung">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Modèle</label>
                                    <input type="text" class="form-control" name="model" value="${product.model || ''}" placeholder="Ex: Galaxy S25 Ultra">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Garantie</label>
                                    <input type="text" class="form-control" name="warranty" value="${product.warranty || ''}" placeholder="Ex: 2 ans">
                                </div>
                            </div>
                            
                            <!-- Images -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-images me-2"></i>Images du produit
                                    </h6>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    ${product.image ? `
                                        <div class="alert alert-info">
                                            <div class="d-flex align-items-center">
                                                <img src="/storage/${product.image}" alt="${product.name}" 
                                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" 
                                                     class="me-3">
                                                <div>
                                                    <strong>Image principale actuelle</strong><br>
                                                    <small class="text-muted">Sélectionnez une nouvelle image pour la remplacer</small>
                                                </div>
                                            </div>
                                        </div>
                                    ` : ''}
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nouvelle image principale</label>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                        <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 5MB</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Images supplémentaires</label>
                                        <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                                        <small class="text-muted">Maximum 5 images supplémentaires</small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-primary" onclick="updateProduct(${product.id})">
                            <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter le modal au DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Initialiser et afficher le modal
    const modalElement = document.getElementById('editProductModal');
    editModal = new bootstrap.Modal(modalElement);
    
    // Nettoyer le modal quand il est fermé
    modalElement.addEventListener('hidden.bs.modal', function() {
        this.remove();
        currentProduct = null;
        editModal = null;
    });
    
    editModal.show();
}

/**
 * Mettre à jour un produit - VERSION REFACTORISÉE
 */
async function updateProduct(productId) {
    const form = document.getElementById('editProductForm');
    if (!form) {
        showNotification('error', 'Formulaire non trouvé');
        return;
    }
    
    const formData = new FormData(form);
    
    // Validation côté client
    if (!validateFormData(formData)) {
        return;
    }
    
    // Ajouter le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        showNotification('error', 'Token CSRF manquant. Veuillez recharger la page.');
        return;
    }
    
    // Nettoyer les fichiers vides
    cleanEmptyFiles(formData);
    
    // Ajouter le token CSRF dans FormData (IMPORTANT pour les fichiers)
    formData.append('_token', csrfToken);
    
    try {
        // Afficher un loader
        showNotification('info', 'Mise à jour en cours...');
        
        const response = await fetch(`/store/api/products/${productId}`, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message || 'Produit mis à jour avec succès');
            
            // Fermer le modal
            if (editModal) {
                editModal.hide();
            }
            
            // Recharger la page pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    }
}

/**
 * Valider les données du formulaire
 */
function validateFormData(formData) {
    const requiredFields = ['name', 'description', 'price', 'stock'];
    const missingFields = [];
    
    requiredFields.forEach(field => {
        const value = formData.get(field);
        
        if (!value || value.toString().trim() === '') {
            missingFields.push(field);
        }
    });
    
    if (missingFields.length > 0) {
        showNotification('error', 'Champs manquants: ' + missingFields.join(', '));
        return false;
    }
    
    // Validation des prix
    const price = parseFloat(formData.get('price'));
    const promoPrice = formData.get('promo_price');
    
    if (price <= 0) {
        showNotification('error', 'Le prix doit être supérieur à 0');
        return false;
    }
    
    if (promoPrice && parseFloat(promoPrice) >= price) {
        showNotification('error', 'Le prix promo doit être inférieur au prix normal');
        return false;
    }
    
    return true;
}

/**
 * Nettoyer les fichiers vides
 */
function cleanEmptyFiles(formData) {
    const entries = Array.from(formData.entries());
    entries.forEach(([key, value]) => {
        if (value instanceof File && value.size === 0) {
            formData.delete(key);
        }
    });
}

/**
 * Supprimer un produit - VERSION REFACTORISÉE
 */
window.deleteProductInternal = async function(id) {
    if (!confirm('Voulez-vous vraiment supprimer ce produit ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        // Afficher un loader
        showNotification('info', 'Suppression en cours...');
        
        const response = await fetch(`/store/api/products/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message || 'Produit supprimé avec succès');
            
            // Recharger la page pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    }
}

/**
 * Fonctions globales pour les boutons d'action
 */
window.editProduct = function(id) {
    try {
        editProductInternal(id);
    } catch (error) {
        showNotification('error', 'Erreur lors de l\'édition: ' + error.message);
    }
};

window.deleteProduct = function(id) {
    try {
        deleteProductInternal(id);
    } catch (error) {
        showNotification('error', 'Erreur lors de la suppression: ' + error.message);
    }
};

/**
 * Fonction de notification
 */
function showNotification(type, message) {
    // Utiliser la fonction de notification existante si disponible
    if (typeof window.showNotification === 'function') {
        window.showNotification(type, message);
    } else {
        // Fallback simple
        alert(message);
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Système de modification de produit initialisé
});
