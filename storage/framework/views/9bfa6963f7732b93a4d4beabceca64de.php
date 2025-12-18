<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="logo">
                <img src="<?php echo e(asset('storage/' . ($adminSettings['site_logo']->value ?? 'logo.png'))); ?>" alt="<?php echo e($adminSettings['site_name']->value ?? 'KAZARIA'); ?> Admin" class="navbar-brand" height="30" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <!-- Dashboard -->
                <li class="nav-item <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.dashboard')); ?>">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Gestion des utilisateurs -->
                <?php if(canAccessAny(['view_users', 'view_products', 'view_orders', 'view_stores', 'manage_messages', 'manage_payments', 'view_invoices', 'manage_categories', 'manage_subcategories', 'manage_attributes'])): ?>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Gestion</h4>
                </li>
                <?php endif; ?>

                <?php if(canAccess('view_users')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#users" class="<?php echo e(request()->routeIs('admin.users.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.users.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-users"></i>
                        <p>Utilisateurs</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.users.*') ? 'show' : ''); ?>" id="users">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.users.index')); ?>">
                                    <span class="sub-item">Tous les utilisateurs</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.users.sellers')); ?>">
                                    <span class="sub-item">Vendeurs</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.users.customers')); ?>">
                                    <span class="sub-item">Clients</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des produits -->
                <?php if(canAccess('view_products')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.products.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#products" class="<?php echo e(request()->routeIs('admin.products.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.products.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-box"></i>
                        <p>Produits</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.products.*') ? 'show' : ''); ?>" id="products">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.products.index')); ?>">
                                    <span class="sub-item">Tous les produits</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des commandes -->
                <?php if(canAccess('view_orders')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.orders.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#orders" class="<?php echo e(request()->routeIs('admin.orders.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.orders.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Commandes</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.orders.*') ? 'show' : ''); ?>" id="orders">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.orders.index')); ?>">
                                    <span class="sub-item">Toutes les commandes</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des boutiques -->
                <?php if(canAccess('view_stores')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.stores.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#stores" class="<?php echo e(request()->routeIs('admin.stores.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.stores.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-store"></i>
                        <p>Boutiques</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.stores.*') ? 'show' : ''); ?>" id="stores">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.stores.index')); ?>">
                                    <span class="sub-item">Toutes les boutiques</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des messages -->
                <?php if(canAccess('manage_messages')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.messages.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#messages" class="<?php echo e(request()->routeIs('admin.messages.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.messages.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-comments"></i>
                        <p>Messages</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.messages.*') ? 'show' : ''); ?>" id="messages">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.messages.index')); ?>">
                                    <span class="sub-item">Tous les messages</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des paiements -->
                <?php if(canAccess('manage_payments')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.payments.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#payments" class="<?php echo e(request()->routeIs('admin.payments.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.payments.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-credit-card"></i>
                        <p>Paiements</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.payments.*') ? 'show' : ''); ?>" id="payments">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.payments.index')); ?>">
                                    <span class="sub-item">Tous les paiements</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des factures -->
                <?php if(canAccess('view_invoices')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.invoices.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#invoices" class="<?php echo e(request()->routeIs('admin.invoices.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.invoices.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-file-invoice"></i>
                        <p>Factures</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.invoices.*') ? 'show' : ''); ?>" id="invoices">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.invoices.index')); ?>">
                                    <span class="sub-item">Toutes les factures</span>
                                </a>
                            </li>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_invoices')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.invoices.create')); ?>">
                                    <span class="sub-item">Créer une facture</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des catégories -->
                <?php if(canAccess('manage_categories')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#categories" class="<?php echo e(request()->routeIs('admin.categories.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.categories.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-tags"></i>
                        <p>Catégories</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.categories.*') ? 'show' : ''); ?>" id="categories">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.categories.index')); ?>">
                                    <span class="sub-item">Toutes les catégories</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des sous-catégories -->
                <?php if(canAccess('manage_subcategories')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.subcategories.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#subcategories" class="<?php echo e(request()->routeIs('admin.subcategories.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.subcategories.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-tag"></i>
                        <p>Sous-catégories</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.subcategories.*') ? 'show' : ''); ?>" id="subcategories">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.subcategories.index')); ?>">
                                    <span class="sub-item">Toutes les sous-catégories</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Gestion des attributs -->
                <?php if(canAccess('manage_attributes')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.attributes.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#attributes" class="<?php echo e(request()->routeIs('admin.attributes.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.attributes.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-list-ul"></i>
                        <p>Attributs</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.attributes.*') ? 'show' : ''); ?>" id="attributes">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.attributes.index')); ?>">
                                    <span class="sub-item">Tous les attributs</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.attributes.create')); ?>">
                                    <span class="sub-item">Créer un attribut</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

        <!-- Gestion du contenu -->
        <?php if(canAccessAny(['manage_banners', 'manage_settings', 'manage_carousel', 'manage_brands'])): ?>
        <li class="nav-section">
            <span class="sidebar-mini-icon">
                <i class="fa fa-ellipsis-h"></i>
            </span>
            <h4 class="text-section">Contenu</h4>
        </li>
        <?php endif; ?>

        <?php if(canAccess('manage_banners')): ?>
        <li class="nav-item <?php echo e(request()->routeIs('admin.banners.*') ? 'active' : ''); ?>">
            <a href="<?php echo e(route('admin.banners.index')); ?>">
                <i class="fas fa-image"></i>
                <p>Bannières</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if(canAccess('manage_settings')): ?>
        <li class="nav-item <?php echo e(request()->routeIs('admin.newsletter.*') ? 'active' : ''); ?>">
            <a href="<?php echo e(route('admin.newsletter.index')); ?>">
                <i class="fas fa-envelope-open"></i>
                <p>Newsletter</p>
            </a>
        </li>

        <li class="nav-item <?php echo e(request()->routeIs('admin.popups.*') ? 'active' : ''); ?>">
            <a href="<?php echo e(route('admin.popups.index')); ?>">
                <i class="fas fa-window-maximize"></i>
                <p>Pop-ups</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if(canAccess('manage_carousel')): ?>
        <li class="nav-item <?php echo e(request()->routeIs('admin.carousel.*') ? 'active' : ''); ?>">
            <a href="<?php echo e(route('admin.carousel.index')); ?>">
                <i class="fas fa-images"></i>
                <p>Carousel Principal</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if(canAccess('manage_brands')): ?>
        <li class="nav-item <?php echo e(request()->routeIs('admin.brands.*') ? 'active' : ''); ?>">
            <a href="<?php echo e(route('admin.brands.index')); ?>">
                <i class="fas fa-tags"></i>
                <p>Marques</p>
            </a>
        </li>
        <?php endif; ?>

        <!-- Rapports et statistiques -->
        <?php if(canAccessAny(['view_reports', 'view_statistics'])): ?>
        <li class="nav-section">
            <span class="sidebar-mini-icon">
                <i class="fa fa-ellipsis-h"></i>
            </span>
            <h4 class="text-section">Rapports</h4>
        </li>
        <?php endif; ?>

                <?php if(canAccess('view_reports')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.reports.*') ? 'active' : ''); ?>">
                    <a data-bs-toggle="collapse" href="#reports" class="<?php echo e(request()->routeIs('admin.reports.*') ? '' : 'collapsed'); ?>" aria-expanded="<?php echo e(request()->routeIs('admin.reports.*') ? 'true' : 'false'); ?>">
                        <i class="fas fa-chart-bar"></i>
                        <p>Rapports</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.reports.*') ? 'show' : ''); ?>" id="reports">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="<?php echo e(route('admin.reports.index')); ?>">
                                    <span class="sub-item">Rapports</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.reports.sales')); ?>">
                                    <span class="sub-item">Ventes</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.reports.users')); ?>">
                                    <span class="sub-item">Utilisateurs</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('admin.reports.products')); ?>">
                                    <span class="sub-item">Produits</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if(canAccess('view_statistics')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.statistics.*') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.statistics.index')); ?>">
                        <i class="fas fa-chart-line"></i>
                        <p>Statistiques</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Configuration -->
                <?php if(canAccessAny(['manage_settings', 'manage_coupons', 'manage_roles'])): ?>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Configuration</h4>
                </li>
                <?php endif; ?>

                <?php if(canAccess('manage_settings')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.settings.index')); ?>">
                        <i class="fas fa-cog"></i>
                        <p>Paramètres</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(canAccess('manage_coupons')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.coupons.*') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.coupons.index')); ?>">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Codes promo</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(canAccess('manage_roles')): ?>
                <li class="nav-item <?php echo e(request()->routeIs('admin.roles.*') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.roles.index')); ?>">
                        <i class="fas fa-user-shield"></i>
                        <p>Rôles & Permissions</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Retour au site -->
                <li class="nav-item">
                    <a href="<?php echo e(route('accueil')); ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <p>Voir le site</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- End Sidebar -->

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\layouts\sidebar.blade.php ENDPATH**/ ?>