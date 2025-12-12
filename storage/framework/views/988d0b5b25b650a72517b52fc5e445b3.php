<?php
    use Illuminate\Support\Str;
    $deviceKeys = array_keys($devices ?? []);
    $selectedDevices = old('display_devices', $popup->display_devices ?? $deviceKeys);
    $selectedPages = old('display_pages', $popup->display_pages ?? []);
    $presetKeys = array_keys($pagePresets ?? []);
    $presetSelection = array_values(array_intersect($selectedPages ?? [], $presetKeys));
    $customPagesDefault = implode(',', array_diff($selectedPages ?? [], $presetKeys));
    $customPagesInput = old('display_pages_custom', $customPagesDefault);
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Contenu de la popup</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Titre</label>
                    <input type="text" name="title" class="form-control" value="<?php echo e(old('title', $popup->title)); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug personnalisé</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo e(old('slug', $popup->slug)); ?>" placeholder="Laissez vide pour générer automatiquement">
                    <small class="text-muted">Utilisé comme identifiant unique pour la popup.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu HTML</label>
                    <textarea name="content" class="form-control" rows="8" placeholder="HTML de votre popup"><?php echo e(old('content', $popup->content)); ?></textarea>
                    <small class="text-muted">Vous pouvez inclure du HTML et des éléments dynamiques.</small>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Texte du bouton</label>
                        <input type="text" name="cta_text" class="form-control" value="<?php echo e(old('cta_text', $popup->cta_text)); ?>" placeholder="Ex: Découvrir l'offre">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lien du bouton</label>
                        <input type="url" name="cta_url" class="form-control" value="<?php echo e(old('cta_url', $popup->cta_url)); ?>" placeholder="https://">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Affichage & ciblage</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date de début</label>
                        <input type="datetime-local" name="display_start" class="form-control"
                               value="<?php echo e(old('display_start', optional($popup->display_start)->format('Y-m-d\TH:i'))); ?>">
                        <small class="text-muted">Laissez vide pour démarrer immédiatement.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin</label>
                        <input type="datetime-local" name="display_end" class="form-control"
                               value="<?php echo e(old('display_end', optional($popup->display_end)->format('Y-m-d\TH:i'))); ?>">
                        <small class="text-muted">Laissez vide pour un affichage sans échéance.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pages concernées</label>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <?php $__currentLoopData = $pagePresets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="page_<?php echo e($key); ?>" name="display_pages[]" value="<?php echo e($key); ?>"
                                       <?php echo e(in_array($key, $presetSelection ?? [], true) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="page_<?php echo e($key); ?>"><?php echo e($label); ?></label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <input type="text" name="display_pages_custom" class="form-control" placeholder="URL(s) personnalisée(s) séparées par des virgules"
                           value="<?php echo e($customPagesInput); ?>">
                    <small class="text-muted">Exemple : /promotions,/offres-speciales</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Appareils ciblés</label>
                    <div class="form-check form-switch">
                        <?php $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check form-switch form-check-inline">
                                <input class="form-check-input" type="checkbox" id="device_<?php echo e($key); ?>" name="display_devices[]" value="<?php echo e($key); ?>"
                                       <?php echo e(in_array($key, $selectedDevices ?? [], true) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="device_<?php echo e($key); ?>"><?php echo e($label); ?></label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Paramètres d'affichage</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           <?php echo e(old('is_active', $popup->is_active ?? false) ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="is_active">Popup active</label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fréquence d'affichage</label>
                    <select name="frequency" class="form-select">
                        <?php $__currentLoopData = $frequencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php echo e(old('frequency', $popup->frequency ?? 'once_per_session') === $value ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Délai avant affichage (secondes)</label>
                    <input type="number" name="delay_seconds" class="form-control" min="0" max="86400"
                           value="<?php echo e(old('delay_seconds', $popup->delay_seconds ?? 0)); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre maximum d'affichages</label>
                    <input type="number" name="max_impressions" class="form-control" min="1"
                           value="<?php echo e(old('max_impressions', $popup->max_impressions)); ?>" placeholder="Illimité">
                </div>

                <div class="mb-3">
                    <label class="form-label">Priorité d'affichage</label>
                    <input type="number" name="priority" class="form-control" min="0" max="1000"
                           value="<?php echo e(old('priority', $popup->priority ?? 0)); ?>">
                    <small class="text-muted">Plus le nombre est élevé, plus la popup est prioritaire.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Disposition</label>
                    <select name="layout" class="form-select">
                        <?php $__currentLoopData = $layouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php echo e(old('layout', $popup->layout ?? 'left-right') === $value ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">Choisissez comment l'image et le contenu sont disposés.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Image (optionnel)</label>
                    <?php if($popup->image): ?>
                        <div class="mb-2">
                            <img src="<?php echo e(Str::startsWith($popup->image, ['http://', 'https://']) ? $popup->image : asset('storage/' . ltrim($popup->image, '/'))); ?>" alt="Image popup" class="img-fluid rounded border">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                            <label class="form-check-label" for="remove_image">Supprimer l'image actuelle</label>
                        </div>
                        <input type="hidden" name="image_path" value="<?php echo e($popup->image); ?>">
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">JPEG, PNG, WEBP ou GIF (4 Mo maximum)</small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-save me-1"></i> Enregistrer
                </button>
                <a href="<?php echo e(route('admin.popups.index')); ?>" class="btn btn-outline-secondary w-100 mt-2">
                    Annuler
                </a>
            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\popups\_form.blade.php ENDPATH**/ ?>