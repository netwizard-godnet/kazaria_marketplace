<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $__env->yieldContent('title', 'KAZARIA Admin Dashboard'); ?></title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <?php
        $siteFavicon = \App\Models\Setting::get('site_favicon');
        $faviconUrl = $siteFavicon ? asset('storage/' . ltrim($siteFavicon, '/')) : asset('favicon.png');
    ?>
    <link rel="icon" href="<?php echo e($faviconUrl); ?>" type="image/x-icon" />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Fonts and icons -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/webfont/webfont.min.js')); ?>"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular", 
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["<?php echo e(asset('kazaria-admin/assets/css/fonts.min.css')); ?>"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

        <!-- CSS Files -->
        <link rel="stylesheet" href="<?php echo e(asset('kazaria-admin/assets/css/bootstrap.min.css')); ?>" />
        <link rel="stylesheet" href="<?php echo e(asset('kazaria-admin/assets/css/plugins.min.css')); ?>" />
        <link rel="stylesheet" href="<?php echo e(asset('kazaria-admin/assets/css/kaiadmin.min.css')); ?>" />
        
        <!-- CSS Just for demo purpose, don't include it in your project -->
        <link rel="stylesheet" href="<?php echo e(asset('kazaria-admin/assets/css/demo.css')); ?>" />
        
        <!-- CSS for icons -->
        <link rel="stylesheet" href="<?php echo e(asset('kazaria-admin/assets/css/fonts.min.css')); ?>" />
        
        <!-- Custom KAZARIA Admin CSS -->
        <link rel="stylesheet" href="<?php echo e(asset('css/admin-custom.css')); ?>" />
        
        <!-- Admin Header CSS -->
        <link rel="stylesheet" href="<?php echo e(asset('css/admin-header.css')); ?>" />
    
        <!-- Custom Admin CSS -->
    <style>
        :root {
            --kazaria-orange: #ff6b35;
            --kazaria-orange-dark: #e55a2b;
        }
        
        .sidebar[data-background-color="dark"] {
            background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        }
        
        .sidebar .nav-item.active > a {
            background-color: var(--kazaria-orange) !important;
            color: white !important;
        }
        
        .sidebar .nav-item > a:hover {
            background-color: rgba(255, 107, 53, 0.1);
            color: var(--kazaria-orange) !important;
        }
        
        .btn-primary {
            background-color: var(--kazaria-orange);
            border-color: var(--kazaria-orange);
        }
        
        .btn-primary:hover {
            background-color: var(--kazaria-orange-dark);
            border-color: var(--kazaria-orange-dark);
        }
        
        .text-primary {
            color: var(--kazaria-orange) !important;
        }
        
        .bg-primary {
            background-color: var(--kazaria-orange) !important;
        }
    </style>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
    <body>
    <div class="wrapper">
        <?php echo $__env->make('admin.layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        
        <div class="main-panel">
            <?php echo $__env->make('admin.layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            
            <div class="content">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
            
            <?php echo $__env->make('admin.layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/core/jquery-3.7.1.min.js')); ?>"></script>
    <script src="<?php echo e(asset('kazaria-admin/assets/js/core/popper.min.js')); ?>"></script>
    <script src="<?php echo e(asset('kazaria-admin/assets/js/core/bootstrap.min.js')); ?>"></script>
    
    <!-- jQuery Scrollbar -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js')); ?>"></script>
    
    <!-- Chart JS -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/chart.js/chart.min.js')); ?>"></script>
    
    <!-- jQuery Sparkline -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js')); ?>"></script>
    
    <!-- Chart Circle -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/chart-circle/circles.min.js')); ?>"></script>
    
    <!-- Datatables -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/datatables/datatables.min.js')); ?>"></script>
    
    <!-- Bootstrap Notify -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js')); ?>"></script>
    
    <!-- jQuery Vector Map -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/vector-map/jquery-jvectormap-2.0.3.min.js')); ?>"></script>
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/vector-map/jquery-jvectormap-world-mill.js')); ?>"></script>
    
    <!-- Sweet Alert -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/plugin/sweetalert/sweetalert.min.js')); ?>"></script>
    
    <!-- Kaiadmin JS -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/kaiadmin.min.js')); ?>"></script>
    
    <!-- Demo JS -->
    <script src="<?php echo e(asset('kazaria-admin/assets/js/demo.js')); ?>"></script>
    
    <!-- Fonction utilitaire pour gérer les loaders sur les boutons -->
    <script>
    // Fonction utilitaire pour gérer les loaders sur les boutons
    function setButtonLoading(button, isLoading, originalText = null) {
        if (!button) return;
        
        if (isLoading) {
            button.dataset.originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Traitement en cours...';
            button.style.cursor = 'not-allowed';
        } else {
            button.disabled = false;
            button.innerHTML = originalText || button.dataset.originalText || 'Valider';
            button.style.cursor = 'pointer';
        }
    }
    
    // Rendre la fonction accessible globalement
    window.setButtonLoading = setButtonLoading;
    
    // Intercepter automatiquement les soumissions de formulaires pour ajouter des loaders
    document.addEventListener('DOMContentLoaded', function() {
        // Intercepter les soumissions de formulaires
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName === 'FORM') {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton && !submitButton.disabled) {
                    setButtonLoading(submitButton, true);
                }
            }
        });
        
        // Intercepter les clics sur les boutons avec onclick qui font des fetch
        document.addEventListener('click', function(e) {
            const button = e.target.closest('button[onclick]');
            if (button && button.onclick && button.onclick.toString().includes('fetch')) {
                // Ne pas intercepter si c'est déjà géré dans la fonction onclick
                // On laisse les fonctions individuelles gérer leurs loaders
            }
        });
    });
    </script>
    
    <style>
    /* Styles pour les loaders de boutons */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }
    
    button:disabled {
        opacity: 0.65;
        cursor: not-allowed !important;
    }
    </style>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\layouts\app.blade.php ENDPATH**/ ?>