

<?php $__env->startSection('title', 'Rapport des ventes'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport des ventes</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="<?php echo e(route('admin.reports.index')); ?>">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Ventes</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.reports.sales')); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catégorie</label>
                    <select name="category_id" id="report_category" class="form-select">
                        <option value="">Toutes</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php echo e((string)request('category_id') === (string)$category->id ? 'selected' : ''); ?>><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sous-catégorie</label>
                    <select name="subcategory_id" id="report_subcategory" class="form-select">
                        <option value="">Toutes</option>
                    </select>
                </div>
                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="<?php echo e(route('admin.reports.sales')); ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="<?php echo e(route('admin.reports.export', 'sales')); ?>" class="btn btn-outline-success"><i class="fas fa-download"></i> Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-1">Total commandes</h5>
                    <h3 class="mb-0"><?php echo e(number_format($totals['orders'] ?? 0, 0, ',', ' ')); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-1">Chiffre d'affaires</h5>
                    <h3 class="mb-0"><?php echo e(number_format($totals['amount'] ?? 0, 0, ',', ' ')); ?> FCFA</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nb commandes</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $salesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(\Carbon\Carbon::parse($row->date)->format('d/m/Y')); ?></td>
                            <td><?php echo e($row->orders_count); ?></td>
                            <td><?php echo e(number_format($row->total_amount, 0, ',', ' ')); ?> FCFA</td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center">Aucune donnée</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const categories = <?php echo json_encode($categoriesJson, 15, 512) ?>;

document.addEventListener('DOMContentLoaded', () => {
    const c = document.getElementById('report_category');
    const s = document.getElementById('report_subcategory');
    if (!c || !s) return;
    const selectedSub = '<?php echo e(request('subcategory_id')); ?>';
    function fillSubs(id){
        s.innerHTML = '<option value="">Toutes</option>';
        const cat = categories.find(x => String(x.id) === String(id));
        if (cat){
            cat.subcategories.forEach(sc => {
                const opt = document.createElement('option');
                opt.value = sc.id; opt.textContent = sc.name;
                if (String(selectedSub) === String(sc.id)) opt.selected = true;
                s.appendChild(opt);
            });
        }
    }
    if (c.value) fillSubs(c.value);
    c.addEventListener('change', e => fillSubs(e.target.value));
});
</script>
<?php $__env->stopPush(); ?>



<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\reports\sales.blade.php ENDPATH**/ ?>