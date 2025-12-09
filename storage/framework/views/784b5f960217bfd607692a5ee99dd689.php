

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo e(number_format($stats['total'])); ?></h4>
                            <p class="mb-0">Total Paiements</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-credit-card fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo e(number_format($stats['completed'])); ?></h4>
                            <p class="mb-0">Terminés</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo e(number_format($stats['pending'])); ?></h4>
                            <p class="mb-0">En attente</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo e(number_format($stats['failed'])); ?></h4>
                            <p class="mb-0">Échoués</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo e(number_format($stats['total_amount'], 0)); ?> FCFA</h4>
                            <p class="mb-0">Montant Total</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo e(number_format($stats['total_commission'], 0)); ?> FCFA</h4>
                            <p class="mb-0">Commissions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtres et Recherche</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('admin.payments.index')); ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Recherche</label>
                                    <input type="text" name="search" class="form-control" value="<?php echo e(request('search')); ?>" placeholder="Référence, transaction, utilisateur...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="status" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>En attente</option>
                                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Terminé</option>
                                        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Échoué</option>
                                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Annulé</option>
                                        <option value="refunded" <?php echo e(request('status') == 'refunded' ? 'selected' : ''); ?>>Remboursé</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Méthode</label>
                                    <select name="payment_method" class="form-control">
                                        <option value="">Toutes</option>
                                        <option value="cinetpay" <?php echo e(request('payment_method') == 'cinetpay' ? 'selected' : ''); ?>>CinetPay</option>
                                        <option value="stripe" <?php echo e(request('payment_method') == 'stripe' ? 'selected' : ''); ?>>Stripe</option>
                                        <option value="paypal" <?php echo e(request('payment_method') == 'paypal' ? 'selected' : ''); ?>>PayPal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date début</label>
                                    <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date fin</label>
                                    <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des paiements -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des Paiements</h3>
                    <div class="card-tools">
                        <a href="<?php echo e(route('admin.payments.export', request()->query())); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Exporter CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Référence</th>
                                    <th>Utilisateur</th>
                                    <th>Boutique</th>
                                    <th>Montant</th>
                                    <th>Commission</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($payment->id); ?></td>
                                    <td>
                                        <code><?php echo e($payment->payment_reference); ?></code>
                                        <?php if($payment->transaction_id): ?>
                                            <br><small class="text-muted"><?php echo e($payment->transaction_id); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo e($payment->user->nom); ?> <?php echo e($payment->user->prenoms); ?></strong>
                                            <br><small class="text-muted"><?php echo e($payment->user->email); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo e($payment->store->name ?? 'N/A'); ?></td>
                                    <td>
                                        <strong><?php echo e(number_format($payment->amount, 0)); ?> FCFA</strong>
                                    </td>
                                    <td>
                                        <?php echo e(number_format($payment->commission_amount, 0)); ?> FCFA
                                        <br><small class="text-muted">(<?php echo e($payment->commission_rate); ?>%)</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo e(ucfirst($payment->payment_method)); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($payment->status_badge_class); ?>"><?php echo e($payment->status_label); ?></span>
                                    </td>
                                    <td><?php echo e($payment->created_at->format('d/m/Y H:i')); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo e(route('admin.payments.show', $payment)); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($payment->status === 'pending'): ?>
                                                <button type="button" class="btn btn-success btn-sm" onclick="markAsCompleted(<?php echo e($payment->id); ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm" onclick="cancelPayment(<?php echo e($payment->id); ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if($payment->status === 'completed'): ?>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="refundPayment(<?php echo e($payment->id); ?>)">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="text-center">Aucun paiement trouvé</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Affichage de <?php echo e($payments->firstItem()); ?> à <?php echo e($payments->lastItem()); ?> sur <?php echo e($payments->total()); ?> résultats
                        </div>
                        <div>
                            <?php echo e($payments->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de remboursement -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rembourser un paiement</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="refundForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Montant du remboursement *</label>
                        <input type="number" name="refund_amount" class="form-control" step="0.01" required>
                        <small class="form-text text-muted">Montant maximum: <span id="maxRefundAmount"></span> FCFA</small>
                    </div>
                    <div class="form-group">
                        <label>Raison du remboursement *</label>
                        <textarea name="refund_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Rembourser</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function markAsCompleted(paymentId) {
    if (confirm('Êtes-vous sûr de vouloir marquer ce paiement comme terminé ?')) {
        fetch(`/admin/payments/${paymentId}/mark-completed`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function cancelPayment(paymentId) {
    if (confirm('Êtes-vous sûr de vouloir annuler ce paiement ?')) {
        fetch(`/admin/payments/${paymentId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function refundPayment(paymentId) {
    // Récupérer les données du paiement et afficher le modal
    fetch(`/admin/payments/${paymentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('maxRefundAmount').textContent = data.payment.amount;
            document.querySelector('input[name="refund_amount"]').max = data.payment.amount;
            document.querySelector('input[name="refund_amount"]').value = data.payment.amount;
            document.getElementById('refundForm').action = `/admin/payments/${paymentId}/refund`;
            $('#refundModal').modal('show');
        });
}

// Gestion du formulaire de remboursement
document.getElementById('refundForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const paymentId = this.action.split('/').pop();
    
    fetch(`/admin/payments/${paymentId}/refund`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            refund_amount: formData.get('refund_amount'),
            refund_reason: formData.get('refund_reason')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#refundModal').modal('hide');
            location.reload();
        } else {
            alert(data.message);
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\payments\index.blade.php ENDPATH**/ ?>