<?php
use Illuminate\Support\Facades\Storage;
?>



<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/store.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/seller-dashboard.css')); ?>">

<!-- Fonctions globales pour les boutons d'action -->
<script>
// Cette section charge en PREMIER pour que les fonctions soient disponibles globalement
console.log('Chargement des fonctions globales du dashboard...');

window.getStatusLabel = function(status) {
    const labels = {
        'pending': 'En cours de validation',
        'processing': 'En cours de livraison',
        'delivered': 'Livrée',
        'cancelled': 'Annulée'
    };
    return labels[status] || status;
};

window.updateOrderStatus = async function(orderNumber, newStatus) {
    console.log('updateOrderStatus appelé:', orderNumber, newStatus);
    if (!confirm(`Êtes-vous sûr de vouloir marquer cette commande comme "${window.getStatusLabel(newStatus)}" ?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/store/api/orders/${orderNumber}/status`, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', `Commande ${orderNumber} mise à jour avec succès !`);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('error', data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    }
};

window.cancelOrder = async function(orderNumber) {
    if (!confirm(`Êtes-vous sûr de vouloir annuler la commande ${orderNumber} ? Cette action est irréversible.`)) {
        return;
    }
    
    try {
        const response = await fetch(`/store/api/orders/${orderNumber}/cancel`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', `Commande ${orderNumber} annulée avec succès !`);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('error', data.message || 'Erreur lors de l\'annulation');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    }
};

window.updatePaymentStatus = async function(orderNumber, newPaymentStatus) {
    console.log('updatePaymentStatus appelé:', orderNumber, newPaymentStatus);
    const statusLabels = {
        'pending': 'En attente',
        'paid': 'Payé',
        'failed': 'Échoué',
        'refunded': 'Remboursé'
    };
    
    const statusLabel = statusLabels[newPaymentStatus] || newPaymentStatus;
    
    if (!confirm(`Êtes-vous sûr de vouloir marquer le paiement de cette commande comme "${statusLabel}" ?`)) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch(`/store/api/orders/${orderNumber}/payment-status`, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                payment_status: newPaymentStatus
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', `Statut de paiement de la commande ${orderNumber} mis à jour avec succès !`);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('error', data.message || 'Erreur lors de la mise à jour du statut de paiement');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    }
};

// ========================================
// FONCTIONS DE GESTION DES PRODUITS
// ========================================

// Afficher le modal d'ajout de produit
window.showAddProductModal = function() {
    // Créer le modal
    const modalHtml = `
        <div class="modal fade z-index-9x" id="addProductModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom du produit *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Prix (FCFA) *</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Stock *</label>
                                    <input type="number" class="form-control" id="stock" name="stock" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Catégorie *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php $__currentLoopData = \App\Models\Category::active()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subcategory_id" class="form-label">Sous-catégorie</label>
                                <select class="form-select" id="subcategory_id" name="subcategory_id">
                                    <option value="">Sélectionner une sous-catégorie</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image principale *</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn orange-bg text-white" onclick="submitProduct()">Ajouter</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter le modal au DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Initialiser Bootstrap Modal
    const modalElement = document.getElementById('addProductModal');
    const modal = new bootstrap.Modal(modalElement);
    
    // Charger les sous-catégories quand une catégorie est sélectionnée
    // Utiliser un délai pour s'assurer que le DOM est complètement rendu
    setTimeout(() => {
        const categorySelect = document.getElementById('category_id');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                const categoryId = this.value;
                console.log('🔍 Catégorie sélectionnée dans modal ajout:', categoryId);
                
                // Cibler directement le select dans le modal d'ajout
                const addModal = document.getElementById('addProductModal');
                const subcategorySelect = addModal ? addModal.querySelector('#subcategory_id') : null;
                
                if (!subcategorySelect) {
                    console.error('❌ Select de sous-catégorie non trouvé dans le modal d\'ajout');
                    return;
                }
                
                console.log('✅ Select de sous-catégorie trouvé dans modal:', subcategorySelect);
                
                // Vider le select
                subcategorySelect.innerHTML = '<option value="">Sélectionner une sous-catégorie</option>';
                
                if (!categoryId || categoryId === '') {
                    console.log('ℹ️ Aucune catégorie sélectionnée');
                    return;
                }
                
                // Charger les sous-catégories directement
                fetch(`/api/categories/${categoryId}/subcategories`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('📋 Réponse API sous-catégories:', data);
                    if (data.success && data.subcategories && data.subcategories.length > 0) {
                        // Utiliser directement la référence au select trouvée au début
                        // Vérifier qu'il est toujours dans le DOM
                        if (!subcategorySelect || !subcategorySelect.parentNode) {
                            console.error('❌ Select de sous-catégorie n\'est plus dans le DOM');
                            // Essayer de le retrouver
                            const addModalCheck = document.getElementById('addProductModal');
                            const selectCheck = addModalCheck ? addModalCheck.querySelector('#subcategory_id') : null;
                            if (!selectCheck) {
                                console.error('❌ Impossible de retrouver le select de sous-catégorie');
                                return;
                            }
                            // Utiliser le nouveau select trouvé
                            data.subcategories.forEach(subcategory => {
                                const option = document.createElement('option');
                                option.value = subcategory.id;
                                option.textContent = subcategory.name;
                                selectCheck.appendChild(option);
                            });
                            console.log(`✅ ${data.subcategories.length} sous-catégorie(s) ajoutée(s) au select (via nouvelle recherche)`);
                        } else {
                            // Utiliser la référence originale
                            const optionsBefore = subcategorySelect.options.length;
                            data.subcategories.forEach(subcategory => {
                                const option = document.createElement('option');
                                option.value = subcategory.id;
                                option.textContent = subcategory.name;
                                subcategorySelect.appendChild(option);
                            });
                            const optionsAfter = subcategorySelect.options.length;
                            console.log(`✅ ${data.subcategories.length} sous-catégorie(s) ajoutée(s) au select`);
                            console.log(`📊 Options avant: ${optionsBefore}, après: ${optionsAfter}`);
                            console.log('📋 Contenu du select:', Array.from(subcategorySelect.options).map(opt => `${opt.value}: ${opt.textContent}`));
                            
                            // Forcer la mise à jour visuelle et le rendu
                            subcategorySelect.dispatchEvent(new Event('change', { bubbles: true }));
                            subcategorySelect.style.display = 'none';
                            subcategorySelect.offsetHeight; // Force reflow
                            subcategorySelect.style.display = '';
                        }
                    } else {
                        console.log('ℹ️ Aucune sous-catégorie disponible pour cette catégorie');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur lors du chargement des sous-catégories:', error);
                });
            });
            console.log('✅ Event listener attaché au select de catégorie');
        } else {
            console.error('❌ Select de catégorie non trouvé dans le modal');
        }
        
        // Supprimer le modal du DOM quand il est fermé
        modalElement.addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }, 200); // Augmenté à 200ms pour plus de sécurité
    
    modal.show();
};

// Éditer un produit
window.editProduct = function(id) {
    console.log('🔧 window.editProduct appelé avec ID:', id);
    
    // Utiliser editProductInternal si disponible (défini plus bas dans le script)
    // Sinon utiliser l'implémentation basique
    if (typeof editProductInternal === 'function') {
        editProductInternal(id);
        return;
    }
    
    // Fallback : implémentation basique
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        showNotification('error', 'Token CSRF manquant. Veuillez recharger la page.');
        return;
    }
    
    fetch(`/store/api/products/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.product) {
            const product = data.product;
            
            // Remplir les champs du formulaire
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_brand').value = product.brand || '';
            document.getElementById('edit_model').value = product.model || '';
            document.getElementById('edit_warranty').value = product.warranty || '';
            document.getElementById('edit_status').value = product.status;
            
            // Afficher l'image actuelle
            if (product.image) {
                const previewEl = document.getElementById('current_image_preview');
                if (previewEl) {
                    previewEl.innerHTML = `
                        <div class="d-flex align-items-center mb-2">
                            <img src="/storage/${product.image}" alt="Image actuelle" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                            <div class="ms-3">
                                <small class="text-muted">Image actuelle</small>
                            </div>
                        </div>
                    `;
                }
            }
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        } else {
            showNotification('error', 'Erreur lors du chargement du produit');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du chargement du produit');
    });
};

// Supprimer un produit
window.deleteProduct = async function(id) {
    console.log('🔧 window.deleteProduct appelé avec ID:', id);
    
    // Utiliser deleteProductInternal si disponible (défini plus bas dans le script)
    // Sinon utiliser l'implémentation basique
    if (typeof deleteProductInternal === 'function') {
        deleteProductInternal(id);
        return;
    }
    
    // Fallback : implémentation basique
    if (!confirm('Voulez-vous vraiment supprimer ce produit ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch(`/store/api/products/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la suppression');
    }
};

// ========================================
// FONCTIONS UTILITAIRES POUR LES PRODUITS
// ========================================

// Variable globale pour stocker les images à supprimer
window.imagesToDelete = [];

// Soumettre le formulaire d'ajout de produit
async function submitProduct() {
    const form = document.getElementById('addProductForm');
    const formData = new FormData(form);
    
    // Ajouter le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    } else {
        showNotification('error', 'Token CSRF manquant');
        return;
    }
    
    // Validation de la description
    const description = formData.get('description');
    if (description.length < 50) {
        showNotification('danger', 'La description doit contenir au moins 50 caractères');
        return;
    }
    
    try {
        const response = await fetch('/store/api/products', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
            
            // Recharger la page pour afficher le nouveau produit
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'ajout du produit');
    }
}

// Éditer un produit - VERSION REFACTORISÉE
async function editProductInternal(id) {
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
            // Charger les données du produit dans le modal statique Blade
            const product = data.product;
            
            // Remplir les champs du formulaire
            const nameField = document.getElementById('edit_name');
            const descriptionField = document.getElementById('edit_description');
            const priceField = document.getElementById('edit_price');
            const stockField = document.getElementById('edit_stock');
            
            if (!nameField || !descriptionField || !priceField || !stockField) {
                console.error('❌ Champs du formulaire non trouvés!');
                throw new Error('Formulaire non trouvé');
            }
            
            document.getElementById('edit_product_id').value = product.id || '';
            nameField.value = product.name || '';
            descriptionField.value = product.description || '';
            priceField.value = product.price || '';
            stockField.value = product.stock || '';
            document.getElementById('edit_brand').value = product.brand || '';
            document.getElementById('edit_model').value = product.model || '';
            document.getElementById('edit_warranty').value = product.warranty || '';
            document.getElementById('edit_promo_price').value = product.promo_price || '';
            document.getElementById('edit_discount').value = product.discount || '';
            document.getElementById('edit_status').value = product.status || 'active';
            
            // Remplir la catégorie et charger les sous-catégories
            if (product.category_id) {
                document.getElementById('edit_category_id').value = product.category_id;
                loadSubcategories(product.category_id, product.subcategory_id);
            }
            
            // Remplir les tags (convertir le tableau en string séparée par virgules)
            const tagsField = document.getElementById('edit_tags');
            if (tagsField && product.tags) {
                tagsField.value = Array.isArray(product.tags) ? product.tags.join(', ') : product.tags;
            }
            
            // Réinitialiser les images à supprimer
            window.imagesToDelete = [];
            
            // Afficher les images existantes (inclure l'image principale)
            let allImages = [];
            
            // Ajouter l'image principale si elle existe
            if (product.image) {
                allImages.push(product.image);
            }
            
            // Ajouter les autres images (sans dupliquer l'image principale)
            if (product.images && Array.isArray(product.images)) {
                product.images.forEach(img => {
                    if (img !== product.image && img) {
                        allImages.push(img);
                    }
                });
            }
            
            // Dédupliquer les images au cas où
            allImages = [...new Set(allImages)];
            
            displayCurrentImages(allImages);
            
            // Afficher le modal statique
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        } else {
            throw new Error(data.message || 'Produit non trouvé');
        }
    } catch (error) {
        console.error('❌ Erreur lors de l\'édition:', error);
        showNotification('error', error.message || 'Erreur lors du chargement du produit');
    }
}

// Afficher les images existantes du produit
function displayCurrentImages(images) {
    const container = document.getElementById('current_images_container');
    if (!container) return;
    
    container.innerHTML = ''; // Vider le conteneur
    
    if (!images || images.length === 0) {
        container.innerHTML = '<p class="text-muted">Aucune image pour ce produit</p>';
        return;
    }
    
    // Affichage des images avec boutons de suppression
    images.forEach((image, index) => {
        const imageUrl = image.startsWith('products/') || image.startsWith('images/') 
            ? `/storage/${image}` 
            : image;
        
        const imageCard = document.createElement('div');
        imageCard.className = 'position-relative';
        imageCard.style.width = '120px';
        imageCard.style.height = '120px';
        imageCard.innerHTML = `
            <img src="${imageUrl}" 
                 alt="Image ${index + 1}" 
                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 2px solid #dee2e6;"
                 onerror="this.src='/images/placeholder.jpg'">
            <button type="button" 
                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                    style="padding: 0.25rem 0.5rem; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"
                    onclick="window.removeImage(${index})"
                    data-image-url="${image}"
                    title="Supprimer cette image">
                <i class="bi bi-x-lg" style="font-size: 0.8rem;"></i>
            </button>
        `;
        container.appendChild(imageCard);
    });
    
    // Sauvegarder la liste complète des images pour la suppression
    window.allImages = images;
    // Sauvegarder l'index des images à supprimer
    window.imagesToDelete = window.imagesToDelete || [];
}

// Fonction pour marquer une image comme à supprimer
window.removeImage = function(index) {
    if (confirm('Voulez-vous vraiment supprimer cette image ?')) {
        // Récupérer l'URL de l'image à partir du bouton
        const buttons = document.querySelectorAll('#current_images_container button');
        if (buttons[index]) {
            const imageUrl = buttons[index].dataset.imageUrl;
            if (imageUrl && !window.imagesToDelete.includes(imageUrl)) {
                window.imagesToDelete.push(imageUrl);
            }
            
            // Marquer visuellement l'image comme supprimée
            const imageDiv = buttons[index].closest('div.position-relative');
            if (imageDiv) {
                imageDiv.style.opacity = '0.5';
                imageDiv.style.pointerEvents = 'none';
            }
            
            showNotification('success', 'Image marquée pour suppression');
        } else {
            showNotification('error', 'Erreur: Image non trouvée');
        }
    }
};

// Fonction simplifiée pour soumettre le formulaire de modification
window.submitEditForm = function() {
    const form = document.getElementById('editProductForm');
    const productId = document.getElementById('edit_product_id').value;
    
    console.log('🔧 Soumission du formulaire pour le produit:', productId);
    
    // Vérifier le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        console.error('❌ Token CSRF non trouvé pour la soumission !');
        showNotification('error', 'Token CSRF manquant. Veuillez recharger la page.');
        return;
    }
    
    // Créer FormData
    const formData = new FormData(form);
    
    // Convertir le statut en is_active
    const status = formData.get('status');
    formData.append('is_active', status === 'active' ? '1' : '0');
    
    // Ajouter les indices des images à supprimer
    if (window.imagesToDelete && window.imagesToDelete.length > 0) {
        window.imagesToDelete.forEach(index => {
            formData.append('images_to_delete[]', index);
        });
        console.log('🗑️ Images à supprimer:', window.imagesToDelete);
    }
    
    // Envoyer la requête en POST avec _method=PUT pour que Laravel puisse parser le FormData
    formData.append('_method', 'PUT');
    
    fetch(`/store/api/products/${productId}`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();
            
            // Recharger la page pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour');
    });
};

// Supprimer un produit - fonction interne
async function deleteProductInternal(id) {
    if (!confirm('Voulez-vous vraiment supprimer ce produit ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch(`/store/api/products/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            // Recharger la page pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la suppression');
    }
}

// Charger les sous-catégories
function loadSubcategories(categoryId, selectedSubcategoryId = null) {
    // Chercher le select dans l'ordre de priorité
    // 1. Dans le modal d'édition (edit_subcategory_id)
    // 2. Dans le modal d'ajout ou autres formulaires (subcategory_id) 
    // 3. Ancien format (subcategory_select)
    let subcategorySelect = document.getElementById('edit_subcategory_id');
    
    if (!subcategorySelect) {
        // Si on est dans le modal d'ajout, chercher subcategory_id dans le modal
        const addModal = document.getElementById('addProductModal');
        if (addModal) {
            subcategorySelect = addModal.querySelector('#subcategory_id');
        }
        
        // Sinon chercher globalement
        if (!subcategorySelect) {
            subcategorySelect = document.getElementById('subcategory_id');
        }
        
        // Dernier recours
        if (!subcategorySelect) {
            subcategorySelect = document.getElementById('subcategory_select');
        }
    }
    
    if (!subcategorySelect) {
        console.warn('⚠️ Champ sous-catégorie non trouvé. IDs recherchés: edit_subcategory_id, subcategory_id, subcategory_select');
        return;
    }
    
    console.log('✅ Select de sous-catégorie trouvé:', subcategorySelect.id);
    
    // Vider les options existantes
    subcategorySelect.innerHTML = '<option value="">Sélectionner une sous-catégorie</option>';
    
    if (!categoryId || categoryId === '') {
        console.log('ℹ️ Aucune catégorie sélectionnée, sous-catégories vidées');
        return;
    }
    
    console.log('📡 Chargement des sous-catégories pour catégorie:', categoryId);
    
    // Charger les sous-catégories via AJAX
    fetch(`/api/categories/${categoryId}/subcategories`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📋 Réponse API sous-catégories:', data);
        if (data.success && data.subcategories && data.subcategories.length > 0) {
            data.subcategories.forEach(subcategory => {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = subcategory.name;
                if (selectedSubcategoryId && subcategory.id == selectedSubcategoryId) {
                    option.selected = true;
                }
                subcategorySelect.appendChild(option);
            });
            console.log(`✅ ${data.subcategories.length} sous-catégorie(s) chargée(s) avec succès`);
        } else {
            console.log('ℹ️ Aucune sous-catégorie disponible pour cette catégorie');
        }
    })
    .catch(error => {
        console.error('❌ Erreur lors du chargement des sous-catégories:', error);
    });
}

// Attacher loadSubcategories à window pour accès global
window.loadSubcategories = loadSubcategories;

console.log('Fonctions globales chargées:', Object.keys(window).filter(k => k.includes('update') || k.includes('cancel') || k.includes('Status') || k.includes('Product') || k.includes('Subcategor')));
</script>

<!-- Container pour les notifications toast du dashboard -->
<div class="toast-container position-fixed top-0 end-0 p-3 z-index-9x">
    <div id="dashboardNotificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i id="dashboardToastIcon" class="bi me-2"></i>
            <strong id="dashboardToastTitle" class="me-auto"></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="dashboardToastBody" class="toast-body"></div>
    </div>
</div>

<!-- Styles pour les notifications toast -->
<style>
.toast {
    min-width: 300px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.toast-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    font-weight: 600;
}

.toast-body {
    font-size: 0.9rem;
    line-height: 1.4;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
    border-left: 4px solid #198754;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border-left: 4px solid #dc3545;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border-left: 4px solid #ffc107;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
    border-left: 4px solid #0dcaf0;
}

</style>

<div class="container-fluid my-4 store-dashboard">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm sticky-top" style="top: 80px;">
                <div class="card-body p-0">
                    <!-- Logo et nom de la boutique -->
                    <div class="text-center p-3 border-bottom">
                        <?php if($store->logo): ?>
                            <img id="storeLogoSidebar" src="<?php echo e($store->logo_url); ?>" alt="<?php echo e($store->name); ?>" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-shop orange-color" style="font-size: 2rem;"></i>
                            </div>
                        <?php endif; ?>
                        <h6 class="fw-bold mb-1"><?php echo e($store->name); ?></h6>
                        <small class="text-muted">
                            <?php echo e($store->category->name); ?>

                            <?php if($store->subcategory): ?>
                                → <?php echo e($store->subcategory->name); ?>

                            <?php endif; ?>
                        </small>
                    </div>

                    <!-- Menu -->
                    <ul class="nav flex-column" id="storeTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#overview" role="tab">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <span>Vue d'ensemble</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#products" role="tab">
                                <i class="bi bi-box-seam me-2"></i>
                                <span>Produits</span>
                                <span class="badge orange-bg ms-auto"><?php echo e($stats['total_products']); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active d-flex align-items-center" data-bs-toggle="tab" href="#orders" role="tab">
                                <i class="bi bi-bag me-2"></i>
                                <span>Commandes</span>
                                <?php if($stats['pending_orders'] > 0): ?>
                                    <span class="badge bg-danger ms-auto"><?php echo e($stats['pending_orders']); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#settings" role="tab">
                                <i class="bi bi-gear me-2"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                        <li class="nav-item border-top">
                            <a class="nav-link d-flex align-items-center" href="<?php echo e(route('store.show', $store->slug)); ?>" target="_blank">
                                <i class="bi bi-eye me-2"></i>
                                <span>Voir ma boutique</span>
                                <i class="bi bi-box-arrow-up-right ms-auto"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="<?php echo e(route('accueil')); ?>">
                                <i class="bi bi-house me-2"></i>
                                <span>Retour au site</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10">
            <div class="tab-content">
                <!-- Vue d'ensemble -->
                <div class="tab-pane fade" id="overview" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>Vue d'ensemble
                        </h2>
                        <div>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Boutique active
                            </span>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Total Produits</p>
                                            <h3 class="fw-bold mb-0 stats-number" id="statTotalProducts"><?php echo e($stats['total_products']); ?></h3>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-box-seam text-primary" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Commandes</p>
                                            <h3 class="fw-bold mb-0"><?php echo e($stats['total_orders']); ?></h3>
                                        </div>
                                        <div class="bg-success bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-bag text-success" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Ventes Totales</p>
                                            <h3 class="fw-bold mb-0"><?php echo e(number_format($stats['total_sales'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></h3>
                                        </div>
                                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-graph-up text-warning" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Revenus (<?php echo e(100 - $store->effective_commission_rate); ?>%)</p>
                                            <h3 class="fw-bold mb-0 orange-color"><?php echo e(number_format($stats['total_revenue'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></h3>
                                        </div>
                                        <div class="orange-bg bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-currency-dollar orange-color" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning me-2"></i>Actions rapides
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <button class="btn btn-outline-primary w-100" onclick="showAddProductModal()">
                                        <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-outline-success w-100" onclick="showTab('orders')">
                                        <i class="bi bi-bag me-2"></i>Voir les commandes
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-outline-warning w-100" onclick="showTab('settings')">
                                        <i class="bi bi-gear me-2"></i>Paramètres
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commandes récentes -->
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>Commandes récentes
                            </h5>
                            <a href="#" onclick="showTab('orders')" class="btn btn-sm btn-outline-primary">Voir tout</a>
                        </div>
                        <div class="card-body">
                            <?php if($orders->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Commande</th>
                                                <th>Client</th>
                                                <th>Total</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $orders->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold"><?php echo e($order->order_number); ?></div>
                                                        <small class="text-muted"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                K
                                    </div>
                                                            <div>
                                                                <div class="fw-bold">Client KAZARIA</div>
                                                                <small class="text-muted">client@kazaria.com</small>
                                </div>
                            </div>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo \App\Helpers\OrderHelper::getStatusColor($order->status); ?>">
                                                            <?php echo \App\Helpers\OrderHelper::getStatusLabel($order->status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-<?php echo \App\Helpers\OrderHelper::getPaymentStatusColor($order->payment_status); ?> me-2">
                                                                <?php echo \App\Helpers\OrderHelper::getPaymentStatusLabel($order->payment_status); ?>
                                                            </span>
                                                            <?php if($order->payment_status == 'pending'): ?>
                                                                <button class="btn btn-outline-success btn-sm" 
                                                                        onclick="updatePaymentStatus('<?php echo e($order->order_number); ?>', 'paid')"
                                                                        title="Marquer comme payé">
                                                                    <i class="bi bi-check-circle me-1"></i>Payé
                                                                </button>
                                                            <?php elseif($order->payment_status == 'paid'): ?>
                                                                <button class="btn btn-outline-warning btn-sm" 
                                                                        onclick="updatePaymentStatus('<?php echo e($order->order_number); ?>', 'pending')"
                                                                        title="Remettre en attente">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>En attente
                                                                </button>
                                                            <?php endif; ?>
                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?php echo e(route('store.order-details', $order->order_number)); ?>" 
                                                               class="btn btn-outline-primary btn-sm" 
                                                               title="Voir les détails">
                                                                <i class="bi bi-eye me-1"></i>Détails
                                                            </a>
                                                            <?php if($order->status == 'pending'): ?>
                                                                <button class="btn btn-outline-info btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'processing')"
                                                                        title="Traiter la commande">
                                                                    <i class="bi bi-play-circle me-1"></i>Traiter
                                                                </button>
                                                            <?php elseif($order->status == 'processing'): ?>
                                                                <button class="btn btn-outline-success btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'delivered')"
                                                                        title="Marquer comme livrée">
                                                                    <i class="bi bi-check-circle me-1"></i>Livrée
                                                                </button>
                                                            <?php elseif($order->status == 'delivered'): ?>
                                                                <button class="btn btn-outline-warning btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'processing')"
                                                                        title="Remettre en cours de traitement">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>Remettre en cours
                                                                </button>
                                                            <?php elseif($order->status == 'cancelled'): ?>
                                                                <button class="btn btn-outline-info btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'processing')"
                                                                        title="Remettre en cours de traitement">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>Remettre en cours
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if($orders->count() > 5): ?>
                                    <div class="text-center mt-3">
                                        <a href="#" onclick="showTab('orders')" class="btn btn-outline-primary">
                                            Voir toutes les commandes (<?php echo e($orders->count()); ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Aucune commande récente</h5>
                                    <p class="text-muted">Les nouvelles commandes apparaîtront ici.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Produits -->
                <div class="tab-pane fade" id="products" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-box-seam me-2"></i>Mes Produits
                        </h2>
                        <button class="btn orange-bg text-white" onclick="showAddProductModal()">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                        </button>
                    </div>

                    <!-- Liste des produits -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php if($products->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Prix</th>
                                                <th>Stock</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php
                                                                // Utiliser l'image principale ou la première image du tableau
                                                                $productImage = $product->image;
                                                                if (!$productImage && $product->images && is_array($product->images) && count($product->images) > 0) {
                                                                    $productImage = $product->images[0];
                                                                }
                                                            ?>
                                                            
                                                            <?php if($productImage): ?>
                                                                <img src="<?php echo e(asset('storage/' . $productImage)); ?>" 
                                                                     alt="<?php echo e($product->name); ?>" 
                                                                     class="me-3" 
                                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                                                     onerror="this.src='<?php echo e(asset('images/produit.jpg')); ?>'">
                                                            <?php else: ?>
                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                                                                     style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                                    <i class="bi bi-phone text-white" style="font-size: 1.2rem;"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <div class="fw-bold"><?php echo e($product->name); ?></div>
                                                                <small class="text-muted"><?php echo e($product->category->name ?? 'N/A'); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo e(number_format($product->price, 0, ',', ' ')); ?> FCFA</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($product->stock > 0 ? 'success' : 'danger'); ?>">
                                                            <?php echo e($product->stock); ?> en stock
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo e($product->is_active ? 'success' : 'warning'); ?>">
                                                            <?php echo e($product->is_active ? 'Actif' : 'Inactif'); ?>

                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="window.editProduct(<?php echo e($product->id); ?>)"
                                                title="Modifier le produit">
                                            <i class="bi bi-pencil me-1"></i>Modifier
                                        </button>
                                                            <button class="btn btn-outline-danger btn-sm" 
                                                                    onclick="window.deleteProduct(<?php echo e($product->id); ?>)"
                                                                    title="Supprimer le produit">
                                                                <i class="bi bi-trash me-1"></i>Supprimer
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Aucun produit trouvé</h5>
                                    <p class="text-muted">Commencez par ajouter votre premier produit.</p>
                                    <button class="btn orange-bg text-white" onclick="showAddProductModal()">
                                        <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                                    </button>
                                    </div>
                            <?php endif; ?>
                                </div>
                            </div>
                        </div>

                <!-- Modal de modification de produit (STATIQUE) -->
                <div class="modal fade z-index-9x" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true" style="z-index: 999999999 !important;">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editProductModalLabel">
                                    <i class="bi bi-pencil me-2"></i>Modifier le produit
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editProductForm" method="POST" action="" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="product_id" id="edit_product_id">
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" id="edit_name" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                                <option value="">Sélectionnez une catégorie</option>
                                                <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Sous-catégorie (optionnel)</label>
                                            <select class="form-select" name="subcategory_id" id="edit_subcategory_id">
                                                <option value="">Sélectionnez une sous-catégorie</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Description <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Stock <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="stock" id="edit_stock" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Marque</label>
                                            <input type="text" class="form-control" name="brand" id="edit_brand">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Modèle</label>
                                            <input type="text" class="form-control" name="model" id="edit_model">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Garantie</label>
                                            <input type="text" class="form-control" name="warranty" id="edit_warranty">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Prix promo (FCFA)</label>
                                            <input type="number" class="form-control" name="promo_price" id="edit_promo_price" step="0.01">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Réduction (%)</label>
                                            <input type="number" class="form-control" name="discount" id="edit_discount" min="0" max="100">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Statut</label>
                                            <select class="form-select" name="status" id="edit_status">
                                                <option value="active">Actif</option>
                                                <option value="inactive">Inactif</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Tags (séparés par virgule)</label>
                                            <input type="text" class="form-control" name="tags" id="edit_tags" placeholder="Ex: nouveau, promo, tendance">
                                            <small class="text-muted">Séparez les tags par des virgules</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Séparation pour les images -->
                                    <hr class="my-4">
                                    <h6 class="mb-3 text-primary">
                                        <i class="bi bi-images me-2"></i>Modification des images
                                    </h6>
                                    
                                    <div class="row g-3">
                                        <!-- Images existantes -->
                                        <div class="col-12">
                                            <label class="form-label">Images actuelles</label>
                                            <div id="current_images_container" class="d-flex flex-wrap gap-2 mb-3">
                                                <!-- Les images seront injectées ici par JavaScript -->
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <hr class="my-3">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Nouvelle image principale</label>
                                            <input type="file" class="form-control" name="image" accept="image/*">
                                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 5MB</small>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Nouvelles images supplémentaires</label>
                                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                                            <small class="text-muted">Maximum 5 images supplémentaires</small>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn orange-bg text-white" onclick="submitEditForm()">
                                    <i class="bi bi-check-lg me-1"></i>Mettre à jour
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commandes -->
                <div class="tab-pane fade show active" id="orders" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-bag me-2"></i>Commandes
                        </h2>
                        <?php if($stats['pending_orders'] > 0): ?>
                            <span class="badge bg-danger fs-6"><?php echo e($stats['pending_orders']); ?> en attente</span>
                        <?php endif; ?>
                    </div>

                    <!-- Filtres et statistiques -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-funnel me-2"></i>Filtres et statistiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small">Statut</label>
                                    <select class="form-select" id="orderStatusFilter">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="processing">En préparation</option>
                                        <option value="shipped">Expédiée</option>
                                        <option value="delivered">Livrée</option>
                                        <option value="cancelled">Annulée</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Date de</label>
                                    <input type="date" class="form-control" id="orderDateFrom">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Date à</label>
                                    <input type="date" class="form-control" id="orderDateTo">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Rechercher</label>
                                    <input type="text" class="form-control" id="orderSearchFilter" placeholder="N°, client, email...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Trier par</label>
                                    <select class="form-select" id="orderSortFilter">
                                        <option value="created_at">Date (récent)</option>
                                        <option value="created_at_asc">Date (ancien)</option>
                                        <option value="total">Montant (élevé)</option>
                                        <option value="total_asc">Montant (faible)</option>
                                        <option value="status">Statut</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">&nbsp;</label>
                                    <button class="btn orange-bg text-white w-100" onclick="loadOrders()" title="Filtrer">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Statistiques rapides -->
                            <div class="row g-3 mt-3" id="orderStatsContainer">
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-primary" id="statTotalOrders"><?php echo e($orderStats['total_orders']); ?></div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-warning bg-opacity-10 rounded">
                                        <div class="fw-bold text-warning" id="statPendingOrders"><?php echo e($orderStats['pending_orders']); ?></div>
                                        <small class="text-muted">En attente</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                        <div class="fw-bold text-info" id="statProcessingOrders"><?php echo e($orderStats['processing_orders']); ?></div>
                                        <small class="text-muted">En cours de livraison</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                        <div class="fw-bold text-success" id="statDeliveredOrders"><?php echo e($orderStats['delivered_orders']); ?></div>
                                        <small class="text-muted">Livrées</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                                        <div class="fw-bold text-danger" id="statCancelledOrders"><?php echo e($orderStats['cancelled_orders']); ?></div>
                                        <small class="text-muted">Annulées</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Statuts de paiement -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="text-center p-2 bg-warning bg-opacity-10 rounded">
                                        <div class="fw-bold text-warning" id="statPendingPayment"><?php echo e($orderStats['pending_payment']); ?></div>
                                        <small class="text-muted">Paiement en attente</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                        <div class="fw-bold text-success" id="statPaidOrders"><?php echo e($orderStats['paid_orders']); ?></div>
                                        <small class="text-muted">Payées</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des commandes -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php if($orders->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° Commande</th>
                                                <th>Date</th>
                                                <th>Client</th>
                                                <th>Produits</th>
                                                <th>Total</th>
                                                <th>Statut</th>
                                                <th>Paiement</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo e($order->order_number); ?></strong>
                                                    </td>
                                                    <td>
                                                        <small><?php echo e($order->created_at->format('d/m/Y H:i')); ?></small>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>Client KAZARIA</strong>
                                                            <br>
                                                            <small class="text-muted">client@kazaria.com</small>
                                    </div>
                                                    </td>
                                                    <td>
                                                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if($item->product && $item->product->store_id == $store->id): ?>
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <?php if($item->product->image): ?>
                                                                        <img src="<?php echo e(Storage::url($item->product->image)); ?>" 
                                                                             alt="<?php echo e($item->product->nom); ?>" 
                                                                             class="me-2" 
                                                                             style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                                                                    <?php endif; ?>
                                                                    <div>
                                                                        <small class="fw-bold"><?php echo e($item->product->nom); ?></small>
                                                                        <br>
                                                                        <small class="text-muted">Qté: <?php echo e($item->quantity); ?></small>
                                </div>
                            </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo \App\Helpers\OrderHelper::getStatusColor($order->status); ?>">
                                                            <?php echo \App\Helpers\OrderHelper::getStatusLabel($order->status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-<?php echo \App\Helpers\OrderHelper::getPaymentStatusColor($order->payment_status); ?> me-2">
                                                                <?php echo \App\Helpers\OrderHelper::getPaymentStatusLabel($order->payment_status); ?>
                                                            </span>
                                                            <?php if($order->payment_status == 'pending'): ?>
                                                                <button class="btn btn-outline-success btn-sm" 
                                                                        onclick="updatePaymentStatus('<?php echo e($order->order_number); ?>', 'paid')"
                                                                        title="Marquer comme payé">
                                                                    <i class="bi bi-check-circle me-1"></i>Payé
                                                                </button>
                                                            <?php elseif($order->payment_status == 'paid'): ?>
                                                                <button class="btn btn-outline-warning btn-sm" 
                                                                        onclick="updatePaymentStatus('<?php echo e($order->order_number); ?>', 'pending')"
                                                                        title="Remettre en attente">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>En attente
                                                                </button>
                                                            <?php elseif($order->payment_status == 'failed'): ?>
                                                                <button class="btn btn-outline-warning btn-sm" 
                                                                        onclick="updatePaymentStatus('<?php echo e($order->order_number); ?>', 'pending')"
                                                                        title="Remettre en attente">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>En attente
                                                                </button>
                                                            <?php elseif($order->payment_status == 'refunded'): ?>
                                                                <button class="btn btn-outline-info btn-sm" 
                                                                        onclick="updatePaymentStatus('<?php echo e($order->order_number); ?>', 'pending')"
                                                                        title="Remettre en attente">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>En attente
                                                                </button>
                                                            <?php endif; ?>
                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <!-- Bouton Voir détails -->
                                                            <a href="<?php echo e(route('store.order-details', $order->order_number)); ?>" 
                                                               class="btn btn-outline-primary btn-sm" 
                                                               title="Voir les détails de la commande">
                                                                <i class="bi bi-eye me-1"></i>Détails
                                                            </a>
                                                            
                                                            <?php if($order->status == 'pending'): ?>
                                                                <!-- Bouton Traiter la commande -->
                                                                <button class="btn btn-outline-info btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'processing')"
                                                                        title="Marquer comme en cours de traitement">
                                                                    <i class="bi bi-play-circle me-1"></i>Traiter
                                                                </button>
                                                            <?php elseif($order->status == 'processing'): ?>
                                                                <!-- Bouton Marquer comme livrée -->
                                                                <button class="btn btn-outline-success btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'delivered')"
                                                                        title="Marquer comme livrée">
                                                                    <i class="bi bi-check-circle me-1"></i>Livrée
                                                                </button>
                                                            <?php elseif($order->status == 'delivered'): ?>
                                                                <!-- Bouton pour remettre en cours -->
                                                                <button class="btn btn-outline-warning btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'processing')"
                                                                        title="Remettre en cours de traitement">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>Remettre en cours
                                                                </button>
                                                            <?php elseif($order->status == 'cancelled'): ?>
                                                                <!-- Bouton pour remettre en cours -->
                                                                <button class="btn btn-outline-info btn-sm" 
                                                                        onclick="updateOrderStatus('<?php echo e($order->order_number); ?>', 'processing')"
                                                                        title="Remettre en cours de traitement">
                                                                    <i class="bi bi-arrow-clockwise me-1"></i>Remettre en cours
                                                                </button>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($order->status != 'cancelled'): ?>
                                                                <!-- Bouton Annuler -->
                                                                <button class="btn btn-outline-danger btn-sm" 
                                                                        onclick="cancelOrder('<?php echo e($order->order_number); ?>')"
                                                                        title="Annuler la commande">
                                                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-bag display-1 text-muted"></i>
                                    <h5 class="mt-3 text-muted">Aucune commande trouvée</h5>
                                    <p class="text-muted">Les commandes de vos produits apparaîtront ici.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Paramètres -->
                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-gear me-2"></i>Paramètres de la boutique
                        </h2>
                    </div>

                    <!-- Informations générales -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informations générales</h5>
                        </div>
                        <div class="card-body">
                            <form id="updateStoreForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="store_name" class="form-label">Nom de la boutique</label>
                                        <input type="text" class="form-control" id="store_name" value="<?php echo e($store->name); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="store_email" value="<?php echo e($store->email); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_phone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="store_phone" value="<?php echo e($store->phone); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_category" class="form-label">Catégorie</label>
                                        <select class="form-select" id="store_category" required>
                                            <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($cat->id); ?>" <?php echo e($store->category_id == $cat->id ? 'selected' : ''); ?>>
                                                    <?php echo e($cat->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="store_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="store_description" rows="4" required><?php echo e($store->description); ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_address" class="form-label">Adresse</label>
                                        <input type="text" class="form-control" id="store_address" value="<?php echo e($store->address); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_city" class="form-label">Ville</label>
                                        <input type="text" class="form-control" id="store_city" value="<?php echo e($store->city); ?>">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn orange-bg text-white">
                                            <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Visuels -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Visuels</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Logo actuel</label>
                                    <div class="mb-3">
                                        <img id="storeLogoSettings" src="<?php echo e($store->logo_url); ?>" alt="Logo" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                    <input type="file" class="form-control" id="new_logo" accept="image/*">
                                    <button class="btn btn-sm orange-bg text-white mt-2" onclick="uploadLogo()">
                                        <i class="bi bi-upload me-1"></i>Changer le logo
                                    </button>
                                    <small class="text-muted d-block mt-1">Format recommandé : PNG, JPG (max 5MB)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bannière actuelle</label>
                                    <div class="mb-3">
                                        <img id="storeBannerSettings" src="<?php echo e($store->banner_url); ?>" alt="Bannière" class="img-thumbnail" style="max-height: 150px; max-width: 100%;">
                                    </div>
                                    <input type="file" class="form-control" id="new_banner" accept="image/*">
                                    <button class="btn btn-sm orange-bg text-white mt-2" onclick="uploadBanner()">
                                        <i class="bi bi-upload me-1"></i>Changer la bannière
                                    </button>
                                    <small class="text-muted d-block mt-1">Format recommandé : PNG, JPG (max 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Réseaux sociaux -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Réseaux sociaux</h5>
                        </div>
                        <div class="card-body">
                            <form id="updateSocialForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="facebook_url" class="form-label">
                                            <i class="bi bi-facebook text-primary me-2"></i>Facebook
                                        </label>
                                        <input type="url" class="form-control" id="facebook_url" 
                                               value="<?php echo e($store->social_links['facebook'] ?? ''); ?>" 
                                               placeholder="https://facebook.com/votre-page">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="instagram_url" class="form-label">
                                            <i class="bi bi-instagram text-danger me-2"></i>Instagram
                                        </label>
                                        <input type="url" class="form-control" id="instagram_url" 
                                               value="<?php echo e($store->social_links['instagram'] ?? ''); ?>" 
                                               placeholder="https://instagram.com/votre-compte">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="twitter_url" class="form-label">
                                            <i class="bi bi-twitter text-info me-2"></i>Twitter
                                        </label>
                                        <input type="url" class="form-control" id="twitter_url" 
                                               value="<?php echo e($store->social_links['twitter'] ?? ''); ?>" 
                                               placeholder="https://twitter.com/votre-compte">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="website_url" class="form-label">
                                            <i class="bi bi-globe text-success me-2"></i>Site web
                                        </label>
                                        <input type="url" class="form-control" id="website_url" 
                                               value="<?php echo e($store->social_links['website'] ?? ''); ?>" 
                                               placeholder="https://votre-site.com">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn orange-bg text-white">
                                            <i class="bi bi-check-circle me-2"></i>Enregistrer les liens
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Paramètres de sécurité -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Paramètres de sécurité</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Statut de la boutique</h6>
                                            <small class="text-muted">Votre boutique est actuellement 
                                                <?php
                                                    $kycStatus = $store->effective_kyc_status ? ucfirst($store->effective_kyc_status) : 'Inconnu';
                                                    $kycClass = $store->isKycValidated() ? 'bg-success' : ($store->isKycPending() ? 'bg-warning text-dark' : ($store->isKycRejected() ? 'bg-danger' : 'bg-secondary'));
                                                ?>
                                                <span class="badge <?php echo e($kycClass); ?>"><?php echo e($kycStatus); ?></span>
                                            </small>
                                        </div>
                                        <div>
                                            <i class="bi bi-shield-check text-success fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Vérification</h6>
                                            <small class="text-muted">
                                                <?php if($store->is_verified): ?>
                                                    <span class="badge bg-success">Vérifiée</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">En attente</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <i class="bi bi-<?php echo e($store->is_verified ? 'check-circle-fill' : 'clock'); ?> 
                                               text-<?php echo e($store->is_verified ? 'success' : 'warning'); ?> fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Commission</h6>
                                            <small class="text-muted">Taux actuel : <?php echo e(number_format($store->effective_commission_rate, 2, ',', ' ')); ?>%</small>
                                        </div>
                                        <div>
                                            <i class="bi bi-percent text-info fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <?php if($store->crm_scoring !== null): ?>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Évaluation du support</h6>
                                                <small class="text-muted">Score actuel : <?php echo e(number_format($store->crm_scoring, 1, ',', ' ')); ?></small>
                                            </div>
                                            <div>
                                                <i class="bi bi-speedometer2 text-primary fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Boutique officielle</h6>
                                            <small class="text-muted">
                                                <?php if($store->is_official): ?>
                                                    <span class="badge bg-primary">Officielle</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Standard</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <i class="bi bi-<?php echo e($store->is_official ? 'star-fill' : 'star'); ?> 
                                               text-<?php echo e($store->is_official ? 'warning' : 'secondary'); ?> fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions dangereuses -->
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>Zone dangereuse
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="text-danger">Désactiver la boutique</h6>
                                    <p class="text-muted small mb-3">Votre boutique ne sera plus visible par les clients.</p>
                                    <button class="btn btn-outline-warning btn-sm" onclick="toggleStoreStatus('suspended')">
                                        <i class="bi bi-pause-circle me-1"></i>Suspendre
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger">Supprimer la boutique</h6>
                                    <p class="text-muted small mb-3">Cette action est irréversible et supprimera tous vos produits.</p>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteStore()">
                                        <i class="bi bi-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const storeId = <?php echo e($store->id); ?>;
const token = localStorage.getItem('auth_token');

// Les fonctions globales sont déjà déclarées en haut de page
// La fonction showNotification est définie plus bas dans le fichier pour utiliser les toasts

// Fonction pour changer d'onglet
function showTab(tabName) {
    const tab = document.querySelector(`a[href="#${tabName}"]`);
    if (tab) {
        const bsTab = new bootstrap.Tab(tab);
        bsTab.show();
    }
}

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Attendre que l'onglet soit restauré avant de charger les données
    setTimeout(() => {
    loadRecentOrders();
    loadOrderStats(); // Charger les statistiques
    
    // Charger les commandes si on est sur l'onglet commandes
    if (document.querySelector('a[href="#orders"]').classList.contains('active')) {
        loadOrders();
    }
    
    // Charger les produits quand l'onglet est affiché
    document.querySelector('a[href="#products"]').addEventListener('shown.bs.tab', function() {
        loadProducts();
    });
    
    // Charger les commandes quand l'onglet est affiché
    document.querySelector('a[href="#orders"]').addEventListener('shown.bs.tab', function() {
        loadOrders();
        loadOrderStats();
    });
    }, 100); // Petit délai pour laisser le temps à restoreActiveTab de s'exécuter
    
    // Auto-reload des commandes toutes les 30 secondes si l'onglet est actif
    setInterval(() => {
        const ordersTab = document.querySelector('a[href="#orders"]');
        if (ordersTab && ordersTab.classList.contains('active')) {
            checkForNewOrders(); // Vérifier les nouvelles commandes
        }
    }, 30000);
    
    // Vérifier les nouvelles commandes
    let lastOrderCount = 0;
    async function checkForNewOrders() {
        try {
            const response = await fetch('/store/api/orders/stats', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const currentOrderCount = data.stats.total_orders || 0;
                
                // Si le nombre de commandes a augmenté
                if (currentOrderCount > lastOrderCount && lastOrderCount > 0) {
                    const newOrders = currentOrderCount - lastOrderCount;
                    showNotification('info', `🎉 ${newOrders} nouvelle${newOrders > 1 ? 's' : ''} commande${newOrders > 1 ? 's' : ''} reçue${newOrders > 1 ? 's' : ''} !`);
                    
                    // Mettre à jour le badge dans le menu
                    const ordersBadge = document.querySelector('a[href="#orders"] .badge');
                    if (ordersBadge) {
                        ordersBadge.textContent = currentOrderCount;
                        ordersBadge.className = 'badge bg-danger ms-auto';
                    }
                }
                
                lastOrderCount = currentOrderCount;
                loadOrderStats(); // Mettre à jour les statistiques affichées
            }
        } catch (error) {
            console.error('Erreur vérification nouvelles commandes:', error);
        }
    }

    // Activer l'onglet via paramètre d'URL (ex: ?tab=settings)
    try {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get('tab');
        if (tabParam) {
            showTab(tabParam);
        }
    } catch (e) {
        console.warn('Paramètres URL non disponibles', e);
    }
});

// Charger les commandes récentes
async function loadRecentOrders() {
    const container = document.getElementById('recentOrdersContainer');
    
    try {
        const response = await fetch(`/store/api/recent-orders?limit=5`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            container.innerHTML = data.orders.map(order => `
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${order.order_number}</h6>
                            <small class="text-muted">${new Date(order.created_at).toLocaleDateString('fr-FR')}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</span>
                            <div class="fw-bold mt-1">${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-muted text-center py-3">Aucune commande pour le moment</p>';
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Charger les produits
async function loadProducts() {
    const container = document.getElementById('productsContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    try {
        const response = await fetch(`/store/api/products`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.products.length > 0) {
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.products.map(product => `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${product.image}" alt="${product.name}" class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                            <span>${product.name}</span>
                                        </div>
                                    </td>
                                    <td>${new Intl.NumberFormat('fr-FR').format(product.price)} FCFA</td>
                                    <td>${product.stock}</td>
                                    <td><span class="badge bg-${product.stock > 0 ? 'success' : 'danger'}">${product.stock > 0 ? 'En stock' : 'Rupture'}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="window.editProduct(${product.id})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="window.deleteProduct(${product.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Vous n'avez pas encore de produits</p>
                    <button class="btn orange-bg text-white" onclick="showAddProductModal()">
                        <i class="bi bi-plus-circle me-2"></i>Ajouter votre premier produit
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Charger les commandes
async function loadOrders() {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    try {
        
        // Récupérer les paramètres de filtrage
        const params = new URLSearchParams();
        const status = document.getElementById('orderStatusFilter').value;
        const dateFrom = document.getElementById('orderDateFrom').value;
        const dateTo = document.getElementById('orderDateTo').value;
        const search = document.getElementById('orderSearchFilter').value;
        const sort = document.getElementById('orderSortFilter').value;
        
        if (status) params.append('status', status);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (search) params.append('search', search);
        if (sort) {
            if (sort.includes('_')) {
                const [sortBy, sortOrder] = sort.split('_');
                params.append('sort_by', sortBy);
                params.append('sort_order', sortOrder || 'desc');
            } else {
                params.append('sort_by', sort);
                params.append('sort_order', 'desc');
            }
        }
        
        const url = `/store/api/orders?${params.toString()}`;
        
        const response = await fetch(url, {
            headers: {'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Articles</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.orders.map(order => `
                                <tr>
                                    <td><strong>${order.order_number}</strong></td>
                                    <td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td>
                                    <td>
                                        <div>
                                            <strong>${order.shipping_name}</strong><br>
                                            <small class="text-muted">${order.shipping_email}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">${order.items_count} article${order.items_count > 1 ? 's' : ''}</span>
                                    </td>
                                    <td><strong>${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</strong></td>
                                    <td>
                                        <span class="badge bg-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</span>
                                        <br>
                                        <small class="text-muted">Paiement: <span class="badge bg-${getPaymentStatusColor(order.payment_status)}">${getPaymentStatusLabel(order.payment_status)}</span></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder('${order.order_number}')" title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            ${getOrderActionButtons(order.status, order.order_number)}
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ${data.pagination ? generatePagination(data.pagination) : ''}
            `;
        } else {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bag text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Aucune commande trouvée</p>
                    <button class="btn btn-outline-primary" onclick="clearFilters()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Effacer les filtres
                    </button>
                </div>
            `;
        }
        
        // Charger les statistiques des commandes
        loadOrderStats();
        
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Charger les statistiques des commandes
async function loadOrderStats() {
    try {
        
        const response = await fetch('/store/api/orders/stats', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            document.getElementById('statTotalOrders').textContent = stats.total_orders || 0;
            document.getElementById('statPendingOrders').textContent = stats.pending_orders || 0;
            document.getElementById('statProcessingOrders').textContent = stats.processing_orders || 0;
            document.getElementById('statDeliveredOrders').textContent = stats.delivered_orders || 0;
            document.getElementById('statCancelledOrders').textContent = stats.cancelled_orders || 0;
            document.getElementById('statPendingPayment').textContent = stats.pending_payment || 0;
            document.getElementById('statPaidOrders').textContent = stats.paid_orders || 0;
        }
    } catch (error) {
        console.error('Erreur chargement stats commandes:', error);
    }
}

// Générer la pagination
function generatePagination(pagination) {
    if (pagination.last_page <= 1) return '';
    
    let paginationHtml = '<nav aria-label="Pagination des commandes"><ul class="pagination justify-content-center">';
    
    // Bouton précédent
    if (pagination.current_page > 1) {
        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrdersPage(${pagination.current_page - 1})">Précédent</a></li>`;
    }
    
    // Pages
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadOrdersPage(${i})">${i}</a></li>`;
    }
    
    // Bouton suivant
    if (pagination.current_page < pagination.last_page) {
        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrdersPage(${pagination.current_page + 1})">Suivant</a></li>`;
    }
    
    paginationHtml += '</ul></nav>';
    
    // Informations de pagination
    paginationHtml += `
        <div class="text-center mt-3">
            <small class="text-muted">
                Affichage de ${pagination.from || 0} à ${pagination.to || 0} sur ${pagination.total} commande${pagination.total > 1 ? 's' : ''}
            </small>
        </div>
    `;
    
    return paginationHtml;
}

// Charger une page spécifique
function loadOrdersPage(page) {
    // Ajouter le paramètre de page aux filtres existants
    const params = new URLSearchParams();
    const status = document.getElementById('orderStatusFilter').value;
    const dateFrom = document.getElementById('orderDateFrom').value;
    const dateTo = document.getElementById('orderDateTo').value;
    const search = document.getElementById('orderSearchFilter').value;
    const sort = document.getElementById('orderSortFilter').value;
    
    if (status) params.append('status', status);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (search) params.append('search', search);
    if (sort) {
        if (sort.includes('_')) {
            const [sortBy, sortOrder] = sort.split('_');
            params.append('sort_by', sortBy);
            params.append('sort_order', sortOrder || 'desc');
        } else {
            params.append('sort_by', sort);
            params.append('sort_order', 'desc');
        }
    }
    params.append('page', page);
    
    // Recharger avec la nouvelle page
    loadOrdersWithParams(params.toString());
}

// Charger les commandes avec des paramètres spécifiques
async function loadOrdersWithParams(params) {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    try {
        
        const response = await fetch(`/store/api/orders?${params}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Articles</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.orders.map(order => `
                                <tr>
                                    <td><strong>${order.order_number}</strong></td>
                                    <td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td>
                                    <td>
                                        <div>
                                            <strong>${order.shipping_name}</strong><br>
                                            <small class="text-muted">${order.shipping_email}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">${order.items_count} article${order.items_count > 1 ? 's' : ''}</span>
                                    </td>
                                    <td><strong>${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</strong></td>
                                    <td>
                                        <span class="badge bg-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</span>
                                        <br>
                                        <small class="text-muted">Paiement: <span class="badge bg-${getPaymentStatusColor(order.payment_status)}">${getPaymentStatusLabel(order.payment_status)}</span></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder('${order.order_number}')" title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            ${getOrderActionButtons(order.status, order.order_number)}
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ${data.pagination ? generatePagination(data.pagination) : ''}
            `;
        } else {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bag text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Aucune commande trouvée</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Effacer les filtres
function clearFilters() {
    document.getElementById('orderStatusFilter').value = '';
    document.getElementById('orderDateFrom').value = '';
    document.getElementById('orderDateTo').value = '';
    document.getElementById('orderSearchFilter').value = '';
    document.getElementById('orderSortFilter').value = 'created_at';
    loadOrders();
}

// Fonctions utilitaires
window.getStatusColor = function(status) {
    const colors = {
        'pending': 'warning',
        'processing': 'info',
        'delivered': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}

window.getStatusLabel = function(status) {
    const labels = {
        'pending': 'En cours de validation',
        'processing': 'En cours de livraison',
        'delivered': 'Livrée',
        'cancelled': 'Annulée'
    };
    return labels[status] || status;
}

window.getPaymentStatusColor = function(paymentStatus) {
    const colors = {
        'pending': 'warning',
        'paid': 'success',
        'failed': 'danger',
        'refunded': 'secondary'
    };
    return colors[paymentStatus] || 'secondary';
}

window.getPaymentStatusLabel = function(paymentStatus) {
    const labels = {
        'pending': 'En attente',
        'paid': 'Payé',
        'failed': 'Échec',
        'refunded': 'Remboursé'
    };
    return labels[paymentStatus] || paymentStatus;
}

/* ============================================
   TOUTES LES FONCTIONS SUIVANTES SONT MAINTENANT DÉFINIES EN HAUT DU FICHIER
   dans la section <script> initiale (lignes 12-706)
   ============================================
   
// showAddProductModal, submitProduct, editProductInternal, deleteProductInternal,
// displayCurrentImages, removeImage, submitEditForm, loadSubcategories
// sont toutes définies en haut et accessibles globalement.
// 
// Le code ci-dessous est commenté pour éviter les duplications.

/*
    // Créer le modal
    const modalHtml = `
        <div class="modal fade z-index-9x" id="addProductModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-control" name="category_id" required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sous-catégorie (optionnel)</label>
                                    <select class="form-control" name="subcategory_id" id="subcategory_select">
                                        <option value="">Sélectionnez une sous-catégorie</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="stock" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marque</label>
                                    <input type="text" class="form-control" name="brand">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Modèle</label>
                                    <input type="text" class="form-control" name="model">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Promotion (optionnel)</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Prix promo (FCFA)</label>
                                            <input type="number" class="form-control" id="add_promo_price" name="promo_price" min="0" placeholder="Ex: 750000">
                                            <small class="text-muted">Prix de vente après réduction</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">OU Réduction (%)</label>
                                            <input type="number" class="form-control" id="add_discount_percent" name="discount" min="0" max="100" value="0" placeholder="Ex: 15">
                                            <small class="text-muted">Pourcentage de réduction</small>
                                        </div>
                                    </div>
                                    <small class="text-info d-block mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Remplissez soit le prix promo, soit le pourcentage. Le système calculera l'autre automatiquement.
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="4" required></textarea>
                                    <small class="text-muted">Minimum 50 caractères</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Garantie</label>
                                    <input type="text" class="form-control" name="warranty" placeholder="Ex: 12 mois">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tags (séparés par virgule)</label>
                                    <input type="text" class="form-control" name="tags" placeholder="Ex: nouveau, promo, tendance">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Images du produit</label>
                                    <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                                    <small class="text-muted">Vous pouvez sélectionner plusieurs images (Max: 5 MB chacune)</small>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn orange-bg text-white" onclick="submitProduct()">
                            <i class="bi bi-check-circle me-2"></i>Ajouter le produit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter au DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    
    // Gérer le calcul automatique entre prix promo et réduction
    setTimeout(() => {
        const priceInput = document.querySelector('#addProductForm input[name="price"]');
        const promoInput = document.getElementById('add_promo_price');
        const discountInput = document.getElementById('add_discount_percent');
        
        // Calcul automatique du pourcentage quand on saisit un prix promo
        promoInput.addEventListener('input', function() {
            if (this.value && priceInput.value) {
                const price = parseFloat(priceInput.value);
                const promo = parseFloat(this.value);
                const discount = ((price - promo) / price * 100).toFixed(2);
                discountInput.value = discount > 0 ? discount : 0;
            }
        });
        
        // Calcul automatique du prix promo quand on saisit un pourcentage
        discountInput.addEventListener('input', function() {
            if (this.value && priceInput.value) {
                const price = parseFloat(priceInput.value);
                const discount = parseFloat(this.value);
                const promo = price * (1 - discount / 100);
                promoInput.value = Math.round(promo);
            }
        });
        
        // Recalculer si le prix change
        priceInput.addEventListener('input', function() {
            if (discountInput.value && discountInput.value > 0) {
                const price = parseFloat(this.value);
                const discount = parseFloat(discountInput.value);
                const promo = price * (1 - discount / 100);
                promoInput.value = Math.round(promo);
            }
        });
    }, 100);
    
    // Supprimer le modal du DOM quand il est fermé
    document.getElementById('addProductModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    modal.show();
}

// Soumettre le formulaire d'ajout de produit
async function submitProduct() {
    const form = document.getElementById('addProductForm');
    const formData = new FormData(form);
    
    // Ajouter le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    } else {
        showNotification('error', 'Token CSRF manquant');
        return;
    }
    
    // Validation de la description
    const description = formData.get('description');
    if (description.length < 50) {
        showNotification('danger', 'La description doit contenir au moins 50 caractères');
        return;
    }
    
    try {
        const response = await fetch('/store/api/products', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
            
            // Recharger la page pour afficher le nouveau produit
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'ajout du produit');
    }
}

// Éditer un produit - VERSION REFACTORISÉE
async function editProductInternal(id) {
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
            // Charger les données du produit dans le modal statique Blade
            const product = data.product;
            
            // Remplir les champs du formulaire
            
            const nameField = document.getElementById('edit_name');
            const descriptionField = document.getElementById('edit_description');
            const priceField = document.getElementById('edit_price');
            const stockField = document.getElementById('edit_stock');
            
            if (!nameField || !descriptionField || !priceField || !stockField) {
                console.error('❌ Champs du formulaire non trouvés!');
                throw new Error('Formulaire non trouvé');
            }
            
            document.getElementById('edit_product_id').value = product.id || '';
            nameField.value = product.name || '';
            descriptionField.value = product.description || '';
            priceField.value = product.price || '';
            stockField.value = product.stock || '';
            document.getElementById('edit_brand').value = product.brand || '';
            document.getElementById('edit_model').value = product.model || '';
            document.getElementById('edit_warranty').value = product.warranty || '';
            document.getElementById('edit_promo_price').value = product.promo_price || '';
            document.getElementById('edit_discount').value = product.discount || '';
            document.getElementById('edit_status').value = product.status || 'active';
            
            // Remplir la catégorie et charger les sous-catégories
            if (product.category_id) {
                document.getElementById('edit_category_id').value = product.category_id;
                loadSubcategories(product.category_id, product.subcategory_id);
            }
            
            // Remplir les tags (convertir le tableau en string séparée par virgules)
            const tagsField = document.getElementById('edit_tags');
            if (tagsField && product.tags) {
                tagsField.value = Array.isArray(product.tags) ? product.tags.join(', ') : product.tags;
            }
            
                name: nameField.value,
                description: descriptionField.value,
                price: priceField.value,
                stock: stockField.value
            });
            
            // Réinitialiser les images à supprimer
            window.imagesToDelete = [];
            
            // Afficher les images existantes (inclure l'image principale)
            let allImages = [];
            
            // Ajouter l'image principale si elle existe
            if (product.image) {
                allImages.push(product.image);
            }
            
            // Ajouter les autres images (sans dupliquer l'image principale)
            if (product.images && Array.isArray(product.images)) {
                product.images.forEach(img => {
                    if (img !== product.image && img) {
                        allImages.push(img);
                    }
                });
            }
            
            // Dédupliquer les images au cas où
            allImages = [...new Set(allImages)];
            
            
            displayCurrentImages(allImages);
            
            // Afficher le modal statique
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        } else {
            throw new Error(data.message || 'Produit non trouvé');
        }
    } catch (error) {
        console.error('❌ Erreur lors de l\'édition:', error);
        showNotification('error', error.message || 'Erreur lors du chargement du produit');
    }
}

// Afficher les images existantes du produit
function displayCurrentImages(images) {
    const container = document.getElementById('current_images_container');
    container.innerHTML = ''; // Vider le conteneur
    
    if (!images || images.length === 0) {
        container.innerHTML = '<p class="text-muted">Aucune image pour ce produit</p>';
        return;
    }
    
    // Affichage des images avec boutons de suppression
    images.forEach((image, index) => {
        const imageUrl = image.startsWith('products/') || image.startsWith('images/') 
            ? `/storage/${image}` 
            : image;
        
        const imageCard = document.createElement('div');
        imageCard.className = 'position-relative';
        imageCard.style.width = '120px';
        imageCard.style.height = '120px';
        imageCard.innerHTML = `
            <img src="${imageUrl}" 
                 alt="Image ${index + 1}" 
                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 2px solid #dee2e6;"
                 onerror="this.src='/images/placeholder.jpg'">
            <button type="button" 
                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                    style="padding: 0.25rem 0.5rem; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"
                    onclick="removeImage(${index})"
                    data-image-url="${image}"
                    title="Supprimer cette image">
                <i class="bi bi-x-lg" style="font-size: 0.8rem;"></i>
                        </button>
        `;
        container.appendChild(imageCard);
    });
    
    // Sauvegarder la liste complète des images pour la suppression
    window.allImages = images;
    // Sauvegarder l'index des images à supprimer
    window.imagesToDelete = window.imagesToDelete || [];
}

// Variable globale pour stocker les images à supprimer
window.imagesToDelete = [];

// Fonction pour marquer une image comme à supprimer
function removeImage(index) {
    if (confirm('Voulez-vous vraiment supprimer cette image ?')) {
        // Récupérer l'URL de l'image à partir du bouton
        const buttons = document.querySelectorAll('#current_images_container button');
        if (buttons[index]) {
            const imageUrl = buttons[index].dataset.imageUrl;
            if (imageUrl && !window.imagesToDelete.includes(imageUrl)) {
                window.imagesToDelete.push(imageUrl);
            }
            
            // Marquer visuellement l'image comme supprimée
            const imageDiv = buttons[index].closest('div.position-relative');
            if (imageDiv) {
                imageDiv.style.opacity = '0.5';
                imageDiv.style.pointerEvents = 'none';
            }
            
            showNotification('success', 'Image marquée pour suppression');
        } else {
            showNotification('error', 'Erreur: Image non trouvée');
        }
    }
}

// Ancienne fonction supprimée - remplacée par editProduct() et submitEditForm()

// Mettre à jour un produit
// Fonction locale pour ouvrir le modal de modification
function editProductLocal(productId) {
    console.log('🔧 Ouverture du modal pour le produit:', productId);
    
    // Remplir le formulaire avec les données du produit
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        console.error('❌ Token CSRF non trouvé !');
        showNotification('error', 'Token CSRF manquant. Veuillez recharger la page.');
        return;
    }
    
    fetch(`/store/api/products/${productId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.product) {
            const product = data.product;
            
            // Remplir les champs du formulaire
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_brand').value = product.brand || '';
            document.getElementById('edit_model').value = product.model || '';
            document.getElementById('edit_warranty').value = product.warranty || '';
            document.getElementById('edit_status').value = product.status;
            
            // Afficher l'image actuelle
            if (product.image) {
                document.getElementById('current_image_preview').innerHTML = `
                    <div class="d-flex align-items-center mb-2">
                        <img src="/storage/${product.image}" alt="Image actuelle" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                        <div class="ms-3">
                            <small class="text-muted">Image actuelle</small>
                        </div>
                    </div>
                `;
            }
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        } else {
            showNotification('error', 'Erreur lors du chargement du produit');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du chargement du produit');
    });
}

// Fonction simplifiée pour soumettre le formulaire
function submitEditForm() {
    const form = document.getElementById('editProductForm');
    const productId = document.getElementById('edit_product_id').value;
    
    console.log('🔧 Soumission du formulaire pour le produit:', productId);
    
    // Vérifier le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        console.error('❌ Token CSRF non trouvé pour la soumission !');
        showNotification('error', 'Token CSRF manquant. Veuillez recharger la page.');
        return;
    }
    
    // Créer FormData
    const formData = new FormData(form);
    
    // Convertir le statut en is_active
    const status = formData.get('status');
    formData.append('is_active', status === 'active' ? '1' : '0');
    
    // Ajouter les indices des images à supprimer
    if (window.imagesToDelete && window.imagesToDelete.length > 0) {
        window.imagesToDelete.forEach(index => {
            formData.append('images_to_delete[]', index);
        });
        console.log('🗑️ Images à supprimer:', window.imagesToDelete);
    }
    
    // Debug: Afficher le contenu du FormData
    console.log('🔍 Contenu du FormData:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
    }
    
    // Envoyer la requête en POST avec _method=PUT pour que Laravel puisse parser le FormData
    formData.append('_method', 'PUT');
    
    fetch(`/store/api/products/${productId}`, {
            method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();
            
            // Recharger la page pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour');
    });
}

// Supprimer un produit
async function deleteProductInternal(id) {
    if (!confirm('Voulez-vous vraiment supprimer ce produit ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch(`/store/api/products/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            // Recharger la page pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la suppression');
    }
}

// ============================================
// FIN DES FONCTIONS DUPLIQUÉES - TOUT EST EN HAUT
// ============================================
*/

// Test de débogage au chargement de la page
console.log('🔧 DEBUG: Fonctions définies:', {
    'window.editProduct': typeof window.editProduct,
    'window.deleteProduct': typeof window.deleteProduct,
    'editProductInternal': typeof editProductInternal,
    'deleteProductInternal': typeof deleteProductInternal
});

function viewOrder(orderNumber) {
    // Rediriger vers la page de détails de la commande
    window.open(`/store/orders/${orderNumber}?token=${token}`, '_blank');
}

// Obtenir les boutons d'action pour les commandes
window.getOrderActionButtons = function(status, orderNumber) {
    const buttons = [];
    
    if (status === 'pending') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-warning" onclick="quickAction('process', '${orderNumber}')" title="Marquer en cours de livraison">
                <i class="bi bi-gear"></i>
            </button>
        `);
        buttons.push(`
            <button class="btn btn-sm btn-outline-danger" onclick="quickAction('cancel', '${orderNumber}')" title="Annuler la commande">
                <i class="bi bi-x-circle"></i>
            </button>
        `);
    } else if (status === 'processing') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-success" onclick="quickAction('deliver', '${orderNumber}')" title="Marquer comme livrée">
                <i class="bi bi-check-circle"></i>
            </button>
        `);
        buttons.push(`
            <button class="btn btn-sm btn-outline-danger" onclick="quickAction('cancel', '${orderNumber}')" title="Annuler la commande">
                <i class="bi bi-x-circle"></i>
            </button>
        `);
    } else if (status === 'delivered') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-info" onclick="quickAction('process', '${orderNumber}')" title="Retour en cours de livraison">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        `);
        buttons.push(`
            <button class="btn btn-sm btn-outline-danger" onclick="quickAction('cancel', '${orderNumber}')" title="Annuler la commande">
                <i class="bi bi-x-circle"></i>
            </button>
        `);
    } else if (status === 'cancelled') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-primary" onclick="quickAction('pending', '${orderNumber}')" title="Réactiver la commande">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        `);
        buttons.push(`
            <button class="btn btn-sm btn-outline-warning" onclick="quickAction('process', '${orderNumber}')" title="Marquer en cours de livraison">
                <i class="bi bi-gear"></i>
            </button>
        `);
    }
    
    return buttons.join('');
}

// Obtenir les boutons d'action pour les statuts de paiement
window.getPaymentActionButtons = function(paymentStatus, orderNumber) {
    const buttons = [];
    
    if (paymentStatus === 'pending') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-success" onclick="quickPaymentAction('paid', '${orderNumber}')" title="Marquer comme payé">
                <i class="bi bi-check-circle"></i>
            </button>
        `);
    } else if (paymentStatus === 'paid') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-warning" onclick="quickPaymentAction('pending', '${orderNumber}')" title="Paiement en attente">
                <i class="bi bi-clock"></i>
            </button>
        `);
    }
    
    return buttons.join('');
}

// Actions rapides sur les statuts de paiement
window.quickPaymentAction = async function(action, orderNumber) {
    try {
        let paymentStatus = '';
        let message = '';
        
        switch (action) {
            case 'paid':
                paymentStatus = 'paid';
                message = 'Commande marquée comme payée';
                break;
            case 'pending':
                paymentStatus = 'pending';
                message = 'Statut de paiement remis en attente';
                break;
            default:
                throw new Error('Action de paiement non reconnue');
        }
        
        const response = await fetch(`/store/api/orders/${orderNumber}/payment-status`, {
            method: 'PUT',
            headers: {'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                payment_status: paymentStatus,
                reason: 'Action rapide depuis le dashboard'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', message);
            loadOrders(); // Recharger la liste des commandes
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'action de paiement');
    }
}

// Actions rapides sur les commandes
async function quickAction(action, orderNumber) {
    try {
        let endpoint = '';
        let message = '';
        let status = '';
        
        switch (action) {
            case 'process':
                endpoint = `/store/api/orders/${orderNumber}/status`;
                status = 'processing';
                message = 'Commande marquée en cours de livraison';
                break;
            case 'deliver':
                endpoint = `/store/api/orders/${orderNumber}/status`;
                status = 'delivered';
                message = 'Commande marquée comme livrée';
                break;
            case 'cancel':
                endpoint = `/store/api/orders/${orderNumber}/status`;
                status = 'cancelled';
                message = 'Commande annulée';
                break;
            case 'pending':
                endpoint = `/store/api/orders/${orderNumber}/status`;
                status = 'pending';
                message = 'Commande réactivée';
                break;
            default:
                throw new Error('Action non reconnue');
        }
        
        const response = await fetch(endpoint, {
            method: 'PUT',
            headers: {'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: status,
                reason: 'Action rapide depuis le dashboard'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', message);
            loadOrders(); // Recharger la liste des commandes
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'action');
    }
}

// Gestion des formulaires
document.addEventListener('DOMContentLoaded', function() {
    const updateStoreForm = document.getElementById('updateStoreForm');
    if (updateStoreForm) {
        updateStoreForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await updateStoreInfo();
        });
    }
    
    const updateSocialForm = document.getElementById('updateSocialForm');
    if (updateSocialForm) {
        updateSocialForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await updateSocialLinks();
        });
    }
});

// Mettre à jour les informations de la boutique
async function updateStoreInfo() {
    const formData = {
        name: document.getElementById('store_name').value,
        email: document.getElementById('store_email').value,
        phone: document.getElementById('store_phone').value,
        category_id: document.getElementById('store_category').value,
        description: document.getElementById('store_description').value,
        address: document.getElementById('store_address').value,
        city: document.getElementById('store_city').value,
    };

    try {
        showNotification('info', 'Mise à jour en cours...');
        
        const response = await fetch('/store/api/update', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Boutique mise à jour avec succès !');
        } else {
            showNotification('danger', data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    }
}

// Upload du logo
async function uploadLogo() {
    const fileInput = document.getElementById('new_logo');
    const file = fileInput.files[0];
    
    if (!file) {
        showNotification('warning', 'Veuillez sélectionner un fichier');
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        showNotification('warning', 'Veuillez sélectionner une image');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        showNotification('warning', 'L\'image ne doit pas dépasser 5MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('logo', file);
    
    try {
        showNotification('info', 'Upload du logo en cours...');
        
        const response = await fetch('/store/api/upload-logo', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
        });
        
        const data = await response.json();
        
            if (data.success) {
                console.log('✅ Upload logo réussi, affichage notification...');
                showNotification('success', 'Logo mis à jour avec succès !');
                
                // Attendre un peu que l'image soit complètement écrite sur le serveur
                setTimeout(() => {
                    console.log('🔄 Début du rechargement des images logo...');
                    
                    // Cibler explicitement les images identifiées avec rechargement forcé
                    const logoSidebar = document.getElementById('storeLogoSidebar');
                    const logoSettings = document.getElementById('storeLogoSettings');
                    
                    console.log('🖼️ Mise à jour des images logo:', { 
                        logoSidebar: !!logoSidebar, 
                        logoSettings: !!logoSettings, 
                        newUrl: data.logo_url 
                    });
                    
                    if (logoSidebar) {
                        forceImageReload(logoSidebar, data.logo_url, 5, 2000); // 5 tentatives, 2s entre chaque
                    }
                    if (logoSettings) {
                        forceImageReload(logoSettings, data.logo_url, 5, 2000);
                    }
                }, 800); // Attendre un peu avant de commencer le rechargement
                
                // Vider le champ de fichier
                fileInput.value = '';
                
                // Rediriger vers l'onglet paramètres après un court délai
                setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', 'settings');
                    window.location.href = url.toString();
                }, 1200);
            } else {
            showNotification('danger', data.message || 'Erreur lors de l\'upload');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'upload');
    }
}

// Upload de la bannière
async function uploadBanner() {
    const fileInput = document.getElementById('new_banner');
    const file = fileInput.files[0];
    
    if (!file) {
        showNotification('warning', 'Veuillez sélectionner un fichier');
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        showNotification('warning', 'Veuillez sélectionner une image');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        showNotification('warning', 'L\'image ne doit pas dépasser 5MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('banner', file);
    
    try {
        showNotification('info', 'Upload de la bannière en cours...');
        
        const response = await fetch('/store/api/upload-banner', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
        });
        
        const data = await response.json();
        
            if (data.success) {
                console.log('✅ Upload bannière réussi, affichage notification...');
                showNotification('success', 'Bannière mise à jour avec succès !');
                
                // Attendre un peu que l'image soit complètement écrite sur le serveur
                setTimeout(() => {
                    console.log('🔄 Début du rechargement de l\'image bannière...');
                    
                    // Cibler explicitement l'image identifiée avec rechargement forcé
                    const bannerSettings = document.getElementById('storeBannerSettings');
                    
                    console.log('🖼️ Mise à jour de l\'image bannière:', { 
                        bannerSettings: !!bannerSettings, 
                        newUrl: data.banner_url 
                    });
                    
                    if (bannerSettings) {
                        forceImageReload(bannerSettings, data.banner_url, 5, 2000); // 5 tentatives, 2s entre chaque
                    }
                }, 800); // Attendre un peu avant de commencer le rechargement
                
                // Vider le champ de fichier
                fileInput.value = '';

                // Rediriger vers l'onglet paramètres après un court délai
                setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', 'settings');
                    window.location.href = url.toString();
                }, 1200);
            } else {
            showNotification('danger', data.message || 'Erreur lors de l\'upload');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'upload');
    }
}

// Mettre à jour les statistiques
async function updateStats() {
    try {
        const response = await fetch('/store/api/stats', {
            headers: {'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Mettre à jour la carte "Total Produits"
            const totalProductsEl = document.getElementById('statTotalProducts');
            if (totalProductsEl) {
                totalProductsEl.textContent = data.stats.total_products;
            }
            
            // Mettre à jour le badge dans le menu "Produits"
            const productsBadge = document.querySelector('a[href="#products"] .badge');
            if (productsBadge) {
                productsBadge.textContent = data.stats.total_products;
            }
            
            console.log('✅ Statistiques mises à jour:', data.stats.total_products, 'produits');
        }
    } catch (error) {
        console.error('Erreur mise à jour stats:', error);
    }
}

// Mettre à jour les liens sociaux
async function updateSocialLinks() {
    const formData = {
        facebook: document.getElementById('facebook_url').value,
        instagram: document.getElementById('instagram_url').value,
        twitter: document.getElementById('twitter_url').value,
        website: document.getElementById('website_url').value,
    };

    try {
        showNotification('info', 'Mise à jour des liens sociaux...');
        
        const response = await fetch('/store/api/update-social', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Liens sociaux mis à jour avec succès !');
        } else {
            showNotification('danger', data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    }
}

// Basculer le statut de la boutique
async function toggleStoreStatus(status) {
    const action = status === 'suspended' ? 'suspendre' : (status === 'active' ? 'activer' : 'modifier le statut');
    
    if (!confirm(`Êtes-vous sûr de vouloir ${action} votre boutique ?`)) {
        return;
    }

    try {
        showNotification('info', `${action.charAt(0).toUpperCase() + action.slice(1)} la boutique...`);
        
        const response = await fetch('/store/api/toggle-status', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ status })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', `Boutique ${action}ée avec succès !`);
            // Recharger la page pour mettre à jour l'interface
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('danger', data.message || 'Erreur lors de l\'opération');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'opération');
    }
}

// Supprimer la boutique
async function deleteStore() {
    const confirmText = 'SUPPRIMER';
    const userInput = prompt(`ATTENTION: Cette action est irréversible et supprimera définitivement votre boutique et tous vos produits.\n\nPour confirmer, tapez "${confirmText}" :`);
    
    if (userInput !== confirmText) {
        showNotification('info', 'Suppression annulée');
        return;
    }

    try {
        showNotification('warning', 'Suppression de la boutique en cours...');
        
        const response = await fetch('/store/api/delete', {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Boutique supprimée avec succès');
            // Rediriger vers la page d'accueil
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        } else {
            showNotification('danger', data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la suppression');
    }
}

// Fonction pour forcer le rechargement d'une image avec délai et vérification
function forceImageReload(imgElement, newSrc, maxRetries = 3, delay = 1000) {
    if (!imgElement) return;
    
    let retryCount = 0;
    
    // Vérifier d'abord si l'URL est accessible
    function checkUrlAccessibility(url, callback) {
        fetch(url, { 
            method: 'HEAD',
            mode: 'no-cors' // Permet de contourner les problèmes CORS
        })
        .then(() => {
            console.log('✅ URL accessible:', url);
            callback(true);
        })
        .catch(error => {
            console.warn('⚠️ URL non accessible:', url, error);
            callback(false);
        });
    }
    
    function attemptReload() {
        console.log(`🔄 Tentative ${retryCount + 1}/${maxRetries} de rechargement de l'image:`, newSrc);
        
        // Créer un nouvel élément image pour forcer le téléchargement
        const tempImg = new Image();
        
        tempImg.onload = function() {
            console.log('✅ Image chargée avec succès, mise à jour de l\'élément');
            imgElement.src = newSrc;
            imgElement.style.opacity = '0.8';
            setTimeout(() => {
                imgElement.style.opacity = '1';
            }, 100);
        };
        
        tempImg.onerror = function() {
            retryCount++;
            console.warn(`❌ Erreur de chargement de l'image (tentative ${retryCount}/${maxRetries}):`, newSrc);
            
            // Tester l'accessibilité de l'URL
            checkUrlAccessibility(newSrc, (isAccessible) => {
                if (!isAccessible) {
                    console.error('🚫 URL non accessible, abandon du rechargement');
                    showNotification('warning', 'L\'image n\'est pas accessible sur le serveur. Vérifiez les permissions.');
                    return;
                }
                
                if (retryCount < maxRetries) {
                    console.log(`⏳ Nouvelle tentative dans ${delay}ms...`);
                    setTimeout(attemptReload, delay);
                } else {
                    console.error('💥 Échec définitif du rechargement de l\'image');
                    showNotification('warning', 'Erreur de chargement de l\'image après plusieurs tentatives. Vérifiez les permissions du serveur.');
                }
            });
        };
        
        // Ajouter un timestamp pour forcer le rechargement
        const srcWithTimestamp = newSrc + (newSrc.includes('?') ? '&' : '?') + 't=' + Date.now();
        tempImg.src = srcWithTimestamp;
    }
    
    // Délai initial avant la première tentative
    setTimeout(attemptReload, 500);
}

// Fonction pour forcer le rechargement des images
function refreshImages(imageType, newUrl) {
    const timestamp = '?t=' + Date.now();
    
    if (imageType === 'logo') {
        // Mettre à jour uniquement les images de logo de la boutique (pas le logo principal du site)
        const logoSelectors = [
            'img[alt="Logo"]',
            'img[alt="' + '<?php echo e($store->name); ?>' + '"]',
            '.card-body .img-fluid.rounded-circle'
        ];
        
        logoSelectors.forEach(selector => {
            // Limiter la recherche au contexte du dashboard uniquement
            const dashboardContainer = document.querySelector('.store-dashboard');
            const searchScope = dashboardContainer || document;
            const images = searchScope.querySelectorAll(selector);
            
            images.forEach(img => {
                // Exclure le logo principal du site (qui contient 'logo.png')
                if (!img.src.includes('logo.png') && 
                    (img.src.includes('logo') || img.alt.includes('Logo') || img.alt.includes('<?php echo e($store->name); ?>'))) {
                    // Forcer le rechargement en vidant d'abord le src
                    const currentSrc = img.src;
                    img.src = '';
                    setTimeout(() => {
                        img.src = newUrl + timestamp;
                    }, 50);
                }
            });
        });
    } else if (imageType === 'banner') {
        // Mettre à jour toutes les images de bannière
        const bannerSelectors = [
            'img[alt="Bannière"]',
            'img[src*="banner"]'
        ];
        
        bannerSelectors.forEach(selector => {
            // Limiter la recherche au contexte du dashboard uniquement
            const dashboardContainer = document.querySelector('.store-dashboard');
            const searchScope = dashboardContainer || document;
            const images = searchScope.querySelectorAll(selector);
            
            images.forEach(img => {
                if (img.src.includes('banner') || img.alt.includes('Bannière')) {
                    // Forcer le rechargement en vidant d'abord le src
                    img.src = '';
                    setTimeout(() => {
                        img.src = newUrl + timestamp;
                    }, 50);
                }
            });
        });
    }
}

// Fonction de notification spécifique au dashboard
window.showNotification = function(type, message) {
    console.log('🔔 showNotification appelée:', type, message);
    
    const toastElement = document.getElementById('dashboardNotificationToast');
    const toastIcon = document.getElementById('dashboardToastIcon');
    const toastTitle = document.getElementById('dashboardToastTitle');
    const toastBody = document.getElementById('dashboardToastBody');
    
    if (!toastElement || !toastIcon || !toastTitle || !toastBody) {
        console.error('❌ Éléments toast du dashboard non trouvés');
        alert(message);
        return;
    }
    
    console.log('✅ Éléments toast trouvés, affichage de la notification');
    
    // Configuration selon le type
    const configs = {
        'success': {
            icon: 'bi-check-circle-fill',
            iconColor: 'text-success',
            title: 'Succès',
            bgClass: 'bg-success-subtle'
        },
        'error': {
            icon: 'bi-exclamation-circle-fill',
            iconColor: 'text-danger',
            title: 'Erreur',
            bgClass: 'bg-danger-subtle'
        },
        'warning': {
            icon: 'bi-exclamation-triangle-fill',
            iconColor: 'text-warning',
            title: 'Attention',
            bgClass: 'bg-warning-subtle'
        },
        'info': {
            icon: 'bi-info-circle-fill',
            iconColor: 'text-info',
            title: 'Information',
            bgClass: 'bg-info-subtle'
        }
    };
    
    const config = configs[type] || configs['info'];
    
    // Mettre à jour le contenu
    toastIcon.className = `bi ${config.icon} ${config.iconColor}`;
    toastTitle.textContent = config.title;
    toastBody.textContent = message;
    
    // Appliquer le style de fond
    toastElement.className = `toast ${config.bgClass}`;
    
    console.log('🎨 Toast configuré, affichage...');
    
    // Afficher le toast
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'error' ? 5000 : 3000
    });
    
    toast.show();
    console.log('🚀 Toast affiché !');
}

// Fonction pour prévisualiser l'image principale sélectionnée
function previewImage(input, type = 'main') {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Fonction pour prévisualiser plusieurs images
function previewMultipleImages(input) {
    const previewContainer = document.getElementById('multipleImagesPreview');
    previewContainer.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        const maxFiles = Math.min(input.files.length, 5); // Limiter à 5 images
        
        for (let i = 0; i < maxFiles; i++) {
            const file = input.files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imageDiv = document.createElement('div');
                imageDiv.className = 'd-inline-block me-2 mb-2';
                imageDiv.innerHTML = `
                    <img src="${e.target.result}" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #dee2e6;" 
                         class="img-thumbnail">
                    <div class="small text-muted text-center">Image ${i + 1}</div>
                `;
                previewContainer.appendChild(imageDiv);
            };
            
            reader.readAsDataURL(file);
        }
        
        if (input.files.length > 5) {
            const warningDiv = document.createElement('div');
            warningDiv.className = 'alert alert-warning small mt-2';
            warningDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Seules les 5 premières images seront prises en compte.';
            previewContainer.appendChild(warningDiv);
        }
    }
}

// Fonction pour supprimer l'image actuelle
function removeCurrentImage() {
    if (confirm('Êtes-vous sûr de vouloir supprimer l\'image actuelle ?')) {
        // Masquer l'image actuelle
        const currentImageDiv = document.querySelector('#editProductModal .d-flex.align-items-center.mb-2');
        if (currentImageDiv) {
            currentImageDiv.style.display = 'none';
        }
        
        // Ajouter un champ caché pour indiquer la suppression
        const form = document.getElementById('editProductForm');
        let hiddenInput = form.querySelector('input[name="remove_image"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'remove_image';
            form.appendChild(hiddenInput);
        }
        hiddenInput.value = '1';
        
        // NE PAS désactiver le champ de fichier - permettre l'ajout d'une nouvelle image
        const fileInput = form.querySelector('input[name="image"]');
        if (fileInput) {
            fileInput.disabled = false;
            fileInput.value = ''; // Vider le champ
        }
        
        // Masquer l'aperçu de l'image principale
        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) {
            imagePreview.style.display = 'none';
        }
        
        showNotification('success', 'Image marquée pour suppression. Vous pouvez ajouter une nouvelle image.');
    }
}

// ========================================
// GESTION DE LA PERSISTANCE DES ONGLETS
// ========================================

// Sauvegarder l'onglet actif dans le localStorage
function saveActiveTab(tabId) {
    localStorage.setItem('activeStoreTab', tabId);
}

// Restaurer l'onglet actif depuis le localStorage
function restoreActiveTab() {
    const savedTab = localStorage.getItem('activeStoreTab');
    if (savedTab) {
        
        // Désactiver tous les onglets
        document.querySelectorAll('#storeTabs .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Activer l'onglet sauvegardé
        const tabLink = document.querySelector(`#storeTabs a[href="#${savedTab}"]`);
        const tabPane = document.getElementById(savedTab);
        
        if (tabLink && tabPane) {
            tabLink.classList.add('active');
            tabPane.classList.add('show', 'active');
        } else {
        }
    } else {
    }
}

// Gérer les clics sur les onglets
function setupTabListeners() {
    document.querySelectorAll('#storeTabs .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const tabId = this.getAttribute('href').substring(1); // Enlever le #
            saveActiveTab(tabId);
        });
    });
}

// Initialiser la gestion des onglets au chargement de la page
// loadSubcategories est défini en haut du fichier
/* CHARGEMENT DES SOUS-CATÉGORIES - FONCTION DÉFINIE EN HAUT
function loadSubcategories(categoryId, selectedSubcategoryId = null) {
    const subcategorySelect = document.getElementById('edit_subcategory_id') || document.getElementById('subcategory_select');
    
    if (!subcategorySelect) {
        console.warn('Champ sous-catégorie non trouvé');
        return;
    }
    
    // Vider les options existantes
    subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie</option>';
    
    if (!categoryId) {
        return;
    }
    
    // Charger les sous-catégories via AJAX
    fetch(`/api/categories/${categoryId}/subcategories`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.subcategories) {
            data.subcategories.forEach(subcategory => {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = subcategory.name;
                if (selectedSubcategoryId && subcategory.id == selectedSubcategoryId) {
                    option.selected = true;
                }
                subcategorySelect.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des sous-catégories:', error);
    });
}

// SYNCHRONISATION PRIX PROMO / RÉDUCTION
// ========================================
function synchronizePriceAndDiscount() {
    const priceInput = document.getElementById('edit_price');
    const promoPriceInput = document.getElementById('edit_promo_price');
    const discountInput = document.getElementById('edit_discount');
    
    // Si le prix promo change, calculer la réduction
    promoPriceInput.addEventListener('input', function() {
        const price = parseFloat(priceInput.value) || 0;
        const promoPrice = parseFloat(this.value) || 0;
        
        if (price > 0 && promoPrice > 0) {
            const discount = ((price - promoPrice) / price * 100).toFixed(2);
            discountInput.value = discount > 0 ? discount : 0;
        } else if (promoPrice === 0) {
            discountInput.value = '';
        }
    });
    
    // Si la réduction change, calculer le prix promo
    discountInput.addEventListener('input', function() {
        const price = parseFloat(priceInput.value) || 0;
        const discount = parseFloat(this.value) || 0;
        
        if (price > 0 && discount > 0) {
            const promoPrice = price * (1 - discount / 100);
            promoPriceInput.value = promoPrice.toFixed(2);
        } else if (discount === 0) {
            promoPriceInput.value = '';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // IMPORTANT: Restaurer l'onglet actif EN PREMIER pour éviter les conflits
    restoreActiveTab();
    
    // Configurer les écouteurs d'événements
    setupTabListeners();
    
    // Synchroniser prix promo et réduction
    synchronizePriceAndDiscount();
    
    // Écouter les changements de catégorie pour charger les sous-catégories
    const categorySelects = document.querySelectorAll('[name="category_id"]');
    categorySelects.forEach(select => {
        select.addEventListener('change', function() {
            loadSubcategories(this.value);
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<!-- Script de modification de produit refactorisé -->



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/store/dashboard.blade.php ENDPATH**/ ?>