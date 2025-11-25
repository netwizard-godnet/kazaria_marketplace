<?php $__env->startSection('title', 'Gestion des Commandes'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Commandes</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Commandes</span>
            </li>
        </ul>
    </div>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-warning">
                                <i class="flaticon-shopping-cart text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Total</p>
                                <h4 class="card-title"><?php echo e($stats['total']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-warning">
                                <i class="flaticon-time text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">En attente</p>
                                <h4 class="card-title"><?php echo e($stats['pending']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-info">
                                <i class="flaticon-settings text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">En cours</p>
                                <h4 class="card-title"><?php echo e($stats['processing']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-success">
                                <i class="flaticon-check text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Livrées</p>
                                <h4 class="card-title"><?php echo e($stats['delivered']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-danger">
                                <i class="flaticon-close text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Annulées</p>
                                <h4 class="card-title"><?php echo e($stats['cancelled']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-success">
                                <i class="flaticon-credit-card text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Payées</p>
                                <h4 class="card-title"><?php echo e($stats['paid']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestion des Commandes</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-sm <?php echo e(empty($currentStatus) ? 'btn-primary' : 'btn-outline-primary'); ?>">Toutes</a>
                            <a href="<?php echo e(route('admin.orders.index', ['status' => 'pending'])); ?>" class="btn btn-sm <?php echo e(($currentStatus === 'pending') ? 'btn-warning' : 'btn-outline-warning'); ?>">En attente</a>
                            <a href="<?php echo e(route('admin.orders.index', ['status' => 'processing'])); ?>" class="btn btn-sm <?php echo e(($currentStatus === 'processing') ? 'btn-info' : 'btn-outline-info'); ?>">En cours</a>
                            <a href="<?php echo e(route('admin.orders.index', ['status' => 'delivered'])); ?>" class="btn btn-sm <?php echo e(($currentStatus === 'delivered') ? 'btn-success' : 'btn-outline-success'); ?>">Livrées</a>
                            <a href="<?php echo e(route('admin.orders.index', ['status' => 'cancelled'])); ?>" class="btn btn-sm <?php echo e(($currentStatus === 'cancelled') ? 'btn-danger' : 'btn-outline-danger'); ?>">Annulées</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form method="GET" action="<?php echo e(route('admin.orders.index')); ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Statut de commande</label>
                                <select name="status" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" <?php echo e($currentStatus === 'pending' ? 'selected' : ''); ?>>En cours de validation</option>
                                    <option value="processing" <?php echo e($currentStatus === 'processing' ? 'selected' : ''); ?>>En cours de livraison</option>
                                    <option value="delivered" <?php echo e($currentStatus === 'delivered' ? 'selected' : ''); ?>>Livrée</option>
                                    <option value="cancelled" <?php echo e($currentStatus === 'cancelled' ? 'selected' : ''); ?>>Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Statut de paiement</label>
                                <select name="payment_status" class="form-control">
                                    <option value="">Tous les paiements</option>
                                    <option value="pending" <?php echo e($currentPaymentStatus === 'pending' ? 'selected' : ''); ?>>En attente</option>
                                    <option value="paid" <?php echo e($currentPaymentStatus === 'paid' ? 'selected' : ''); ?>>Payé</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                                    <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-secondary btn-sm">Effacer</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Client</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Paiement</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>#<?php echo e($order->order_number); ?></td>
                                    <td><?php echo e($order->user->prenoms ?? 'N/A'); ?> <?php echo e($order->user->nom ?? ''); ?></td>
                                    <td><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</td>
                                    <td>
                                        <span class="badge bg-<?php echo e($order->status_class); ?>">
                                            <?php echo e($order->status_label); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($order->payment_status_class); ?>">
                                            <?php echo e($order->payment_status_label); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                                    <td>
                                        <?php if($order->status === 'pending'): ?>
                                            <button class="btn btn-warning btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'processing')" title="Marquer en cours">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'cancelled')" title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif($order->status === 'processing'): ?>
                                            <button class="btn btn-success btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'delivered')" title="Marquer livrée">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'cancelled')" title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif($order->status === 'delivered'): ?>
                                            <button class="btn btn-info btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'processing')" title="Retour en cours">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'cancelled')" title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif($order->status === 'cancelled'): ?>
                                            <button class="btn btn-primary btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'pending')" title="Réactiver">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="quickUpdateStatus(<?php echo e($order->id); ?>, 'processing')" title="Marquer en cours">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-primary btn-sm" onclick="showOrderModal(<?php echo e($order->id); ?>)" title="Actions avancées">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <?php if($order->status === 'cancelled'): ?>
                                            <button class="btn btn-danger btn-sm" onclick="deleteOrder(<?php echo e($order->id); ?>)" title="Supprimer la commande">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucune commande trouvée</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($orders->hasPages()): ?>
                    <div class="d-flex justify-content-center mt-3">
                        <?php echo e($orders->links()); ?>

                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les actions avancées -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">Actions sur la commande #<span id="modalOrderNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-shipping-fast me-2"></i>Statut de la commande
                        </h6>
                        <div class="d-grid gap-2" id="orderStatusButtons">
                            <!-- Les boutons seront générés dynamiquement -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-credit-card me-2"></i>Statut de paiement
                        </h6>
                        <div class="d-grid gap-2" id="paymentStatusButtons">
                            <!-- Les boutons seront générés dynamiquement -->
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-tools me-2"></i>Actions spéciales
                        </h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-info" onclick="viewOrderDetails()">
                                <i class="fas fa-eye me-2"></i>Voir les détails complets
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteOrder()" id="deleteOrderBtn" style="display: none;">
                                <i class="fas fa-trash me-2"></i>Supprimer la commande
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let currentOrderId = null;
let currentOrderData = null;
const ordersData = <?php echo json_encode($orders->items(), 15, 512) ?>;

function showOrderModal(orderId) {
    currentOrderId = orderId;
    currentOrderData = ordersData.find(order => order.id === orderId);
    
    if (currentOrderData) {
        document.getElementById('modalOrderNumber').textContent = currentOrderData.order_number;
        generateOrderStatusButtons();
        generatePaymentStatusButtons();
        
        const deleteBtn = document.getElementById('deleteOrderBtn');
        if (currentOrderData.status === 'cancelled') {
            deleteBtn.style.display = 'block';
        } else {
            deleteBtn.style.display = 'none';
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('orderModal'));
    modal.show();
}

function quickUpdateStatus(orderId, status) {
    currentOrderId = orderId;
    updateOrderStatusRequest(orderId, status);
}

function generateOrderStatusButtons() {
    const container = document.getElementById('orderStatusButtons');
    const currentStatus = currentOrderData.status;
    const availableStatuses = getAvailableOrderStatuses(currentStatus);
    
    container.innerHTML = '';
    
    availableStatuses.forEach(status => {
        const button = document.createElement('button');
        button.className = `btn btn-${status.class} btn-sm`;
        button.innerHTML = `<i class="${status.icon} me-2"></i>${status.label}`;
        button.onclick = () => updateOrderStatus(status.value);
        container.appendChild(button);
    });
}

function generatePaymentStatusButtons() {
    const container = document.getElementById('paymentStatusButtons');
    const currentPaymentStatus = currentOrderData.payment_status;
    const availableStatuses = getAvailablePaymentStatuses(currentPaymentStatus);
    
    container.innerHTML = '';
    
    availableStatuses.forEach(status => {
        const button = document.createElement('button');
        button.className = `btn btn-${status.class} btn-sm`;
        button.innerHTML = `<i class="${status.icon} me-2"></i>${status.label}`;
        button.onclick = () => updatePaymentStatus(status.value);
        container.appendChild(button);
    });
}

function getAvailableOrderStatuses(currentStatus) {
    const statuses = {
        'pending': [
            { value: 'processing', label: 'Marquer en cours', class: 'warning', icon: 'fas fa-play' },
            { value: 'cancelled', label: 'Annuler', class: 'danger', icon: 'fas fa-times' }
        ],
        'processing': [
            { value: 'delivered', label: 'Marquer livrée', class: 'success', icon: 'fas fa-check' },
            { value: 'cancelled', label: 'Annuler', class: 'danger', icon: 'fas fa-times' }
        ],
        'delivered': [
            { value: 'processing', label: 'Retour en cours', class: 'info', icon: 'fas fa-undo' },
            { value: 'cancelled', label: 'Annuler', class: 'danger', icon: 'fas fa-times' }
        ],
        'cancelled': [
            { value: 'pending', label: 'Réactiver', class: 'primary', icon: 'fas fa-redo' },
            { value: 'processing', label: 'Marquer en cours', class: 'warning', icon: 'fas fa-play' }
        ]
    };
    return statuses[currentStatus] || [];
}

function getAvailablePaymentStatuses(currentPaymentStatus) {
    const statuses = {
        'pending': [
            { value: 'paid', label: 'Marquer comme payé', class: 'success', icon: 'fas fa-check-circle' }
        ],
        'paid': [
            { value: 'pending', label: 'Paiement en attente', class: 'warning', icon: 'fas fa-clock' }
        ]
    };
    return statuses[currentPaymentStatus] || [];
}

function updateOrderStatus(status) {
    updateOrderStatusRequest(currentOrderId, status);
    const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    modal.hide();
}

function updatePaymentStatus(paymentStatus) {
    updatePaymentStatusRequest(currentOrderId, paymentStatus);
    const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    modal.hide();
}

function updateOrderStatusRequest(orderId, status) {
    fetch(`/admin/orders/${orderId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status, reason: 'Changement de statut par l\'admin' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    });
}

function updatePaymentStatusRequest(orderId, paymentStatus) {
    fetch(`/admin/orders/${orderId}/payment-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ payment_status: paymentStatus, reason: 'Changement de statut de paiement par l\'admin' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    });
}

function viewOrderDetails() {
    window.open(`/admin/orders/${currentOrderId}`, '_blank');
}

function deleteOrder(orderId = null) {
    const orderToDelete = orderId || currentOrderId;
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette commande ? Cette action est irréversible.')) {
        return;
    }
    
    fetch(`/admin/orders/${orderToDelete}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la suppression');
    });
}

function showNotification(type, message) {
    // Créer une notification toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Supprimer l'élément après 5 secondes
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>