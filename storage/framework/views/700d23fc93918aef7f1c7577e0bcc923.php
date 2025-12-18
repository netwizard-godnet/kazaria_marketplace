

<?php $__env->startSection('title', 'Modifier la Facture'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Modifier la Facture: <?php echo e($invoice->invoice_number); ?></h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.invoices.index')); ?>">Factures</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Modifier</span></li>
        </ul>
    </div>

    <form action="<?php echo e(route('admin.invoices.update', $invoice)); ?>" method="POST" id="invoiceForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="row">
            <!-- Informations client -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informations Client</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="user_id">Client *</label>
                            <select class="form-control <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="user_id" name="user_id" required>
                                <option value="">Sélectionner un client</option>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>" <?php echo e(old('user_id', $invoice->user_id) == $user->id ? 'selected' : ''); ?>>
                                        <?php echo e($user->prenoms); ?> <?php echo e($user->nom); ?> (<?php echo e($user->email); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="order_id">Commande associée (optionnel)</label>
                            <select class="form-control" id="order_id" name="order_id">
                                <option value="">Aucune</option>
                                <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ord->id); ?>" <?php echo e(old('order_id', $invoice->order_id) == $ord->id ? 'selected' : ''); ?>>
                                        <?php echo e($ord->order_number); ?> - <?php echo e($ord->user->prenoms ?? ''); ?> <?php echo e($ord->user->nom ?? ''); ?> (<?php echo e(number_format($ord->total, 0, ',', ' ')); ?> FCFA)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="client_name">Nom du client *</label>
                            <input type="text" class="form-control <?php $__errorArgs = ['client_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="client_name" name="client_name" value="<?php echo e(old('client_name', $invoice->client_name)); ?>" required>
                            <?php $__errorArgs = ['client_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="client_email">Email *</label>
                            <input type="email" class="form-control <?php $__errorArgs = ['client_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="client_email" name="client_email" value="<?php echo e(old('client_email', $invoice->client_email)); ?>" required>
                            <?php $__errorArgs = ['client_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="client_phone">Téléphone</label>
                            <input type="text" class="form-control" id="client_phone" name="client_phone" 
                                   value="<?php echo e(old('client_phone', $invoice->client_phone)); ?>">
                        </div>

                        <div class="form-group">
                            <label for="client_address">Adresse</label>
                            <textarea class="form-control" id="client_address" name="client_address" rows="2"><?php echo e(old('client_address', $invoice->client_address)); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_city">Ville</label>
                                    <input type="text" class="form-control" id="client_city" name="client_city" 
                                           value="<?php echo e(old('client_city', $invoice->client_city)); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_postal_code">Code postal</label>
                                    <input type="text" class="form-control" id="client_postal_code" name="client_postal_code" 
                                           value="<?php echo e(old('client_postal_code', $invoice->client_postal_code)); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations facture -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informations Facture</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="invoice_date">Date d'émission *</label>
                            <input type="date" class="form-control <?php $__errorArgs = ['invoice_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="invoice_date" name="invoice_date" value="<?php echo e(old('invoice_date', $invoice->invoice_date->format('Y-m-d'))); ?>" required>
                            <?php $__errorArgs = ['invoice_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="due_date">Date d'échéance</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" 
                                   value="<?php echo e(old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '')); ?>">
                        </div>

                        <div class="form-group">
                            <label for="status">Statut *</label>
                            <select class="form-control <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="status" name="status" required>
                                <option value="draft" <?php echo e(old('status', $invoice->status) == 'draft' ? 'selected' : ''); ?>>Brouillon</option>
                                <option value="sent" <?php echo e(old('status', $invoice->status) == 'sent' ? 'selected' : ''); ?>>Envoyée</option>
                                <option value="paid" <?php echo e(old('status', $invoice->status) == 'paid' ? 'selected' : ''); ?>>Payée</option>
                                <option value="overdue" <?php echo e(old('status', $invoice->status) == 'overdue' ? 'selected' : ''); ?>>En retard</option>
                                <option value="cancelled" <?php echo e(old('status', $invoice->status) == 'cancelled' ? 'selected' : ''); ?>>Annulée</option>
                                <option value="refunded" <?php echo e(old('status', $invoice->status) == 'refunded' ? 'selected' : ''); ?>>Remboursée</option>
                            </select>
                            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="subtotal">Sous-total (FCFA) *</label>
                            <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['subtotal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="subtotal" name="subtotal" value="<?php echo e(old('subtotal', $invoice->subtotal)); ?>" required>
                            <?php $__errorArgs = ['subtotal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="tax_rate">Taux de TVA (%)</label>
                            <input type="number" step="0.01" class="form-control" id="tax_rate" name="tax_rate" 
                                   value="<?php echo e(old('tax_rate', $invoice->tax_rate)); ?>" min="0" max="100">
                        </div>

                        <div class="form-group">
                            <label for="discount">Remise (FCFA)</label>
                            <input type="number" step="0.01" class="form-control" id="discount" name="discount" 
                                   value="<?php echo e(old('discount', $invoice->discount)); ?>" min="0">
                        </div>

                        <div class="form-group">
                            <label for="shipping_cost">Frais de livraison (FCFA)</label>
                            <input type="number" step="0.01" class="form-control" id="shipping_cost" name="shipping_cost" 
                                   value="<?php echo e(old('shipping_cost', $invoice->shipping_cost)); ?>" min="0">
                        </div>

                        <div class="form-group">
                            <label for="total">Total (FCFA) *</label>
                            <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['total'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="total" name="total" value="<?php echo e(old('total', $invoice->total)); ?>" required readonly>
                            <?php $__errorArgs = ['total'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description et notes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Description et Notes</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo e(old('description', $invoice->description)); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes internes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo e(old('notes', $invoice->notes)); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="terms">Conditions générales</label>
                            <textarea class="form-control" id="terms" name="terms" rows="2"><?php echo e(old('terms', $invoice->terms)); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="<?php echo e(route('admin.invoices.show', $invoice)); ?>" class="btn btn-info">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                        <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const subtotalInput = document.getElementById('subtotal');
    const taxRateInput = document.getElementById('tax_rate');
    const discountInput = document.getElementById('discount');
    const shippingInput = document.getElementById('shipping_cost');
    const totalInput = document.getElementById('total');

    function calculateTotal() {
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const taxRate = parseFloat(taxRateInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const shipping = parseFloat(shippingInput.value) || 0;

        const taxAmount = (subtotal * taxRate) / 100;
        const total = subtotal + taxAmount + shipping - discount;

        totalInput.value = total.toFixed(2);
    }

    subtotalInput.addEventListener('input', calculateTotal);
    taxRateInput.addEventListener('input', calculateTotal);
    discountInput.addEventListener('input', calculateTotal);
    shippingInput.addEventListener('input', calculateTotal);
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\invoices\edit.blade.php ENDPATH**/ ?>