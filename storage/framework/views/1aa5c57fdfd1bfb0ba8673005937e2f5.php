

<?php $__env->startSection('title', 'Gestion des Produits'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Produits</h4>
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
                <span>Produits</span>
            </li>
        </ul>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Gestion des Produits</h3>
                        <div class="d-flex">
                            <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-success mr-2 text-nowrap" style="">
                                <i class="fas fa-plus"></i> Ajouter un produit
                            </a>
                            <input type="text" class="form-control mr-2" placeholder="Rechercher un produit..." id="searchInput" value="<?php echo e(request('search')); ?>">
                            <select class="form-control mr-2" id="statusFilter" onchange="filterByStatus()">
                                <option value="">Tous les statuts</option>
                                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Actifs</option>
                                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactifs</option>
                            </select>
                            <select class="form-control mr-2" id="categoryFilter" onchange="filterByCategory()">
                                <option value="">Toutes les catégories</option>
                                <?php $__currentLoopData = \App\Models\Category::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" <?php echo e(request('category') == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button class="btn btn-primary" onclick="searchProducts()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Nom</th>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                    <th>Catégorie</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($product->id); ?></td>
                                    <td>
                                        <?php if($product->first_image_url): ?>
                                            <img src="<?php echo e($product->first_image_url); ?>" alt="<?php echo e($product->name); ?>" class="product-image">
                                        <?php else: ?>
                                            <div class="product-image-placeholder">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(Str::limit($product->name, 30)); ?></td>
                                    <td><?php echo e(number_format($product->price, 0, ',', ' ')); ?> FCFA</td>
                                    <td>
                                        <span class="badge badge-<?php echo e($product->stock > 0 ? 'success' : 'danger'); ?>">
                                            <?php echo e($product->stock); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($product->category->name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($product->is_active ? 'success' : 'danger'); ?>">
                                            <?php echo e($product->is_active ? 'Actif' : 'Inactif'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Bouton Voir -->
                                            <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="btn btn-info btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Bouton Modifier -->
                                            <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-warning btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Bouton Toggle Status -->
                                            <form action="<?php echo e(route('admin.products.toggle-status', $product)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php if($product->is_active): ?>
                                                    <button type="submit" class="btn btn-secondary btn-sm" title="Désactiver" onclick="return confirm('Êtes-vous sûr de vouloir désactiver ce produit ?')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-success btn-sm" title="Activer" onclick="return confirm('Êtes-vous sûr de vouloir activer ce produit ?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            
                                            <!-- Bouton Supprimer -->
                                            <form action="<?php echo e(route('admin.products.destroy', $product)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucun produit trouvé</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if($products->hasPages()): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Affichage de <?php echo e($products->firstItem()); ?> à <?php echo e($products->lastItem()); ?> sur <?php echo e($products->total()); ?> produits
                        </div>
                        <div class="pagination-wrapper">
                            <?php echo e($products->appends(request()->query())->links('pagination.bootstrap-4')); ?>

                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Styles pour les images de produits */
.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.product-image-placeholder {
    width: 50px;
    height: 50px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Styles pour la pagination */
.pagination-wrapper {
    margin-top: 0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    background-color: #fff;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Amélioration du tableau */
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

/* Amélioration des boutons d'action */
.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Responsive pour les filtres */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        gap: 10px;
    }
    
    .card-header .d-flex > * {
        width: 100%;
    }
}

/* Amélioration des badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-danger {
    background-color: #dc3545;
    color: #fff;
}

.badge-secondary {
    background-color: #6c757d;
    color: #fff;
}

/* Amélioration des boutons */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
}

/* Amélioration du tableau */
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.02);
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function searchProducts() {
    const searchTerm = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const category = document.getElementById('categoryFilter').value;

    let url = `<?php echo e(route('admin.products.index')); ?>?`;
    const params = new URLSearchParams();

    if (searchTerm.trim()) {
        params.append('search', searchTerm);
    }
    if (status) {
        params.append('status', status);
    }
    if (category) {
        params.append('category', category);
    }

    window.location.href = url + params.toString();
}

// Recherche en temps réel
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchProducts();
    }
});

function filterByStatus() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;

    let url = `<?php echo e(route('admin.products.index')); ?>?`;
    const params = new URLSearchParams();

    if (search.trim()) {
        params.append('search', search);
    }
    if (status) {
        params.append('status', status);
    }
    if (category) {
        params.append('category', category);
    }

    window.location.href = url + params.toString();
}

function filterByCategory() {
    const category = document.getElementById('categoryFilter').value;
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;

    let url = `<?php echo e(route('admin.products.index')); ?>?`;
    const params = new URLSearchParams();

    if (search.trim()) {
        params.append('search', search);
    }
    if (status) {
        params.append('status', status);
    }
    if (category) {
        params.append('category', category);
    }

    window.location.href = url + params.toString();
}

function editProduct(productId) {
    // Rediriger vers la page d'édition ou ouvrir un modal
    window.location.href = `/admin/products/${productId}/edit`;
}

function toggleProductStatus(productId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de ce produit ?')) {
        // Créer un formulaire pour la requête POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/products/${productId}/toggle-status`;

        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            csrfToken.value = token.getAttribute('content');
        } else {
            alert('Erreur: Token de sécurité manquant');
            return;
        }
        form.appendChild(csrfToken);

        // Ajouter au DOM et soumettre
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteProduct(productId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.')) {
        // Créer un formulaire pour la requête DELETE
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/products/${productId}`;

        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            csrfToken.value = token.getAttribute('content');
        } else {
            alert('Erreur: Token de sécurité manquant');
            return;
        }
        form.appendChild(csrfToken);

        // Ajouter la méthode DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);

        // Ajouter au DOM et soumettre
        document.body.appendChild(form);
        form.submit();
    }
}

</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\products\index.blade.php ENDPATH**/ ?>