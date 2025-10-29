<footer class="container-fluid pt-2">
    <div class="container py-5 border-top border-bottom">
        <div class="row g-3">
            <div class="col-md-3">
                <p class="mb-2 fw-bold">BESOIN D'AIDE ?</p>
                <div class="vstack gap-1 text-start ms-2">
                    <a href="https://wa.me/<?php echo e(str_replace(['+', ' ', '-'], '', $settings['contact_phone'] ?? '2250701234567')); ?>" class="btn btn-sm text-secondary text-start fs-8" target="_blank">Discuter avec nous</a>
                    <a href="<?php echo e(route('help-faq')); ?>" class="btn btn-sm text-secondary text-start fs-8">Aide & FAQ</a>
                    <a href="<?php echo e(route('contact')); ?>" class="btn btn-sm text-secondary text-start fs-8">Contactez-nous</a>
                </div>
                <p class="mt-3 mb-2 fw-bold">LIENS UTILES</p>
                <div class="vstack gap-1 ms-2">
                    <a href="<?php echo e(route('suivre-commande')); ?>" class="btn btn-sm text-secondary text-start fs-8">Suivre sa commande</a>
                    <a href="<?php echo e(route('expedition-livraison')); ?>" class="btn btn-sm text-secondary text-start fs-8">Expédition & Livraison</a>
                    <a href="<?php echo e(route('politique-retour')); ?>" class="btn btn-sm text-secondary text-start fs-8">Politique de retour</a>
                    <a href="<?php echo e(route('comment-commander')); ?>" class="btn btn-sm text-secondary text-start fs-8">Comment commander ?</a>
                    <a href="<?php echo e(route('agences-points-relais')); ?>" class="btn btn-sm text-secondary text-start fs-8">Agences & Points de relais KAZARIA?</a>
                </div>
            </div>
            <div class="col-md-3">
                <p class="mb-2 fw-bold">A PROPOS</p>
                <div class="vstack gap-1 ms-2">
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Qui nous sommes ?</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Carrières chez KAZARIA</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Conditions générales d'utilisation</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">KAZARIA Express</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Toutes les boutiques officielles</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Vente Flash</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Directives relatives informations de paiements sur KAZARIA</a>
                </div>
            </div>
            <div class="col-md-3">
                <p class="mb-2 fw-bold">GAGNER DE L'ARGENT</p>
                <div class="vstack gap-1 ms-2">
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Vendre sur KAZARIA</a>
                    <a onclick="goToSell(event)" class="btn btn-sm text-secondary text-start fs-8">Espace vendeur</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8 d-none">Devenez consultant KAZARIA</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8 d-none">Devenez partenaire de service loqistique</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Toutes les boutiques officielles</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Vente Flash</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8 d-none">Directives relatives informations de paiements sur KAZARIA</a>
                </div>
            </div>
            <div class="col-md-3">
                <p class="mb-2 fw-bold">CATEGORIES</p>
                <div class="vstack gap-1 ms-2">
                    <?php if(isset($allCategories) && $allCategories->count() > 0): ?>
                        <?php $__currentLoopData = $allCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="btn btn-sm text-secondary text-start fs-8"><?php echo e($category->name); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        
                        <a href="#" class="btn btn-sm text-secondary text-start fs-8">Téléphones et tablettes</a>
                        <a href="#" class="btn btn-sm text-secondary text-start fs-8">TV et Electronique</a>
                        <a href="#" class="btn btn-sm text-secondary text-start fs-8">Electroménager</a>
                        <a href="#" class="btn btn-sm text-secondary text-start fs-8">Ordinateurs et accessoires</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="container py-2 d-flex flex-column flex-sm-row align-items-center justify-content-between">
        <p class="mb-0 fs-8">&copy; <?php echo e(\Carbon\Carbon::now()->format('Y')); ?>. Tous droits réservés</>
        <div class="d-flex align-items-center justify-content-start">
            <p class="mb-0 me-2 fs-8">Paiement sécurisé avec :</p>
            <img src="<?php echo e(asset('images/mastercard.jpg')); ?>" class="me-2" alt="">
            <img src="<?php echo e(asset('images/visa.jpg')); ?>" alt="">
        </div>
    </div>
</footer>

<!-- Footer Mobile Sticky -->
<footer class="bg-white px-2 py-3 d-sm-none container-fluid shadow-lg border-top" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;">
    <div class="row g-1 text-center">
        <div class="col-3">
            <a href="<?php echo e(route('accueil')); ?>" class="text-decoration-none">
                <div class="vstack gap-1 align-items-center p-1 mobile-nav-item">
                    <i class="fa-solid fa-home orange-color" style="font-size: 1.2rem;"></i>
                    <span class="fs-8 text-muted">Accueil</span>
                </div>
            </a>
        </div>
        <div class="col-3">
            <a href="#" class="text-decoration-none" data-bs-toggle="offcanvas" data-bs-target="#mobileCategoriesOffcanvas">
                <div class="vstack gap-1 align-items-center p-1 mobile-nav-item">
                    <i class="fa-solid fa-th-large orange-color" style="font-size: 1.2rem;"></i>
                    <span class="fs-8 text-muted">Catégories</span>
                </div>
            </a>
        </div>
        <div class="col-3">
            <a href="#" onclick="goToFavorites(event)" class="text-decoration-none">
                <div class="vstack gap-1 align-items-center p-1 mobile-nav-item">
                    <i class="fa-solid fa-heart orange-color" style="font-size: 1.2rem;"></i>
                    <span class="fs-8 text-muted">Favoris</span>
                </div>
            </a>
        </div>
        <div class="col-3">
            <a href="<?php echo e(route('product-cart')); ?>" class="text-decoration-none">
                <div class="vstack gap-1 align-items-center p-1 mobile-nav-item position-relative">
                    <i class="fa-solid fa-shopping-bag orange-color" style="font-size: 1.2rem;"></i>
                    <span class="fs-8 text-muted">Panier</span>
                    <!-- Badge pour le nombre d'articles -->
                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;" id="cartBadge">
                        0
                    </span>
                </div>
            </a>
        </div>
    </div>
</footer>

<!-- Offcanvas pour les catégories mobiles -->
<div class="offcanvas offcanvas-start z-index-9x" tabindex="-1" id="mobileCategoriesOffcanvas" aria-labelledby="mobileCategoriesOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="mobileCategoriesOffcanvasLabel">
            <i class="fa-solid fa-th-large orange-color me-2"></i>
            Catégories
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php if(isset($allCategories)): ?>
            <div class="list-group list-group-flush">
                <?php $__currentLoopData = $allCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="list-group-item p-0">
                    <!-- Catégorie principale -->
                    <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="d-flex align-items-center p-3 text-decoration-none category-main-link">
                                        <?php if($category->image && !empty($category->image)): ?>
                                        <img src="<?php echo e(str_starts_with($category->image, 'http') ? $category->image : (str_starts_with($category->image, 'images/') ? asset($category->image) : Storage::url($category->image))); ?>" alt="<?php echo e($category->name); ?>" style="width: 24px; height: 24px; object-fit: contain;" class="me-3">
                                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <span class="fw-bold text-dark"><?php echo e($category->name); ?></span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-muted"></i>
                    </a>
                    
                    <!-- Sous-catégories (collapsible) -->
                    <?php if($category->subcategories && $category->subcategories->count() > 0): ?>
                    <div class="collapse" id="subcategories<?php echo e($category->id); ?>">
                        <div class="px-3 pb-2">
                            <?php $__currentLoopData = $category->subcategories->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="d-flex align-items-center py-2 px-3 text-decoration-none subcategory-link">
                                            <?php if($subcategory->image && !empty($subcategory->image)): ?>
                                            <img src="<?php echo e(str_starts_with($subcategory->image, 'http') ? $subcategory->image : (str_starts_with($subcategory->image, 'images/') ? asset($subcategory->image) : Storage::url($subcategory->image))); ?>" alt="<?php echo e($subcategory->name); ?>" style="width: 16px; height: 16px; object-fit: contain;" class="me-2">
                                            <?php endif; ?>
                                <span class="text-muted"><?php echo e($subcategory->name); ?></span>
                            </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                            <?php if($category->subcategories->count() > 6): ?>
                            <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="d-flex align-items-center py-2 px-3 text-decoration-none">
                                <span class="text-primary fs-7">
                                    <i class="fa-solid fa-plus me-1"></i>
                                    Voir <?php echo e($category->subcategories->count() - 6); ?> autres...
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Bouton pour afficher/masquer les sous-catégories -->
                    <button class="btn btn-link w-100 text-start p-0 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#subcategories<?php echo e($category->id); ?>" aria-expanded="false" aria-controls="subcategories<?php echo e($category->id); ?>">
                        <div class="px-3 py-2 border-top">
                            <small class="text-muted">
                                <i class="fa-solid fa-chevron-down me-1" id="chevron<?php echo e($category->id); ?>"></i>
                                <?php echo e($category->subcategories->count()); ?> sous-catégories
                            </small>
                        </div>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        
        <!-- Section rapide -->
        <div class="border-top mt-3 pt-3">
            <h6 class="fw-bold px-3 mb-3">
                <i class="fa-solid fa-bolt orange-color me-2"></i>
                Accès rapide
            </h6>
            <div class="px-3">
                <a href="<?php echo e(route('boutique_officielle')); ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fa-solid fa-store me-2"></i>
                    Boutiques Officielles
                </a>
                <a href="<?php echo e(route('search_product')); ?>" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fa-solid fa-search me-2"></i>
                    Rechercher
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Espace pour éviter que le contenu soit caché par le footer mobile -->
<div class="d-sm-none" style="height: 80px;"></div>

<!-- Styles pour le footer mobile -->
<style>
    .mobile-nav-item {
        transition: all 0.2s ease;
        border-radius: 8px;
        cursor: pointer;
    }
    
    .mobile-nav-item:hover {
        background-color: rgba(255, 140, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .mobile-nav-item:active {
        transform: translateY(0);
        background-color: rgba(255, 140, 0, 0.2);
    }
    
    /* Animation pour le badge du panier */
    #cartBadge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.1); }
        100% { transform: translate(-50%, -50%) scale(1); }
    }
    
    /* Responsive pour très petits écrans */
    @media (max-width: 375px) {
        .mobile-nav-item i {
            font-size: 1rem !important;
        }
        
        .mobile-nav-item span {
            font-size: 0.7rem !important;
        }
        
        #cartBadge {
            font-size: 0.5rem !important;
        }
    }
    
    /* Amélioration de l'offcanvas */
    .offcanvas-start {
        width: 320px !important;
    }
    
    /* Styles pour les catégories principales */
    .category-main-link:hover {
        background-color: rgba(255, 140, 0, 0.1);
        transition: background-color 0.2s ease;
    }
    
    /* Styles pour les sous-catégories */
    .subcategory-link:hover {
        background-color: rgba(255, 140, 0, 0.05);
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .subcategory-link:hover span {
        color: var(--main-color) !important;
    }
    
    /* Animation des chevrons */
    .collapse.show + button .fa-chevron-down {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }
    
    .fa-chevron-down {
        transition: transform 0.3s ease;
    }
    
    /* Indentation des sous-catégories */
    .subcategory-link {
        margin-left: 1rem;
        border-left: 2px solid transparent;
        transition: border-color 0.2s ease;
    }
    
    .subcategory-link:hover {
        border-left-color: var(--main-color);
    }
    
    /* Styles pour les boutons d'accès rapide */
    .btn-outline-primary:hover,
    .btn-outline-secondary:hover {
        transform: translateY(-1px);
        transition: transform 0.2s ease;
    }
</style>

<!-- Script pour initialiser les dropdowns Bootstrap -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser tous les dropdowns Bootstrap
    const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownElements.forEach(function(dropdownElement) {
        new bootstrap.Dropdown(dropdownElement);
    });
});
</script>

<!-- MAIN JS -->
 <script src="<?php echo e(asset('js/main.js')); ?>"></script>
 <script src="<?php echo e(asset('js/carousel.js')); ?>"></script>
<script src="<?php echo e(asset('js/cart.js')); ?>"></script>
<script src="<?php echo e(asset('js/filters.js')); ?>"></script>
<script src="<?php echo e(asset('js/search-autocomplete.js')); ?>"></script>

 <script>
    document.addEventListener("DOMContentLoaded", () => {
        // Initialiser les dropdowns Bootstrap
        const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdownElements.forEach(function(dropdownElement) {
            new bootstrap.Dropdown(dropdownElement);
        });
        console.log('Dropdowns Bootstrap initialisés');
        
        // Initialiser les carousels
        document.querySelectorAll("[data-multi-carousel]").forEach(el => {
            const options = {
            slidesToShow: parseInt(el.dataset.slidesToShow || 4),
            slidesToScroll: parseInt(el.dataset.slidesToScroll || 1),
            gap: parseInt(el.dataset.gap || 10),
            autoplay: el.dataset.autoplay === "true",
            autoplaySpeed: parseInt(el.dataset.autoplaySpeed || 3000),
            pauseOnHover: el.dataset.pauseOnHover !== "false",
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: parseInt(el.dataset.slidesLg || el.dataset.slidesToShow) } },
                { breakpoint: 992,  settings: { slidesToShow: parseInt(el.dataset.slidesMd || el.dataset.slidesToShow) } },
                { breakpoint: 768,  settings: { slidesToShow: parseInt(el.dataset.slidesSm || el.dataset.slidesToShow) } },
                { breakpoint: 576,  settings: { slidesToShow: parseInt(el.dataset.slidesXs || el.dataset.slidesToShow) } }
            ]
            };
            new MultiCarousel(el, options);
        });

        // Gestion du footer mobile
        const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
        const cartBadge = document.getElementById('cartBadge');
        
        // Effet de feedback tactile pour les éléments de navigation mobile
        mobileNavItems.forEach(item => {
            item.addEventListener('touchstart', function() {
                this.style.transform = 'translateY(0)';
                this.style.backgroundColor = 'rgba(255, 140, 0, 0.2)';
            });
            
            item.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = '';
                    this.style.backgroundColor = '';
                }, 150);
            });
        });

        // Mise à jour du badge du panier (simulation)
        function updateCartBadge() {
            // Ici vous pourriez récupérer le nombre d'articles depuis votre système de panier
            const cartCount = 0; // Remplacer par la vraie logique
            if (cartCount > 0) {
                cartBadge.textContent = cartCount;
                cartBadge.style.display = 'block';
            } else {
                cartBadge.style.display = 'none';
            }
        }

        // Mise à jour initiale
        updateCartBadge();

        // Gestion du scroll pour masquer/afficher le footer mobile
        let lastScrollTop = 0;
        const footer = document.querySelector('footer[style*="position: fixed"]');
        
        if (footer) {
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Masquer le footer en scrollant vers le bas, l'afficher en scrollant vers le haut
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    footer.style.transform = 'translateY(100%)';
                } else {
                    footer.style.transform = 'translateY(0)';
                }
                
                lastScrollTop = scrollTop;
            });
        }

        // Animation d'entrée pour le footer mobile
        if (footer) {
            footer.style.transition = 'transform 0.3s ease';
        }

        // Gestion des collapsibles dans l'offcanvas
        const collapseElements = document.querySelectorAll('.collapse');
        collapseElements.forEach(collapse => {
            collapse.addEventListener('show.bs.collapse', function() {
                const targetId = this.id;
                const categoryId = targetId.replace('subcategories', '');
                const chevron = document.getElementById(`chevron${categoryId}`);
                if (chevron) {
                    chevron.style.transform = 'rotate(180deg)';
                }
            });
            
            collapse.addEventListener('hide.bs.collapse', function() {
                const targetId = this.id;
                const categoryId = targetId.replace('subcategories', '');
                const chevron = document.getElementById(`chevron${categoryId}`);
                if (chevron) {
                    chevron.style.transform = 'rotate(0deg)';
                }
            });
        });

        // Animation des liens de sous-catégories
        const subcategoryLinks = document.querySelectorAll('.subcategory-link');
        subcategoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Fermer l'offcanvas après le clic
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('mobileCategoriesOffcanvas'));
                if (offcanvas) {
                    setTimeout(() => {
                        offcanvas.hide();
                    }, 150);
                }
            });
        });
    });

    // Fonction pour aller vers les favoris (conservée pour d'autres usages)
    window.goToFavorites = function(event) {
        event.preventDefault();
        // Redirection directe vers le profil (l'authentification est gérée par le middleware)
        window.location.href = '/profil#favorites';
        return true;
    }

    // Fonction pour mettre à jour le texte du bouton vendeur
    function updateSellerButton() {
        const token = localStorage.getItem('auth_token');
        const sellerBtn = document.getElementById('sellerButton');
        
        if (!sellerBtn) return;
        
        if (token) {
            fetch('/api/check-seller-status', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.is_seller && data.has_store) {
                    sellerBtn.innerHTML = '<i class="bi bi-shop me-1"></i>Ma boutique';
                    sellerBtn.title = 'Accéder à ma boutique';
                } else {
                    sellerBtn.innerHTML = 'Vendez sur KAZARIA';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }
    }

    // Mettre à jour le bouton au chargement
    document.addEventListener('DOMContentLoaded', updateSellerButton);
</script>

<!-- Styles pour les notifications toast -->
<style>
.toast {
    min-width: 300px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.toast-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    font-weight: 600;
}

.toast-body {
    font-size: 0.9rem;
    line-height: 1.4;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
    border-left: 4px solid #198754;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border-left: 4px solid #dc3545;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border-left: 4px solid #ffc107;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
    border-left: 4px solid #0dcaf0;
}
</style>

<!-- Container pour les notifications toast -->
<div class="toast-container position-fixed top-0 end-0 p-3 z-index-9x" style="z-index: 9999;">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i id="toastIcon" class="bi me-2"></i>
            <strong id="toastTitle" class="me-auto"></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="toastBody" class="toast-body bg-light"></div>
    </div>
</div>

<script>
// Fonction globale pour afficher les notifications toast
window.showNotification = function(type, message) {
    const toastElement = document.getElementById('notificationToast');
    const toastIcon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastBody = document.getElementById('toastBody');
    
    if (!toastElement || !toastIcon || !toastTitle || !toastBody) {
        console.warn('Éléments toast non trouvés, utilisation de alert()');
        alert(message);
        return;
    }
    
    // Configuration selon le type
    const configs = {
        'success': {
            icon: 'bi-check-circle-fill',
            iconColor: 'text-success',
            title: 'Succès',
            bgClass: 'bg-success-subtle'
        },
        'error': {
            icon: 'bi-exclamation-circle-fill',
            iconColor: 'text-danger',
            title: 'Erreur',
            bgClass: 'bg-danger-subtle'
        },
        'warning': {
            icon: 'bi-exclamation-triangle-fill',
            iconColor: 'text-warning',
            title: 'Attention',
            bgClass: 'bg-warning-subtle'
        },
        'info': {
            icon: 'bi-info-circle-fill',
            iconColor: 'text-info',
            title: 'Information',
            bgClass: 'bg-info-subtle'
        }
    };
    
    const config = configs[type] || configs['info'];
    
    // Mettre à jour le contenu
    toastIcon.className = `bi ${config.icon} ${config.iconColor}`;
    toastTitle.textContent = config.title;
    toastBody.textContent = message;
    
    // Appliquer le style de fond
    toastElement.className = `toast ${config.bgClass}`;
    
    // Afficher le toast
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'error' ? 5000 : 3000
    });
    
    toast.show();
};

// Fallback pour les navigateurs plus anciens
if (typeof window.showNotification !== 'function') {
    window.showNotification = function(type, message) {
        alert(message);
    };
}
</script>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
</body>
</html><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/layouts/footer.blade.php ENDPATH**/ ?>