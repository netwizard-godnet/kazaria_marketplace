<?php $__env->startSection('title', 'Gestion du Carousel'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion du Carousel</h4>
        <p class="text-muted">Gérez les slides du carousel principal de votre page d'accueil</p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Une ou plusieurs erreurs sont survenues :</h6>
            <ul class="mb-0 pl-3">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Créer un slide</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.carousel.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Titre du slide</label>
                            <input type="text" name="title" class="form-control" placeholder="Ex: Black Friday Sale">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Ex: Jusqu'à -50% sur tous les produits"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Image du slide</label>
                            <input type="file" name="image" class="form-control" required>
                            <small class="text-muted">Formats libres, aucune limite de taille (pensez simplement à optimiser vos fichiers).</small>
                        </div>
                        <div class="form-group">
                            <label>Lien (optionnel)</label>
                            <input type="url" name="link_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>Texte du bouton</label>
                            <input type="text" name="button_text" class="form-control" placeholder="Ex: Acheter maintenant">
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            </div>
                            <div class="col">
                                <label>Statut</label>
                                <select name="is_active" class="form-control">
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col">
                                <label>Date de début</label>
                                <input type="datetime-local" name="starts_at" class="form-control">
                            </div>
                            <div class="col">
                                <label>Date de fin</label>
                                <input type="datetime-local" name="ends_at" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-plus"></i> Créer le slide
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Slides du carousel</h4>
                </div>
                <div class="card-body">
                    <?php if($slides->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Description</th>
                                    <th>Ordre</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $slides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php if($slide->image_url): ?>
                                            <img src="<?php echo e($slide->image_url); ?>" width="80" height="40" alt="<?php echo e($slide->title); ?>" class="rounded" style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 40px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo e($slide->title ?: 'Sans titre'); ?></strong>
                                        <?php if($slide->button_text): ?>
                                        <br><small class="text-muted">Bouton: <?php echo e($slide->button_text); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo e(Str::limit($slide->description, 50) ?: 'Aucune description'); ?></small>
                                    </td>
                                    <td><?php echo e($slide->sort_order); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($slide->is_active ? 'success' : 'secondary'); ?>">
                                            <?php echo e($slide->is_active ? 'Actif' : 'Inactif'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-warning btn-sm" onclick="editSlide(<?php echo e($slide->id); ?>)" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="<?php echo e(route('admin.carousel.destroy', $slide)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce slide ?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="btn btn-danger btn-sm" type="submit" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3"><?php echo e($slides->links()); ?></div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun slide créé</h5>
                        <p class="text-muted">Créez votre premier slide pour le carousel</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Aperçu du carousel -->
    <?php if($slides->where('is_active', true)->count() > 0): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Aperçu du carousel</h5>
                </div>
                <div class="card-body">
                    <div class="carousel slide" id="previewCarousel" data-bs-ride="carousel" style="max-width: 600px;">
                        <div class="carousel-inner" style="height: 200px;">
                            <?php $__currentLoopData = $slides->where('is_active', true)->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="carousel-item <?php echo e($index === 0 ? 'active' : ''); ?>">
                                <img src="<?php echo e($slide->image_url); ?>" class="d-block w-100 h-100" alt="<?php echo e($slide->title); ?>" style="object-fit: cover;">
                                <?php if($slide->title || $slide->description): ?>
                                <div class="carousel-caption d-none d-md-block">
                                    <?php if($slide->title): ?>
                                    <h5 class="text-white"><?php echo e($slide->title); ?></h5>
                                    <?php endif; ?>
                                    <?php if($slide->description): ?>
                                    <p class="text-white"><?php echo e(Str::limit($slide->description, 100)); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php if($slides->where('is_active', true)->count() > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#previewCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#previewCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal d'édition -->
    <div class="modal fade" id="editSlideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le slide</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="editSlideForm" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Titre du slide</label>
                            <input type="text" id="edit_title" name="title" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Image du slide</label>
                            <input type="file" id="edit_image" name="image" class="form-control">
                            <small class="text-muted">Laisser vide pour conserver l'image actuelle.</small>
                        </div>
                        <div class="form-group">
                            <label>Lien (optionnel)</label>
                            <input type="url" id="edit_link_url" name="link_url" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Texte du bouton</label>
                            <input type="text" id="edit_button_text" name="button_text" class="form-control">
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label>Ordre d'affichage</label>
                                <input type="number" id="edit_sort_order" name="sort_order" class="form-control" min="0">
                            </div>
                            <div class="col">
                                <label>Statut</label>
                                <select id="edit_is_active" name="is_active" class="form-control">
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col">
                                <label>Date de début</label>
                                <input type="datetime-local" id="edit_starts_at" name="starts_at" class="form-control">
                            </div>
                            <div class="col">
                                <label>Date de fin</label>
                                <input type="datetime-local" id="edit_ends_at" name="ends_at" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editSlide(slideId) {
    // Récupérer les données du slide via AJAX
    fetch(`/admin/carousel/${slideId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const slide = data.slide;
            
            // Remplir le formulaire
            document.getElementById('edit_title').value = slide.title || '';
            document.getElementById('edit_description').value = slide.description || '';
            document.getElementById('edit_link_url').value = slide.link_url || '';
            document.getElementById('edit_button_text').value = slide.button_text || '';
            document.getElementById('edit_sort_order').value = slide.sort_order || 0;
            document.getElementById('edit_is_active').value = slide.is_active ? '1' : '0';
            document.getElementById('edit_starts_at').value = slide.starts_at ? slide.starts_at.substring(0, 16) : '';
            document.getElementById('edit_ends_at').value = slide.ends_at ? slide.ends_at.substring(0, 16) : '';
            
            // Définir l'action du formulaire
            document.getElementById('editSlideForm').action = `/admin/carousel/${slideId}`;
            
            // Ouvrir la modal
            $('#editSlideModal').modal('show');
        } else {
            alert('Erreur lors du chargement des données du slide');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors du chargement des données du slide');
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\carousel\index.blade.php ENDPATH**/ ?>