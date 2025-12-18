

<?php $__env->startSection('title', 'Détails de la Facture'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Facture: <?php echo e($invoice->invoice_number); ?></h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.invoices.index')); ?>">Factures</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span><?php echo e($invoice->invoice_number); ?></span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Détails de la facture -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Détails de la Facture</h4>
                    <div class="card-tools">
                        <span class="badge bg-<?php echo e($invoice->status_class); ?>"><?php echo e($invoice->status_label); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informations Client</h6>
                            <p><strong>Nom:</strong> <?php echo e($invoice->client_name); ?></p>
                            <p><strong>Email:</strong> <?php echo e($invoice->client_email); ?></p>
                            <?php if($invoice->client_phone): ?>
                                <p><strong>Téléphone:</strong> <?php echo e($invoice->client_phone); ?></p>
                            <?php endif; ?>
                            <?php if($invoice->client_address): ?>
                                <p><strong>Adresse:</strong> <?php echo e($invoice->client_address); ?></p>
                            <?php endif; ?>
                            <?php if($invoice->client_city): ?>
                                <p><strong>Ville:</strong> <?php echo e($invoice->client_city); ?></p>
                            <?php endif; ?>
                            <?php if($invoice->client_postal_code): ?>
                                <p><strong>Code postal:</strong> <?php echo e($invoice->client_postal_code); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6>Informations Facture</h6>
                            <p><strong>N° Facture:</strong> <?php echo e($invoice->invoice_number); ?></p>
                            <p><strong>Date d'émission:</strong> <?php echo e($invoice->invoice_date->format('d/m/Y')); ?></p>
                            <?php if($invoice->due_date): ?>
                                <p><strong>Date d'échéance:</strong> <?php echo e($invoice->due_date->format('d/m/Y')); ?></p>
                            <?php endif; ?>
                            <?php if($invoice->paid_date): ?>
                                <p><strong>Date de paiement:</strong> <?php echo e($invoice->paid_date->format('d/m/Y')); ?></p>
                            <?php endif; ?>
                            <?php if($invoice->order): ?>
                                <p><strong>Commande associée:</strong> 
                                    <a href="<?php echo e(route('admin.orders.show', $invoice->order)); ?>"><?php echo e($invoice->order->order_number); ?></a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($invoice->description): ?>
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p><?php echo e($invoice->description); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Tableau des montants -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Sous-total</strong></td>
                                    <td class="text-end"><?php echo e(number_format($invoice->subtotal, 0, ',', ' ')); ?> FCFA</td>
                                </tr>
                                <?php if($invoice->tax_rate > 0): ?>
                                <tr>
                                    <td>TVA (<?php echo e($invoice->tax_rate); ?>%)</td>
                                    <td class="text-end"><?php echo e(number_format($invoice->tax_amount, 0, ',', ' ')); ?> FCFA</td>
                                </tr>
                                <?php endif; ?>
                                <?php if($invoice->shipping_cost > 0): ?>
                                <tr>
                                    <td>Frais de livraison</td>
                                    <td class="text-end"><?php echo e(number_format($invoice->shipping_cost, 0, ',', ' ')); ?> FCFA</td>
                                </tr>
                                <?php endif; ?>
                                <?php if($invoice->discount > 0): ?>
                                <tr>
                                    <td>Remise</td>
                                    <td class="text-end text-danger">- <?php echo e(number_format($invoice->discount, 0, ',', ' ')); ?> FCFA</td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-primary">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-end"><strong><?php echo e(number_format($invoice->total, 0, ',', ' ')); ?> FCFA</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if($invoice->payment_method): ?>
                    <div class="mt-4">
                        <h6>Informations de Paiement</h6>
                        <p><strong>Méthode:</strong> 
                            <?php if($invoice->payment_method == 'card'): ?>
                                Carte bancaire
                            <?php elseif($invoice->payment_method == 'mobile_money'): ?>
                                Mobile Money
                            <?php elseif($invoice->payment_method == 'cash'): ?>
                                Espèces
                            <?php elseif($invoice->payment_method == 'bank_transfer'): ?>
                                Virement bancaire
                            <?php else: ?>
                                <?php echo e(ucfirst(str_replace('_', ' ', $invoice->payment_method))); ?>

                            <?php endif; ?>
                        </p>
                        <?php if($invoice->payment_reference): ?>
                            <p><strong>Référence:</strong> <?php echo e($invoice->payment_reference); ?></p>
                        <?php endif; ?>
                        <?php if($invoice->payment_notes): ?>
                            <p><strong>Notes:</strong> <?php echo e($invoice->payment_notes); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if($invoice->terms): ?>
                    <div class="mt-4">
                        <h6>Conditions Générales</h6>
                        <p><?php echo e($invoice->terms); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.invoices.download', $invoice)); ?>" class="btn btn-success">
                            <i class="fas fa-download"></i> Télécharger PDF
                        </a>
                        <?php if(canAccess('edit_invoices')): ?>
                        <a href="<?php echo e(route('admin.invoices.edit', $invoice)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Informations</h4>
                </div>
                <div class="card-body">
                    <p><strong>Créée par:</strong> <?php echo e($invoice->creator->prenoms ?? 'N/A'); ?> <?php echo e($invoice->creator->nom ?? ''); ?></p>
                    <p><strong>Créée le:</strong> <?php echo e($invoice->created_at->format('d/m/Y H:i')); ?></p>
                    <?php if($invoice->updated_at != $invoice->created_at): ?>
                        <p><strong>Modifiée le:</strong> <?php echo e($invoice->updated_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                    <?php if($invoice->isOverdue()): ?>
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-triangle"></i> Cette facture est en retard !
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($invoice->notes): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Notes Internes</h4>
                </div>
                <div class="card-body">
                    <p><?php echo e($invoice->notes); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\invoices\show.blade.php ENDPATH**/ ?>