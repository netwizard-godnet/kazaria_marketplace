<footer class="footer">
    <div class="container-fluid d-flex justify-content-between">
        <nav class="pull-left">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('accueil')); ?>" target="_blank">
                        KAZARIA Marketplace
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('admin.help')); ?>">Aide</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('admin.documentation')); ?>">Documentation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('contact')); ?>" target="_blank">Support</a>
                </li>
            </ul>
        </nav>
        <div class="copyright">
            <?php echo e(date('Y')); ?>, fait avec <i class="fa fa-heart heart text-danger"></i> par
            <a href="<?php echo e(route('accueil')); ?>" target="_blank">KAZARIA</a>
        </div>
        <div>
            Version <?php echo e(config('app.version', '1.0.0')); ?> |
            <a href="<?php echo e(route('admin.changelog')); ?>">Changelog</a>
        </div>
    </div>
</footer>

<?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/layouts/footer.blade.php ENDPATH**/ ?>