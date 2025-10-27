<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <img src="{{ asset('storage/' . ($adminSettings['site_logo']->value ?? 'logo.png')) }}" alt="{{ $adminSettings['site_name']->value ?? 'KAZARIA' }} Admin" class="navbar-brand" height="30" />
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
                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Gestion des utilisateurs -->
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Gestion</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#users" class="{{ request()->routeIs('admin.users.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}">
                        <i class="fas fa-users"></i>
                        <p>Utilisateurs</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.users.*') ? 'show' : '' }}" id="users">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.users.index') }}">
                                    <span class="sub-item">Tous les utilisateurs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.sellers') }}">
                                    <span class="sub-item">Vendeurs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.customers') }}">
                                    <span class="sub-item">Clients</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestion des produits -->
                <li class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#products" class="{{ request()->routeIs('admin.products.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.products.*') ? 'true' : 'false' }}">
                        <i class="fas fa-box"></i>
                        <p>Produits</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.products.*') ? 'show' : '' }}" id="products">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.products.index') }}">
                                    <span class="sub-item">Tous les produits</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestion des commandes -->
                <li class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#orders" class="{{ request()->routeIs('admin.orders.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.orders.*') ? 'true' : 'false' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Commandes</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.orders.*') ? 'show' : '' }}" id="orders">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.orders.index') }}">
                                    <span class="sub-item">Toutes les commandes</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestion des boutiques -->
                <li class="nav-item {{ request()->routeIs('admin.stores.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#stores" class="{{ request()->routeIs('admin.stores.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.stores.*') ? 'true' : 'false' }}">
                        <i class="fas fa-store"></i>
                        <p>Boutiques</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.stores.*') ? 'show' : '' }}" id="stores">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.stores.index') }}">
                                    <span class="sub-item">Toutes les boutiques</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestion des catégories -->
                <li class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#categories" class="{{ request()->routeIs('admin.categories.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.categories.*') ? 'true' : 'false' }}">
                        <i class="fas fa-tags"></i>
                        <p>Catégories</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.categories.*') ? 'show' : '' }}" id="categories">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.categories.index') }}">
                                    <span class="sub-item">Toutes les catégories</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestion des sous-catégories -->
                <li class="nav-item {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#subcategories" class="{{ request()->routeIs('admin.subcategories.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.subcategories.*') ? 'true' : 'false' }}">
                        <i class="fas fa-tag"></i>
                        <p>Sous-catégories</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.subcategories.*') ? 'show' : '' }}" id="subcategories">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.subcategories.index') }}">
                                    <span class="sub-item">Toutes les sous-catégories</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestion des attributs -->
                <li class="nav-item {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#attributes" class="{{ request()->routeIs('admin.attributes.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.attributes.*') ? 'true' : 'false' }}">
                        <i class="fas fa-list-ul"></i>
                        <p>Attributs</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.attributes.*') ? 'show' : '' }}" id="attributes">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.attributes.index') }}">
                                    <span class="sub-item">Tous les attributs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.attributes.create') }}">
                                    <span class="sub-item">Créer un attribut</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

        <!-- Gestion du contenu -->
        <li class="nav-section">
            <span class="sidebar-mini-icon">
                <i class="fa fa-ellipsis-h"></i>
            </span>
            <h4 class="text-section">Contenu</h4>
        </li>


        <li class="nav-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
            <a href="{{ route('admin.banners.index') }}">
                <i class="fas fa-image"></i>
                <p>Bannières</p>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.carousel.*') ? 'active' : '' }}">
            <a href="{{ route('admin.carousel.index') }}">
                <i class="fas fa-images"></i>
                <p>Carousel Principal</p>
            </a>
        </li>

        <!-- Rapports et statistiques -->
        <li class="nav-section">
            <span class="sidebar-mini-icon">
                <i class="fa fa-ellipsis-h"></i>
            </span>
            <h4 class="text-section">Rapports</h4>
        </li>

                <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#reports" class="{{ request()->routeIs('admin.reports.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}">
                        <i class="fas fa-chart-bar"></i>
                        <p>Rapports</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="reports">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.reports.index') }}">
                                    <span class="sub-item">Rapports</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.reports.sales') }}">
                                    <span class="sub-item">Ventes</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.reports.users') }}">
                                    <span class="sub-item">Utilisateurs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.reports.products') }}">
                                    <span class="sub-item">Produits</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Configuration -->
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Configuration</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-cog"></i>
                        <p>Paramètres</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}">
                        <i class="fas fa-user-shield"></i>
                        <p>Rôles & Permissions</p>
                    </a>
                </li>

                <li class="d-none nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}">
                        <i class="fas fa-tags"></i>
                        <p>Catégories</p>
                    </a>
                </li>

                <!-- Retour au site -->
                <li class="nav-item">
                    <a href="{{ route('accueil') }}" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <p>Voir le site</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- End Sidebar -->

