<?php $__env->startSection('title', 'Changelog - KAZARIA Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Changelog</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Changelog</span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-history text-primary me-2"></i>
                        Historique des mises à jour
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="timeline">
                                <!-- Version récente -->
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content ps-4">
                                        <h5 class="text-primary mb-2">
                                            <i class="fas fa-star me-2"></i>
                                            Version actuelle - Mises à jour récentes
                                        </h5>
                                        <div class="text-muted">
                                            <h6 class="mt-3 mb-2">Fonctionnalités ajoutées :</h6>
                                            <ul>
                                                <li>Système de pop-up de consentement cookies</li>
                                                <li>Rappel de panier après inactivité</li>
                                                <li>Amélioration de l'affichage mobile du header</li>
                                                <li>Gestion des commandes multi-boutiques avec calculs proportionnels</li>
                                                <li>Filtres de commandes par date et statut dans le profil utilisateur</li>
                                                <li>Système de suivi de commandes avec barre de progression</li>
                                            </ul>
                                            
                                            <h6 class="mt-3 mb-2">Corrections :</h6>
                                            <ul>
                                                <li>Correction de l'affichage des boutons d'action dans le dashboard vendeur</li>
                                                <li>Correction du calcul des totaux pour les commandes multi-boutiques</li>
                                                <li>Amélioration de la gestion des photos de profil</li>
                                                <li>Correction de l'affichage du panier sur mobile</li>
                                                <li>Optimisation des z-index pour les pop-ups sur mobile</li>
                                            </ul>
                                            
                                            <h6 class="mt-3 mb-2">Améliorations techniques :</h6>
                                            <ul>
                                                <li>Séparation entre authentification session (web) et API (token)</li>
                                                <li>Amélioration de la gestion des caches Laravel</li>
                                                <li>Optimisation des requêtes SQL</li>
                                                <li>Amélioration de la sécurité CSRF</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Version précédente -->
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content ps-4">
                                        <h5 class="text-success mb-2">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Version stable précédente
                                        </h5>
                                        <div class="text-muted">
                                            <p>Version stable avec toutes les fonctionnalités de base :</p>
                                            <ul>
                                                <li>Dashboard administrateur complet</li>
                                                <li>Gestion des produits, catégories et sous-catégories</li>
                                                <li>Système de commandes</li>
                                                <li>Gestion des boutiques</li>
                                                <li>Authentification et gestion des utilisateurs</li>
                                                <li>Système de paiement</li>
                                                <li>SEO et référencement</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Notes de développement -->
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content ps-4">
                                        <h5 class="text-info mb-2">
                                            <i class="fas fa-code me-2"></i>
                                            Développement continu
                                        </h5>
                                        <div class="text-muted">
                                            <p>Le système est en développement continu. Les mises à jour incluent :</p>
                                            <ul>
                                                <li>Nouvelles fonctionnalités basées sur les retours utilisateurs</li>
                                                <li>Corrections de bugs régulières</li>
                                                <li>Amélioration des performances</li>
                                                <li>Sécurité et optimisations</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 2rem;
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 0.4rem;
        top: 1.5rem;
        width: 2px;
        height: calc(100% - 1rem);
        background: #dee2e6;
    }
    
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0.25rem;
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 2px currentColor;
        z-index: 1;
    }
    
    .timeline-content {
        margin-left: 1.5rem;
    }
    
    .timeline-content h5 {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .timeline-content ul {
        margin-top: 0.5rem;
        padding-left: 1.5rem;
    }
    
    .timeline-content li {
        margin-bottom: 0.25rem;
    }
</style>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/changelog.blade.php ENDPATH**/ ?>