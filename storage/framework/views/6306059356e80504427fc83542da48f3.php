<?php if($paginator->hasPages()): ?>
    <nav aria-label="Pagination Navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fa-solid fa-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Précédent</span>
                    </span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">
                        <i class="fa-solid fa-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Précédent</span>
                    </a>
                </li>
            <?php endif; ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(is_string($element)): ?>
                    <li class="page-item disabled">
                        <span class="page-link"><?php echo e($element); ?></span>
                    </li>
                <?php endif; ?>

                
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="page-item active">
                                <span class="page-link"><?php echo e($page); ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">
                        <span class="d-none d-sm-inline me-1">Suivant</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">
                        <span class="d-none d-sm-inline me-1">Suivant</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                </li>
            <?php endif; ?>
        </ul>
        
        
        <div class="text-center mt-3">
            <div class="pagination-info d-inline-block">
                <small>
                    <i class="fa-solid fa-info-circle me-1"></i>
                    Affichage de <strong><?php echo e($paginator->firstItem()); ?></strong> à <strong><?php echo e($paginator->lastItem()); ?></strong> 
                    sur <strong><?php echo e($paginator->total()); ?></strong> résultats
                    <span class="text-muted">(24 par page)</span>
                </small>
            </div>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\pagination\custom.blade.php ENDPATH**/ ?>