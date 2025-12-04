<?php
use Illuminate\Support\Facades\Storage;
use App\Models\Banner;
use Illuminate\Support\Facades\Auth;

// Charger l'utilisateur avec ses relations si authentifié
// Cela garantit que les données sont disponibles même si le View Composer n'a pas fonctionné
$headerUser = null;
if (auth()->check()) {
    $headerUser = $currentUser ?? Auth::user();
    if ($headerUser) {
        $headerUser->loadMissing('store');
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="user-logged-in" content="<?php echo e(auth()->check() ? 'true' : 'false'); ?>">
        
        <?php if (isset($component)) { $__componentOriginal42da61123f891e63201d7be28f403427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42da61123f891e63201d7be28f403427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo','data' => ['title' => $seoTitle ?? ($settings['site_name'] ?? 'KAZARIA') . ' - Votre marketplace en ligne en Côte d\'Ivoire','description' => $seoDescription ?? ($settings['site_description'] ?? 'Découvrez une large gamme de produits électroniques, électroménagers et accessoires sur KAZARIA. Livraison gratuite, paiement sécurisé et satisfaction garantie.'),'keywords' => $seoKeywords ?? ($settings['site_keywords'] ?? 'e-commerce, marketplace, Côte d\'Ivoire, Abidjan, téléphones, électronique, électroménager, ordinateurs, livraison gratuite'),'image' => $seoImage ?? null,'url' => $seoUrl ?? null,'type' => $seoType ?? 'website','canonical' => $seoCanonical ?? null,'robots' => $seoRobots ?? 'index,follow','jsonLd' => $seoJsonLd ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoTitle ?? ($settings['site_name'] ?? 'KAZARIA') . ' - Votre marketplace en ligne en Côte d\'Ivoire'),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoDescription ?? ($settings['site_description'] ?? 'Découvrez une large gamme de produits électroniques, électroménagers et accessoires sur KAZARIA. Livraison gratuite, paiement sécurisé et satisfaction garantie.')),'keywords' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoKeywords ?? ($settings['site_keywords'] ?? 'e-commerce, marketplace, Côte d\'Ivoire, Abidjan, téléphones, électronique, électroménager, ordinateurs, livraison gratuite')),'image' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoImage ?? null),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoUrl ?? null),'type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoType ?? 'website'),'canonical' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoCanonical ?? null),'robots' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoRobots ?? 'index,follow'),'jsonLd' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seoJsonLd ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $attributes = $__attributesOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__attributesOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $component = $__componentOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__componentOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <!-- Fontawesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-(your integrity hash)" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <!-- SLICK -->
        <!-- <link rel="stylesheet" href="<?php echo e(asset('slick/slick.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('slick/slick-theme.css')); ?>"> -->
        <!-- CUSTOM CSS -->
        <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
        <!-- <link rel="stylesheet" href="<?php echo e(asset('css/profil.css')); ?>"> -->
        <link rel="stylesheet" href="<?php echo e(asset('css/carousel.css')); ?>">
        <?php echo $__env->yieldPushContent('styles'); ?>
        
        <!-- Styles pour l'autocomplétion -->
        <style>
            .suggestion-item {
                transition: background-color 0.2s ease;
            }
            
            .suggestion-item:hover {
                background-color: #f8f9fa !important;
            }
            
            .suggestion-item:last-child {
                border-bottom: none !important;
            }
            
            #searchSuggestions {
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border: 1px solid #dee2e6;
            }
            
            #searchSuggestions mark {
                background-color: #fff3cd;
                padding: 0;
                border-radius: 2px;
            }
            
            .cursor-pointer {
                cursor: pointer;
            }
            
            #clearSearch {
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                z-index: 10;
            }
        </style>
        <!-- FONTS -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    </head>

    <body>
        <?php echo $__env->renderWhen(config('kazar_ai.enabled'), 'components.kazar-ai', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
        <?php
            $headerGifBanner = Banner::getHeaderGif();
        ?>
        <div class="z-index-9x" style="position: sticky; top: 0;">
        <!-- Header Banner -->
        <div class="container-fluid p-0 <?php echo e($headerGifBanner->visibility_classes ?? ''); ?>">
            <?php if($headerGifBanner && $headerGifBanner->image_url): ?>
                <?php if($headerGifBanner->link_url): ?>
                    <a href="<?php echo e($headerGifBanner->link_url); ?>" target="_blank" rel="noopener" class="d-block">
                        <img src="<?php echo e($headerGifBanner->image_url); ?>" alt="Banner KAZARIA" class="w-100" style="max-height: 60px; object-fit: cover; display: block;">
                    </a>
                <?php else: ?>
                    <img src="<?php echo e($headerGifBanner->image_url); ?>" alt="Banner KAZARIA" class="w-100" style="max-height: 60px; object-fit: cover; display: block;">
                <?php endif; ?>
            <?php else: ?>
            <img src="<?php echo e(asset('images/banner.gif')); ?>" alt="Banner KAZARIA" class="w-100" style="max-height: 60px; object-fit: cover; display: block;">
            <?php endif; ?>
        </div>
        <header class="z-index-9x shadow d-none d-sm-block">
            <div class="container-fluid blue-bg py-0 position-relative">
                <nav class="navbar navbar-expand-lg py-0">
                    <div class="container-fluid py-0">
                        <a class="navbar-brand fw-bolder text-white fs-2" href="<?php echo e(route('accueil')); ?>">
                            <img src="<?php echo e(asset('storage/' . ($settings['site_logo'] ?? 'logo.png'))); ?>" class="logo-size-header" alt="<?php echo e($settings['site_name'] ?? 'KAZARIA'); ?>">
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <form class="d-flex ms-auto position-relative" action="<?php echo e(route('search_product')); ?>" method="GET" role="search" id="searchForm">
                                <div class="bg-light d-flex align-items-center justify-content-between rounded-2 me-2 position-relative">
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Toutes les catégories
                                        </button>
                                        <ul class="bg-light dropdown-menu">
                                            <li><a class="dropdown-item fs-8" href="<?php echo e(route('search_product')); ?>">Toutes les catégories</a></li>
                                            <?php if(isset($allCategories)): ?>
                                                <?php $__currentLoopData = $allCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center fs-8" href="<?php echo e(route('categorie', $category->slug)); ?>">
                                                        <?php if($category->image && !empty($category->image)): ?>
                                                        <img src="<?php echo e(str_starts_with($category->image, 'http') ? $category->image : (str_starts_with($category->image, 'images/') ? asset($category->image) : Storage::url($category->image))); ?>" style="width: 15px; height: 15px; object-fit: contain;" class="me-2">
                                                        <?php endif; ?>
                                                        <?php echo e($category->name); ?>

                                                    </a>
                                                </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <div class="position-relative">
                                        <input class="form-control py-2 px-4 me-2 border-0 rounded-0 width-400 fs-8" type="search" name="q" placeholder="Je veux acheter..." aria-label="Search" id="searchInput" autocomplete="off"/>
                                        <div id="searchSuggestions" class="position-absolute w-100 bg-white border rounded shadow-lg d-none fs-8" style="top: 100%; left: 0; z-index: 1000; max-height: 300px; overflow-y: auto;">
                                            <!-- Les suggestions apparaîtront ici -->
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm text-muted position-absolute end-0 me-2 fs-8" id="clearSearch" style="display: none;">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                                <button class="btn orange-bg rounded-1 text-white text-uppercase fw-bolder fs-8" type="submit">
                                Rechercher
                                </button>
                            </form>
                            <ul class="navbar-nav">
                                <li class="nav-item d-flex align-items-center justify-content-center">
                                    <a class="nav-link position-relative" aria-current="page" href="#" onclick="goToFavorites(event)">
                                        <i class="fa-solid fa-heart text-white fa-2x"></i>
                                        <span class="position-absolute bottom-0 end-0 orange-bg px-2 rounded-2 fw-lighter fs-8 text-white favorites-count d-none">0</span>
                                    </a>
                                </li>
                                <li class="nav-item d-flex align-items-center justify-content-center">
                                    <a class="nav-link position-relative" aria-current="page" href="<?php echo e(route('product-cart')); ?>">
                                        <i class="fa-solid fa-shopping-cart text-white fa-2x"></i>
                                        <span class="position-absolute bottom-0 end-0 orange-bg px-2 rounded-2 fw-lighter fs-8 text-white cart-count">0</span>
                                    </a>
                                </li>
                                <li id="auth-section" class="nav-item d-flex align-items-center justify-content-center">
                                    <?php if($headerUser): ?>
                                        <div class="dropdown">
                                            <button class="nav-link d-flex align-items-center btn btn-link text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown">
                                                <i class="fa-solid fa-user text-white fa-2x"></i>
                                                <div class="vstack text-white ms-1">
                                                    <span class="fs-8 fw-bold fw-lighter"><?php echo e(trim(($headerUser->prenoms ?? '') . ' ' . ($headerUser->nom ?? '')) ?: 'Utilisateur'); ?></span>
                                                    <span class="fs-8 fw-lighter">Connecté(e)<i class="fa-solid fa-chevron-down text-white"></i></span>
                                                </div>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item fs-8" href="<?php echo e(route('profil')); ?>"><i class="fa-solid fa-user me-2 orange-color"></i>Mon profil</a></li>
                                                <?php if($headerUser->is_seller): ?>
                                                    <?php if($headerUser->store): ?>
                                                        <li><a class="dropdown-item fs-8" href="<?php echo e(route('store.dashboard')); ?>"><i class="fa-solid fa-store me-2 orange-color"></i>Ma boutique</a></li>
                                                    <?php else: ?>
                                                        <li><a class="dropdown-item fs-8" href="<?php echo e(route('store.create')); ?>"><i class="fa-solid fa-plus me-2 orange-color"></i>Créer ma boutique</a></li>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li class="">
                                                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="dropdown-item bg-danger text-white fs-8"><i class="fa-solid fa-sign-out-alt me-2"></i>Déconnexion</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <a class="nav-link d-flex align-items-center" aria-current="page" href="/authentification">
                                            <i class="fa-solid fa-user text-white fa-2x"></i>
                                            <div class="vstack text-white ms-1">
                                                <span class="fs-8 fw-lighter">Connexion</span>
                                                <span class="fs-8 fw-lighter">Inscription</span>
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                <hr class="text-white my-1">
                <div class="row gx-2 py-0">
                    <!--  -->
                    <div class="col-md-8 hstack gap-1">
                        <div class="d-flex align-items-center justify-content-start">
                            <a class="btn btn-sm orange-bg text-white fs-8 text-nowrap" href="<?php echo e(route('boutique_officielle')); ?>">
                            Boutiques Officielles <i class="fa-solid fa-certificate"></i>
                            </a>
                        </div>
                        <?php if(isset($allCategories)): ?>
                            <?php $__currentLoopData = $allCategories->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="header-menu d-flex align-items-center justify-content-start">
                                <a class="btn btn-sm text-white fs-8 text-nowrap" href="<?php echo e(route('categorie', $menuCategory->slug)); ?>">
                                    <?php if($menuCategory->image && !empty($menuCategory->image)): ?>
                                    <img src="<?php echo e(str_starts_with($menuCategory->image, 'http') ? $menuCategory->image : (str_starts_with($menuCategory->image, 'images/') ? asset($menuCategory->image) : Storage::url($menuCategory->image))); ?>" style="width: 20px; height: 20px; object-fit: contain;" class="me-1">
                                    <?php endif; ?>
                                    <?php echo e($menuCategory->name); ?> <i class="fa-solid fas fa-chevron-down fs-8"></i>
                                </a>
                                <div class="w-100 bg-light py-2 position-absolute top-100 start-0 z-index-9x d-none container-fluid">
                                <div class="row g-1">
                                        <?php if($menuCategory->subcategories->count() > 0): ?>
                                        <?php $__currentLoopData = $menuCategory->subcategories->chunk(ceil($menuCategory->subcategories->count() / 4)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-2">
                                        <div class="list-group">
                                                <a href="<?php echo e(route('categorie', $menuCategory->slug)); ?>" class="list-group-item list-group-item-action orange-bg text-white rounded-0 d-none fs-8">
                                                    <?php if($menuCategory->image && !empty($menuCategory->image)): ?>
                                                    <img src="<?php echo e(str_starts_with($menuCategory->image, 'http') ? $menuCategory->image : (str_starts_with($menuCategory->image, 'images/') ? asset($menuCategory->image) : Storage::url($menuCategory->image))); ?>" alt="<?php echo e($menuCategory->name); ?>" style="width: 16px; height: 16px; object-fit: contain;" class="me-2">
                                                    <?php endif; ?>
                                                    <?php echo e($menuCategory->name); ?>

                                                </a>
                                                <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <a href="<?php echo e(route('categorie', $menuCategory->slug)); ?>?subcategory=<?php echo e($subcat->slug); ?>" class="list-group-item list-group-item-action fs-8">
                                                    <?php if($subcat->image && !empty($subcat->image)): ?>
                                                    <img src="<?php echo e(str_starts_with($subcat->image, 'http') ? $subcat->image : (str_starts_with($subcat->image, 'images/') ? asset($subcat->image) : Storage::url($subcat->image))); ?>" style="width: 16px; height: 16px; object-fit: contain;" class="me-2">
                                                    <?php endif; ?>
                                                    <?php echo e($subcat->name); ?>

                                                </a>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                        <div class="col-md-12">
                                            <div class="list-group">
                                                <a href="<?php echo e(route('categorie', $menuCategory->slug)); ?>" class="list-group-item list-group-item-action orange-bg text-white rounded-0">
                                                    <?php if($menuCategory->image && !empty($menuCategory->image)): ?>
                                                    <img src="<?php echo e(str_starts_with($menuCategory->image, 'http') ? $menuCategory->image : (str_starts_with($menuCategory->image, 'images/') ? asset($menuCategory->image) : Storage::url($menuCategory->image))); ?>" alt="<?php echo e($menuCategory->name); ?>" style="width: 16px; height: 16px; object-fit: contain;" class="me-2">
                                                    <?php endif; ?>
                                                    <?php echo e($menuCategory->name); ?>

                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-1">
                        
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-start">
                            <?php if($headerUser && $headerUser->is_seller): ?>
                                <?php if($headerUser->store): ?>
                                    <a href="<?php echo e(route('store.dashboard')); ?>" class="btn btn-sm fs-8 text-white rounded-0 border-end pe-3" style="border-right-color:var(--main-color)!important;">
                                        <i class="fa-solid fa-store me-1"></i>Ma boutique
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('store.create')); ?>" class="btn btn-sm fs-8 text-white rounded-0 border-end pe-3" style="border-right-color:var(--main-color)!important;">
                                        <i class="fa-solid fa-plus me-1"></i>Créer ma boutique
                                    </a>
                                <?php endif; ?>
                            <?php elseif($headerUser): ?>
                                <a href="<?php echo e(route('store.create')); ?>" class="btn btn-sm fs-8 text-white rounded-0 border-end pe-3" style="border-right-color:var(--main-color)!important;">
                                    <i class="fa-solid fa-store me-1"></i>Vendez sur KAZARIA
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('login')); ?>" class="btn btn-sm fs-8 text-white rounded-0 border-end pe-3" style="border-right-color:var(--main-color)!important;">
                                    <i class="fa-solid fa-store me-1"></i>Vendez sur KAZARIA
                                </a>
                            <?php endif; ?>
                            <?php if($headerUser): ?>
                                <a href="<?php echo e(route('profil')); ?>#orders" class="btn btn-sm fs-8 text-white rounded-0 ps-3">
                                    <i class="fa-solid fa-box me-1"></i>Suivre ma commande
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('login')); ?>" class="btn btn-sm fs-8 text-white rounded-0 ps-3">
                                    <i class="fa-solid fa-box me-1"></i>Suivre ma commande
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!--  -->
                </div>
            </div>
        </header>

        <!-- Mobile Header -->
        <header class="z-index-9x shadow d-sm-none" style="position: sticky; top: 0;">
            <div class="container-fluid blue-bg py-2 position-relative">
                <nav class="py-0">
                    <div class="vstack gap-2">
                        <div class="w-100 d-flex align-items-center justify-content-between">
                            <a class="" href="<?php echo e(route('accueil')); ?>">
                                <img src="<?php echo e(asset('storage/' . ($settings['site_logo'] ?? 'logo.png'))); ?>" class="logo-size-header" alt="<?php echo e($settings['site_name'] ?? 'KAZARIA'); ?>">
                            </a>
                            <ul class="d-flex align-items-center justify-content-evenly m-0">
                                <li class="nav-item px-1 d-flex align-items-center justify-content-center me-2">
                                    <a class="nav-link position-relative" aria-current="page" href="<?php echo e(route('product-cart')); ?>">
                                        <i class="fa-solid fas fa-shopping-cart text-white fa-2x"></i>
                                        <span class="position-absolute bottom-0 end-0 bg-danger px-2 rounded-2 fw-lighter fs-8 text-white cart-count">0</span>
                                    </a>
                                </li>
                                <?php if($headerUser): ?>
                                    <div class="dropdown">
                                        <button class="nav-link d-flex align-items-center btn btn-link text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown">
                                            <i class="fa-solid fa-user-check text-white fa-2x"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="<?php echo e(route('profil')); ?>"><i class="fa-solid fa-user me-2 orange-color"></i>Mon profil</a></li>
                                            <?php if($headerUser->is_seller): ?>
                                                <?php if($headerUser->store): ?>
                                                    <li><a class="dropdown-item" href="<?php echo e(route('store.dashboard')); ?>"><i class="fa-solid fa-store me-2 orange-color"></i>Ma boutique</a></li>
                                                <?php else: ?>
                                                    <li><a class="dropdown-item" href="<?php echo e(route('store.create')); ?>"><i class="fa-solid fa-plus me-2 orange-color"></i>Créer ma boutique</a></li>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li class="">
                                                <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="dropdown-item bg-danger text-white"><i class="fa-solid fa-sign-out-alt me-2"></i>Déconnexion</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                                    <a class="nav-link d-flex align-items-center" aria-current="page" href="/authentification">
                                        <i class="fa-solid fa-user text-white fa-2x"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="">
                            <form class="w-100 d-flex justify-content-center position-relative" action="<?php echo e(route('search_product')); ?>" method="GET" role="search" id="mobileSearchForm">
                                <div class="w-100 bg-light d-flex align-items-center justify-content-between rounded-2 me-2 position-relative">
                                    <div class="w-100 position-relative">
                                        <input class="form-control px-4 me-2 border-0 width-400" type="search" name="q" placeholder="Je veux acheter..." aria-label="Search" id="mobileSearchInput" autocomplete="off"/>
                                        <div id="mobileSearchSuggestions" class="position-absolute w-100 bg-white border rounded shadow-lg d-none" style="top: 100%; left: 0; z-index: 1000; max-height: 300px; overflow-y: auto;">
                                            <!-- Les suggestions apparaîtront ici -->
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm text-muted position-absolute end-0 me-2" id="mobileClearSearch" style="display: none;">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                                <button class="btn orange-bg text-white text-uppercase fw-bolder" type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <!-- Header end -->
        </div><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/layouts/header.blade.php ENDPATH**/ ?>