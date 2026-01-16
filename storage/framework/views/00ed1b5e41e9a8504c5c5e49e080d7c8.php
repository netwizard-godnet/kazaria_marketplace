<?php $__env->startSection('title', 'Aide & Documentation - KAZARIA Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Aide & Documentation</h4>
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
                <span>Aide</span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        Guide d'utilisation du dashboard administrateur
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3"><i class="fas fa-dashboard text-info me-2"></i> Dashboard</h5>
                            <p class="text-muted">
                                Le dashboard vous donne une vue d'ensemble de votre marketplace : statistiques des utilisateurs, produits, commandes, revenus, etc.
                                Utilisez les cartes statistiques pour suivre les performances de votre plateforme.
                            </p>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-shopping-cart text-success me-2"></i> Gestion des Commandes</h5>
                            <p class="text-muted">
                                Dans la section <strong>Commandes</strong>, vous pouvez :
                            </p>
                            <ul class="text-muted">
                                <li>Voir toutes les commandes passées sur la plateforme</li>
                                <li>Filtrer les commandes par statut, date, ou client</li>
                                <li>Voir les détails complets d'une commande</li>
                                <li>Gérer les statuts de commande</li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-box text-warning me-2"></i> Gestion des Produits</h5>
                            <p class="text-muted">
                                Dans la section <strong>Produits</strong>, vous pouvez :
                            </p>
                            <ul class="text-muted">
                                <li>Voir tous les produits créés par les vendeurs</li>
                                <li>Approuver ou refuser des produits</li>
                                <li>Modifier les informations d'un produit</li>
                                <li>Gérer les stocks et prix</li>
                                <li>Voir les statistiques de vente par produit</li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-store text-primary me-2"></i> Gestion des Boutiques</h5>
                            <p class="text-muted">
                                Dans la section <strong>Boutiques</strong>, vous pouvez :
                            </p>
                            <ul class="text-muted">
                                <li>Voir toutes les boutiques créées par les vendeurs</li>
                                <li>Approuver ou suspendre des boutiques</li>
                                <li>Modifier les informations d'une boutique</li>
                                <li>Gérer les paramètres de chaque boutique</li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-users text-info me-2"></i> Gestion des Utilisateurs</h5>
                            <p class="text-muted">
                                Dans la section <strong>Utilisateurs</strong>, vous pouvez :
                            </p>
                            <ul class="text-muted">
                                <li>Voir tous les utilisateurs (clients et vendeurs)</li>
                                <li>Filtrer par type d'utilisateur</li>
                                <li>Modifier les informations utilisateur</li>
                                <li>Activer ou désactiver des comptes</li>
                                <li>Gérer les rôles et permissions</li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-tags text-danger me-2"></i> Gestion des Catégories</h5>
                            <p class="text-muted">
                                Dans la section <strong>Catégories</strong>, vous pouvez :
                            </p>
                            <ul class="text-muted">
                                <li>Créer et modifier des catégories principales</li>
                                <li>Gérer les sous-catégories</li>
                                <li>Organiser la hiérarchie des catégories</li>
                                <li>Activer ou désactiver des catégories</li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-cog text-secondary me-2"></i> Paramètres</h5>
                            <p class="text-muted">
                                Dans la section <strong>Paramètres</strong>, vous pouvez :
                            </p>
                            <ul class="text-muted">
                                <li>Configurer les paramètres généraux de la plateforme</li>
                                <li>Gérer les bannières publicitaires</li>
                                <li>Modifier le carousel d'accueil</li>
                                <li>Configurer les options de paiement et livraison</li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-bell text-warning me-2"></i> Notifications & Messages</h5>
                            <p class="text-muted">
                                Vous recevez des notifications pour :
                            </p>
                            <ul class="text-muted">
                                <li>Nouvelles commandes</li>
                                <li>Messages des utilisateurs</li>
                                <li>Demandes d'approbation</li>
                                <li>Alertes système importantes</li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-question text-primary me-2"></i> Besoin d'aide ?</h5>
                            <p class="text-muted">
                                Si vous rencontrez un problème ou avez une question :
                            </p>
                            <ul class="text-muted">
                                <li>Consultez la <a href="<?php echo e(route('admin.documentation')); ?>" class="text-primary">documentation complète</a></li>
                                <li>Vérifiez le <a href="<?php echo e(route('admin.changelog')); ?>" class="text-primary">changelog</a> pour les dernières mises à jour</li>
                                <li>Contactez le support technique</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/help.blade.php ENDPATH**/ ?>