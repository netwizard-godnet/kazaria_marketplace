

<?php $__env->startSection('title', 'Créer une Facture'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Créer une Facture</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.invoices.index')); ?>">Factures</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Créer</span></li>
        </ul>
    </div>

    <form action="<?php echo e(route('admin.invoices.store')); ?>" method="POST" id="invoiceForm">
        <?php echo csrf_field(); ?>
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
                                    <option value="<?php echo e($user->id); ?>" <?php echo e(old('user_id', $order->user_id ?? null) == $user->id ? 'selected' : ''); ?>>
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
                                    <option value="<?php echo e($ord->id); ?>" <?php echo e(old('order_id', $order ? $order->id : null) == $ord->id ? 'selected' : ''); ?>>
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
                                   id="client_name" name="client_name" value="<?php echo e(old('client_name', $order && $order->user ? ($order->user->prenoms . ' ' . $order->user->nom) : '')); ?>" required>
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
                                   id="client_email" name="client_email" value="<?php echo e(old('client_email', $order && $order->user ? $order->user->email : '')); ?>" required>
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
                                   value="<?php echo e(old('client_phone', $order ? ($order->shipping_phone ?? ($order->user ? $order->user->telephone : '')) : '')); ?>">
                        </div>

                        <div class="form-group">
                            <label for="client_address">Adresse</label>
                            <textarea class="form-control" id="client_address" name="client_address" rows="2"><?php echo e(old('client_address', $order ? ($order->shipping_address ?? '') : '')); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_city">Ville</label>
                                    <input type="text" class="form-control" id="client_city" name="client_city" 
                                           value="<?php echo e(old('client_city', $order ? ($order->shipping_city ?? '') : '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_postal_code">Code postal</label>
                                    <input type="text" class="form-control" id="client_postal_code" name="client_postal_code" 
                                           value="<?php echo e(old('client_postal_code', $order ? ($order->shipping_postal_code ?? '') : '')); ?>">
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
                                   id="invoice_date" name="invoice_date" value="<?php echo e(old('invoice_date', date('Y-m-d'))); ?>" required>
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
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo e(old('due_date')); ?>">
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
                                <option value="draft" <?php echo e(old('status', 'draft') == 'draft' ? 'selected' : ''); ?>>Brouillon</option>
                                <option value="sent" <?php echo e(old('status') == 'sent' ? 'selected' : ''); ?>>Envoyée</option>
                                <option value="paid" <?php echo e(old('status') == 'paid' ? 'selected' : ''); ?>>Payée</option>
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
                                   id="subtotal" name="subtotal" value="<?php echo e(old('subtotal', $order ? ($order->subtotal ?? 0) : 0)); ?>" required>
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
                                   value="<?php echo e(old('tax_rate', 18)); ?>" min="0" max="100">
                        </div>

                        <div class="form-group">
                            <label for="discount">Remise (FCFA)</label>
                            <input type="number" step="0.01" class="form-control" id="discount" name="discount" 
                                   value="<?php echo e(old('discount', $order ? ($order->discount ?? 0) : 0)); ?>" min="0">
                        </div>

                        <div class="form-group">
                            <label for="shipping_cost">Frais de livraison (FCFA)</label>
                            <input type="number" step="0.01" class="form-control" id="shipping_cost" name="shipping_cost" 
                                   value="<?php echo e(old('shipping_cost', $order ? ($order->shipping_cost ?? 0) : 0)); ?>" min="0">
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
                                   id="total" name="total" value="<?php echo e(old('total', $order ? ($order->total ?? 0) : 0)); ?>" required readonly>
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

        <!-- Produits/Services -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Produits / Services</h3>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary" id="loadOrderItemsBtn" title="Charger les produits de la commande sélectionnée">
                                <i class="fas fa-shopping-cart"></i> Charger depuis la commande
                            </button>
                            <button type="button" class="btn btn-sm btn-info" id="searchProductBtn" title="Rechercher et ajouter un produit du catalogue">
                                <i class="fas fa-search"></i> Rechercher un produit
                            </button>
                            <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                                <i class="fas fa-plus"></i> Ajouter manuellement
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Champ de recherche de produit -->
                        <div class="mb-3" id="productSearchContainer" style="display: none;">
                            <label>Rechercher un produit</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="productSearch" placeholder="Tapez le nom d'un produit...">
                                <button type="button" class="btn btn-secondary" id="cancelSearchBtn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div id="productSearchResults" class="mt-2" style="max-height: 300px; overflow-y: auto; display: none;">
                                <!-- Les résultats de recherche apparaîtront ici -->
                            </div>
                        </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Description</th>
                                        <th style="width: 15%;">Quantité</th>
                                        <th style="width: 20%;">Prix unitaire (FCFA)</th>
                                        <th style="width: 20%;">Total (FCFA)</th>
                                        <th style="width: 5%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <?php if($order && $order->orderItems): ?>
                                        <?php $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="item-row">
                                            <td>
                                                <input type="text" class="form-control item-description" name="items[<?php echo e($index); ?>][description]" 
                                                       value="<?php echo e($item->product_name); ?>" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-quantity" name="items[<?php echo e($index); ?>][quantity]" 
                                                       value="<?php echo e($item->quantity); ?>" min="1" step="1" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-price" name="items[<?php echo e($index); ?>][price]" 
                                                       value="<?php echo e($item->price); ?>" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-total" name="items[<?php echo e($index); ?>][total]" 
                                                       value="<?php echo e($item->total); ?>" step="0.01" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Sous-total:</strong></td>
                                        <td><strong id="itemsSubtotal">0</strong> FCFA</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
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
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Description des produits ou services facturés..."><?php echo e(old('description')); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes internes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Notes internes (non visibles par le client)..."><?php echo e(old('notes')); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="terms">Conditions générales</label>
                            <textarea class="form-control" id="terms" name="terms" rows="2" 
                                      placeholder="Conditions générales de vente..."><?php echo e(old('terms')); ?></textarea>
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
                            <i class="fas fa-save"></i> Créer la facture
                        </button>
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
    const itemsTableBody = document.getElementById('itemsTableBody');
    const itemsSubtotal = document.getElementById('itemsSubtotal');
    const addItemBtn = document.getElementById('addItemBtn');

    // Fonction pour obtenir le prochain index disponible
    function getNextItemIndex() {
        const rows = itemsTableBody.querySelectorAll('.item-row');
        return rows.length;
    }

    // Fonction pour réindexer tous les champs après suppression
    function reindexItems() {
        const rows = itemsTableBody.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            row.querySelector('.item-description').name = `items[${index}][description]`;
            row.querySelector('.item-quantity').name = `items[${index}][quantity]`;
            row.querySelector('.item-price').name = `items[${index}][price]`;
            row.querySelector('.item-total').name = `items[${index}][total]`;
        });
    }

    // Ajouter une nouvelle ligne de produit
    addItemBtn.addEventListener('click', function() {
        const index = getNextItemIndex();
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>
                <input type="text" class="form-control item-description" name="items[${index}][description]" required>
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="items[${index}][quantity]" value="1" min="1" step="1" required>
            </td>
            <td>
                <input type="number" class="form-control item-price" name="items[${index}][price]" value="0" step="0.01" min="0" required>
            </td>
            <td>
                <input type="number" class="form-control item-total" name="items[${index}][total]" value="0" step="0.01" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        itemsTableBody.appendChild(row);
        attachItemEvents(row);
    });

    // Attacher les événements à une ligne
    function attachItemEvents(row) {
        const quantityInput = row.querySelector('.item-quantity');
        const priceInput = row.querySelector('.item-price');
        const totalInput = row.querySelector('.item-total');
        const removeBtn = row.querySelector('.remove-item');

        // Calculer le total de la ligne
        function calculateRowTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            totalInput.value = total.toFixed(2);
            calculateItemsSubtotal();
            calculateInvoiceTotal();
        }

        quantityInput.addEventListener('input', calculateRowTotal);
        priceInput.addEventListener('input', calculateRowTotal);
        removeBtn.addEventListener('click', function() {
            row.remove();
            reindexItems(); // Réindexer après suppression
            calculateItemsSubtotal();
            calculateInvoiceTotal();
        });
    }

    // Calculer le sous-total des items
    function calculateItemsSubtotal() {
        let subtotal = 0;
        document.querySelectorAll('.item-total').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        itemsSubtotal.textContent = subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        subtotalInput.value = subtotal.toFixed(2);
    }

    // Calculer le total de la facture
    function calculateInvoiceTotal() {
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const taxRate = parseFloat(taxRateInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const shipping = parseFloat(shippingInput.value) || 0;

        const taxAmount = (subtotal * taxRate) / 100;
        const total = subtotal + taxAmount + shipping - discount;

        totalInput.value = total.toFixed(2);
    }

    // Attacher les événements aux lignes existantes
    document.querySelectorAll('.item-row').forEach(row => {
        attachItemEvents(row);
    });

    // Calculer le sous-total initial
    calculateItemsSubtotal();
    calculateInvoiceTotal();

    // Écouter les changements sur les champs de calcul
    taxRateInput.addEventListener('input', calculateInvoiceTotal);
    discountInput.addEventListener('input', calculateInvoiceTotal);
    shippingInput.addEventListener('input', calculateInvoiceTotal);

    // ========== CHARGEMENT AUTOMATIQUE DEPUIS UNE COMMANDE ==========
    const loadOrderItemsBtn = document.getElementById('loadOrderItemsBtn');
    const orderIdSelect = document.getElementById('order_id');
    
    loadOrderItemsBtn.addEventListener('click', function() {
        const orderId = orderIdSelect.value;
        if (!orderId) {
            alert('Veuillez d\'abord sélectionner une commande.');
            return;
        }
        
        if (confirm('Charger les produits de cette commande ? Les produits existants seront remplacés.')) {
            loadOrderItemsBtn.disabled = true;
            loadOrderItemsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
            
            fetch(`<?php echo e(url('/admin/invoices/api/order-items')); ?>/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.items.length > 0) {
                        // Vider le tableau
                        itemsTableBody.innerHTML = '';
                        
                        // Ajouter les items de la commande
                        data.items.forEach((item, index) => {
                            const row = document.createElement('tr');
                            row.className = 'item-row';
                            row.innerHTML = `
                                <td>
                                    <input type="text" class="form-control item-description" name="items[${index}][description]" 
                                           value="${item.description}" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control item-quantity" name="items[${index}][quantity]" 
                                           value="${item.quantity}" min="1" step="1" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control item-price" name="items[${index}][price]" 
                                           value="${item.price}" step="0.01" min="0" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control item-total" name="items[${index}][total]" 
                                           value="${item.total}" step="0.01" readonly>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            `;
                            itemsTableBody.appendChild(row);
                            attachItemEvents(row);
                        });
                        
                        // Mettre à jour le sous-total
                        subtotalInput.value = data.subtotal.toFixed(2);
                        calculateInvoiceTotal();
                        
                        alert('Produits chargés avec succès !');
                    } else {
                        alert('Aucun produit trouvé dans cette commande.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des produits.');
                })
                .finally(() => {
                    loadOrderItemsBtn.disabled = false;
                    loadOrderItemsBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Charger depuis la commande';
                });
        }
    });

    // ========== RECHERCHE DE PRODUITS ==========
    const searchProductBtn = document.getElementById('searchProductBtn');
    const productSearchContainer = document.getElementById('productSearchContainer');
    const productSearch = document.getElementById('productSearch');
    const productSearchResults = document.getElementById('productSearchResults');
    const cancelSearchBtn = document.getElementById('cancelSearchBtn');
    let searchTimeout;

    searchProductBtn.addEventListener('click', function() {
        productSearchContainer.style.display = 'block';
        productSearch.focus();
    });

    cancelSearchBtn.addEventListener('click', function() {
        productSearchContainer.style.display = 'none';
        productSearch.value = '';
        productSearchResults.style.display = 'none';
        productSearchResults.innerHTML = '';
    });

    productSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            productSearchResults.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetch(`<?php echo e(route('admin.invoices.api.search-products')); ?>?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(products => {
                    if (products.length > 0) {
                        productSearchResults.innerHTML = products.map(product => `
                            <div class="card mb-2 product-result-item" style="cursor: pointer;" data-product-id="${product.id}" data-product-name="${product.name}" data-product-price="${product.price}">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                                        ${product.image ? `<img src="${product.image}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">` : ''}
                                        <div class="flex-grow-1">
                                            <strong>${product.name}</strong><br>
                                            <small class="text-muted">${product.display}</small>
                                            ${product.stock > 0 ? `<br><small class="text-success">Stock: ${product.stock}</small>` : '<br><small class="text-danger">Rupture de stock</small>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                        productSearchResults.style.display = 'block';
                        
                        // Attacher les événements de clic
                        document.querySelectorAll('.product-result-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const productId = this.dataset.productId;
                                const productName = this.dataset.productName;
                                const productPrice = parseFloat(this.dataset.productPrice);
                                
                                // Ajouter le produit au tableau
                                const index = getNextItemIndex();
                                const row = document.createElement('tr');
                                row.className = 'item-row';
                                row.innerHTML = `
                                    <td>
                                        <input type="text" class="form-control item-description" name="items[${index}][description]" 
                                               value="${productName}" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-quantity" name="items[${index}][quantity]" 
                                               value="1" min="1" step="1" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-price" name="items[${index}][price]" 
                                               value="${productPrice}" step="0.01" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-total" name="items[${index}][total]" 
                                               value="${productPrice}" step="0.01" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                `;
                                itemsTableBody.appendChild(row);
                                attachItemEvents(row);
                                calculateItemsSubtotal();
                                calculateInvoiceTotal();
                                
                                // Fermer la recherche
                                productSearch.value = '';
                                productSearchResults.style.display = 'none';
                                productSearchResults.innerHTML = '';
                            });
                        });
                    } else {
                        productSearchResults.innerHTML = '<div class="alert alert-info">Aucun produit trouvé.</div>';
                        productSearchResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    productSearchResults.innerHTML = '<div class="alert alert-danger">Erreur lors de la recherche.</div>';
                    productSearchResults.style.display = 'block';
                });
        }, 300);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\invoices\create.blade.php ENDPATH**/ ?>