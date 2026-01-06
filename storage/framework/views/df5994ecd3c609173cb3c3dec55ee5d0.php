<?php echo $__env->make('layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Container global pour les alertes Bootstrap -->
    <div id="alertContainer" class="position-fixed top-0 start-50 translate-middle-x mt-3 z-index-9x" style="z-index: 11000; min-width: 400px; max-width: 600px;"></div>

    
    <?php echo $__env->yieldContent('content'); ?>

<?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/layouts/app.blade.php ENDPATH**/ ?>