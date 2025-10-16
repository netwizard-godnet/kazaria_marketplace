@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/store.css') }}">

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold blue-color mb-0">
                        <i class="bi bi-bag me-2"></i>Détails de la commande
                    </h2>
                    <p class="text-muted mb-0" id="orderNumber">Chargement...</p>
                </div>
                <div>
                    <a href="{{ route('store.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour au dashboard
                    </a>
                </div>
            </div>

            <!-- Contenu principal -->
            <div id="orderDetailsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des détails de la commande...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de changement de statut -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Changer le statut de la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Nouveau statut</label>
                        <select class="form-select" id="newStatus" required>
                            <option value="pending">En attente</option>
                            <option value="processing">En préparation</option>
                            <option value="shipped">Expédiée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">Notes (optionnel)</label>
                        <textarea class="form-control" id="statusNotes" rows="3" placeholder="Ajoutez des notes sur le changement de statut..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="updateOrderStatus()">Mettre à jour</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'expédition -->
<div class="modal fade" id="shipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marquer comme expédiée</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shipForm">
                    <div class="mb-3">
                        <label for="trackingNumber" class="form-label">Numéro de suivi</label>
                        <input type="text" class="form-control" id="trackingNumber" placeholder="Ex: 1234567890">
                    </div>
                    <div class="mb-3">
                        <label for="shippingCompany" class="form-label">Compagnie de livraison</label>
                        <input type="text" class="form-control" id="shippingCompany" placeholder="Ex: DHL, FedEx, Chronopost...">
                    </div>
                    <div class="mb-3">
                        <label for="shipNotes" class="form-label">Notes d'expédition</label>
                        <textarea class="form-control" id="shipNotes" rows="3" placeholder="Informations supplémentaires sur l'expédition..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="markAsShipped()">Marquer comme expédiée</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'annulation -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Annuler la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cancelForm">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action est irréversible. La commande sera définitivement annulée.
                    </div>
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Raison de l'annulation <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancelReason" rows="4" required placeholder="Expliquez pourquoi vous annulez cette commande..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="cancelOrder()">Confirmer l'annulation</button>
            </div>
        </div>
    </div>
</div>

<script>
const token = localStorage.getItem('auth_token');
let currentOrder = null;

// Récupérer le numéro de commande depuis l'URL
const urlParams = new URLSearchParams(window.location.search);
const orderNumber = urlParams.get('order');

if (!orderNumber) {
    document.getElementById('orderDetailsContainer').innerHTML = `
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-2"></i>
            Numéro de commande non fourni.
        </div>
    `;
} else {
    document.getElementById('orderNumber').textContent = `Commande #${orderNumber}`;
    loadOrderDetails();
}

// Charger les détails de la commande
async function loadOrderDetails() {
    const container = document.getElementById('orderDetailsContainer');
    
    try {
        const response = await fetch(`/api/store/orders/${orderNumber}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentOrder = data.order;
            displayOrderDetails(data.order);
        } else {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    ${data.message}
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                Erreur lors du chargement des détails de la commande.
            </div>
        `;
    }
}

// Afficher les détails de la commande
function displayOrderDetails(order) {
    const container = document.getElementById('orderDetailsContainer');
    
    const statusBadge = getStatusBadge(order.status);
    const paymentBadge = getPaymentBadge(order.payment_status);
    
    container.innerHTML = `
        <div class="row">
            <!-- Informations générales -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Informations de la commande</h5>
                        <div>
                            ${statusBadge}
                            ${paymentBadge}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Numéro de commande :</strong><br>
                                <span class="text-muted">${order.order_number}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Date de commande :</strong><br>
                                <span class="text-muted">${new Date(order.created_at).toLocaleDateString('fr-FR', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Méthode de paiement :</strong><br>
                                <span class="text-muted">${getPaymentMethodLabel(order.payment_method)}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Dernière mise à jour :</strong><br>
                                <span class="text-muted">${new Date(order.updated_at).toLocaleDateString('fr-FR', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Articles de la commande -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Articles commandés (${order.items.length})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${order.items.map(item => `
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="${item.product_image || '/images/produit.jpg'}" 
                                                         alt="${item.product_name}" 
                                                         class="me-3" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                    <div>
                                                        <strong>${item.product_name}</strong>
                                                        ${item.product ? `<br><small class="text-muted">Réf: ${item.product.slug}</small>` : ''}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>${new Intl.NumberFormat('fr-FR').format(item.price)} FCFA</td>
                                            <td>${item.quantity}</td>
                                            <td><strong>${new Intl.NumberFormat('fr-FR').format(item.total)} FCFA</strong></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes du client -->
                ${order.customer_notes ? `
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Notes du client</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">${order.customer_notes}</p>
                    </div>
                </div>
                ` : ''}
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Informations de livraison -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Adresse de livraison</h5>
                    </div>
                    <div class="card-body">
                        <address class="mb-0">
                            <strong>${order.shipping_name}</strong><br>
                            ${order.shipping_address}<br>
                            ${order.shipping_city}${order.shipping_postal_code ? ', ' + order.shipping_postal_code : ''}<br>
                            ${order.shipping_country}<br>
                            <i class="bi bi-telephone me-1"></i>${order.shipping_phone}<br>
                            <i class="bi bi-envelope me-1"></i>${order.shipping_email}
                        </address>
                    </div>
                </div>

                <!-- Résumé financier -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Résumé financier</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total :</span>
                            <span>${new Intl.NumberFormat('fr-FR').format(order.subtotal)} FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Frais de livraison :</span>
                            <span>${new Intl.NumberFormat('fr-FR').format(order.shipping_cost)} FCFA</span>
                        </div>
                        ${order.tax > 0 ? `
                        <div class="d-flex justify-content-between mb-2">
                            <span>Taxes :</span>
                            <span>${new Intl.NumberFormat('fr-FR').format(order.tax)} FCFA</span>
                        </div>
                        ` : ''}
                        ${order.discount > 0 ? `
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Remise :</span>
                            <span>-${new Intl.NumberFormat('fr-FR').format(order.discount)} FCFA</span>
                        </div>
                        ` : ''}
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total :</span>
                            <span class="orange-color">${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            ${getActionButtons(order.status)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Obtenir les boutons d'action selon le statut
function getActionButtons(status) {
    const buttons = [];
    
    if (status === 'pending') {
        buttons.push(`
            <button class="btn btn-primary" onclick="showStatusModal()">
                <i class="bi bi-arrow-clockwise me-2"></i>Changer le statut
            </button>
        `);
        buttons.push(`
            <button class="btn btn-success" onclick="showShipModal()">
                <i class="bi bi-truck me-2"></i>Marquer comme expédiée
            </button>
        `);
        buttons.push(`
            <button class="btn btn-danger" onclick="showCancelModal()">
                <i class="bi bi-x-circle me-2"></i>Annuler la commande
            </button>
        `);
    } else if (status === 'processing') {
        buttons.push(`
            <button class="btn btn-primary" onclick="showStatusModal()">
                <i class="bi bi-arrow-clockwise me-2"></i>Changer le statut
            </button>
        `);
        buttons.push(`
            <button class="btn btn-success" onclick="showShipModal()">
                <i class="bi bi-truck me-2"></i>Marquer comme expédiée
            </button>
        `);
    } else if (status === 'shipped') {
        buttons.push(`
            <button class="btn btn-primary" onclick="showStatusModal()">
                <i class="bi bi-arrow-clockwise me-2"></i>Changer le statut
            </button>
        `);
    } else if (status === 'delivered') {
        buttons.push(`
            <button class="btn btn-outline-primary" disabled>
                <i class="bi bi-check-circle me-2"></i>Commande livrée
            </button>
        `);
    } else if (status === 'cancelled') {
        buttons.push(`
            <button class="btn btn-outline-danger" disabled>
                <i class="bi bi-x-circle me-2"></i>Commande annulée
            </button>
        `);
    }
    
    return buttons.join('');
}

// Obtenir le badge de statut
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">En attente</span>',
        'processing': '<span class="badge bg-info">En préparation</span>',
        'shipped': '<span class="badge bg-primary">Expédiée</span>',
        'delivered': '<span class="badge bg-success">Livrée</span>',
        'cancelled': '<span class="badge bg-danger">Annulée</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Inconnu</span>';
}

// Obtenir le badge de paiement
function getPaymentBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Paiement en attente</span>',
        'paid': '<span class="badge bg-success">Payé</span>',
        'failed': '<span class="badge bg-danger">Échec de paiement</span>',
        'refunded': '<span class="badge bg-info">Remboursé</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Inconnu</span>';
}

// Obtenir le label de méthode de paiement
function getPaymentMethodLabel(method) {
    const labels = {
        'card': 'Carte bancaire',
        'mobile_money': 'Mobile Money',
        'cash_on_delivery': 'Paiement à la livraison'
    };
    return labels[method] || method;
}

// Afficher le modal de changement de statut
function showStatusModal() {
    if (currentOrder) {
        document.getElementById('newStatus').value = currentOrder.status;
    }
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

// Afficher le modal d'expédition
function showShipModal() {
    new bootstrap.Modal(document.getElementById('shipModal')).show();
}

// Afficher le modal d'annulation
function showCancelModal() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

// Mettre à jour le statut de la commande
async function updateOrderStatus() {
    const newStatus = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    
    try {
        const response = await fetch(`/api/store/orders/${orderNumber}/status`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus,
                notes: notes
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Statut mis à jour avec succès');
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            loadOrderDetails(); // Recharger les détails
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour du statut');
    }
}

// Marquer comme expédiée
async function markAsShipped() {
    const trackingNumber = document.getElementById('trackingNumber').value;
    const shippingCompany = document.getElementById('shippingCompany').value;
    const notes = document.getElementById('shipNotes').value;
    
    try {
        const response = await fetch(`/api/store/orders/${orderNumber}/ship`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tracking_number: trackingNumber,
                shipping_company: shippingCompany,
                notes: notes
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Commande marquée comme expédiée');
            bootstrap.Modal.getInstance(document.getElementById('shipModal')).hide();
            loadOrderDetails(); // Recharger les détails
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors du marquage de la commande');
    }
}

// Annuler la commande
async function cancelOrder() {
    const reason = document.getElementById('cancelReason').value;
    
    if (!reason.trim()) {
        showNotification('warning', 'Veuillez indiquer la raison de l\'annulation');
        return;
    }
    
    try {
        const response = await fetch(`/api/store/orders/${orderNumber}/cancel`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                reason: reason
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Commande annulée avec succès');
            bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
            loadOrderDetails(); // Recharger les détails
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'annulation de la commande');
    }
}

// Fonction de notification
function showNotification(type, message) {
    // Utiliser la fonction de notification du dashboard si disponible
    if (typeof window.showNotification === 'function') {
        window.showNotification(type, message);
    } else {
        // Fallback avec alert
        alert(message);
    }
}
</script>
@endsection
