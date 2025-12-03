@extends('admin.layouts.app')

@section('title', 'Documentation - KAZARIA Admin')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Documentation</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Documentation</span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-book text-primary me-2"></i>
                        Documentation complète du système KAZARIA
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Cette documentation couvre toutes les fonctionnalités du système d'administration KAZARIA.
                            </div>
                            
                            <div id="documentation-content">
                                <!-- Section 1: Architecture -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-sitemap me-2"></i>
                                        1. Architecture du système
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">1.1 Structure de la base de données</h5>
                                        <p class="text-muted">
                                            Le système utilise une base de données relationnelle avec les tables principales :
                                        </p>
                                        <ul class="text-muted">
                                            <li><strong>users</strong> : Gestion des utilisateurs (clients et vendeurs)</li>
                                            <li><strong>stores</strong> : Boutiques créées par les vendeurs</li>
                                            <li><strong>products</strong> : Produits disponibles sur la plateforme</li>
                                            <li><strong>orders</strong> : Commandes des clients</li>
                                            <li><strong>order_items</strong> : Articles des commandes</li>
                                            <li><strong>categories</strong> : Catégories principales</li>
                                            <li><strong>subcategories</strong> : Sous-catégories</li>
                                            <li><strong>payments</strong> : Transactions de paiement</li>
                                            <li><strong>reviews</strong> : Avis clients</li>
                                        </ul>
                                        
                                        <h5 class="mt-3 mb-2">1.2 Authentification</h5>
                                        <p class="text-muted">
                                            Le système utilise l'authentification de session Laravel pour les utilisateurs web et Sanctum pour l'API.
                                            Les administrateurs doivent avoir le champ <code>is_admin = true</code> ou avoir un rôle admin actif.
                                        </p>
                                    </div>
                                </section>
                                
                                <!-- Section 2: Gestion des commandes -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-shopping-cart text-success me-2"></i>
                                        2. Gestion des commandes
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">2.1 Statuts de commande</h5>
                                        <p class="text-muted">Les statuts disponibles sont :</p>
                                        <ul class="text-muted">
                                            <li><span class="badge bg-warning">pending</span> - Commande en attente</li>
                                            <li><span class="badge bg-info">processing</span> - Commande en cours de traitement</li>
                                            <li><span class="badge bg-success">delivered</span> - Commande livrée</li>
                                            <li><span class="badge bg-danger">cancelled</span> - Commande annulée</li>
                                        </ul>
                                        
                                        <h5 class="mt-3 mb-2">2.2 Numérotation des commandes</h5>
                                        <p class="text-muted">
                                            Chaque commande reçoit un numéro unique au format : <code>KAZ-YYYYMMDD-XXXXXX</code>
                                            où YYYYMMDD est la date et XXXXXX est un identifiant unique.
                                        </p>
                                        
                                        <h5 class="mt-3 mb-2">2.3 Commandes multi-boutiques</h5>
                                        <p class="text-muted">
                                            Une commande peut contenir des produits de plusieurs boutiques. Chaque vendeur ne voit que les articles de sa boutique.
                                            Les totaux sont calculés proportionnellement pour chaque vendeur.
                                        </p>
                                    </div>
                                </section>
                                
                                <!-- Section 3: Gestion des produits -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-box text-warning me-2"></i>
                                        3. Gestion des produits
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">3.1 Cycle de vie d'un produit</h5>
                                        <ol class="text-muted">
                                            <li>Le vendeur crée un produit via son dashboard</li>
                                            <li>Le produit est en statut "en attente" (pending)</li>
                                            <li>L'administrateur peut approuver le produit</li>
                                            <li>Le produit devient visible sur la plateforme</li>
                                            <li>L'admin peut modifier ou supprimer un produit à tout moment</li>
                                        </ol>
                                        
                                        <h5 class="mt-3 mb-2">3.2 Attributs et variations</h5>
                                        <p class="text-muted">
                                            Les produits peuvent avoir des attributs (taille, couleur, etc.) avec des valeurs spécifiques.
                                            Chaque combinaison d'attributs peut avoir son propre stock et prix.
                                        </p>
                                        
                                        <h5 class="mt-3 mb-2">3.3 Gestion des stocks</h5>
                                        <p class="text-muted">
                                            Le système suit automatiquement les stocks. Lorsqu'une commande est passée, le stock est déduit.
                                            Les alertes de stock faible peuvent être configurées.
                                        </p>
                                    </div>
                                </section>
                                
                                <!-- Section 4: Gestion des boutiques -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-store text-primary me-2"></i>
                                        4. Gestion des boutiques
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">4.1 Création d'une boutique</h5>
                                        <p class="text-muted">
                                            Les vendeurs peuvent créer une boutique via leur dashboard. L'administrateur doit approuver la boutique
                                            avant qu'elle ne soit visible sur la plateforme.
                                        </p>
                                        
                                        <h5 class="mt-3 mb-2">4.2 Paramètres de boutique</h5>
                                        <ul class="text-muted">
                                            <li>Logo et bannière</li>
                                            <li>Description et informations de contact</li>
                                            <li>Politiques de livraison et retours</li>
                                            <li>Statut (actif/suspendu)</li>
                                        </ul>
                                    </div>
                                </section>
                                
                                <!-- Section 5: Gestion des utilisateurs -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-users text-info me-2"></i>
                                        5. Gestion des utilisateurs
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">5.1 Types d'utilisateurs</h5>
                                        <ul class="text-muted">
                                            <li><strong>Clients</strong> : Utilisateurs qui achètent des produits</li>
                                            <li><strong>Vendeurs</strong> : Utilisateurs qui vendent des produits via leur boutique</li>
                                            <li><strong>Administrateurs</strong> : Accès complet au dashboard admin</li>
                                        </ul>
                                        
                                        <h5 class="mt-3 mb-2">5.2 Rôles et permissions</h5>
                                        <p class="text-muted">
                                            Le système supporte un système de rôles. Chaque rôle peut avoir des permissions spécifiques.
                                            Un utilisateur peut avoir le flag <code>is_admin = true</code> ou un rôle admin actif.
                                        </p>
                                    </div>
                                </section>
                                
                                <!-- Section 6: SEO et référencement -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-search text-success me-2"></i>
                                        6. SEO et référencement
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">6.1 Métadonnées SEO</h5>
                                        <p class="text-muted">
                                            Chaque page peut avoir des métadonnées SEO personnalisées : titre, description, mots-clés.
                                            Le système génère automatiquement des sitemaps et des robots.txt.
                                        </p>
                                        
                                        <h5 class="mt-3 mb-2">6.2 URLs optimisées</h5>
                                        <p class="text-muted">
                                            Les URLs utilisent des slugs pour une meilleure indexation :
                                            <code>/produit/nom-du-produit</code>,
                                            <code>/categorie/nom-categorie</code>
                                        </p>
                                    </div>
                                </section>
                                
                                <!-- Section 7: Paiements -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-credit-card text-danger me-2"></i>
                                        7. Système de paiement
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">7.1 Méthodes de paiement</h5>
                                        <p class="text-muted">
                                            Le système supporte plusieurs méthodes de paiement. Chaque transaction est enregistrée
                                            dans la table <code>payments</code> avec son statut.
                                        </p>
                                        
                                        <h5 class="mt-3 mb-2">7.2 Statuts de paiement</h5>
                                        <ul class="text-muted">
                                            <li><span class="badge bg-warning">pending</span> - Paiement en attente</li>
                                            <li><span class="badge bg-success">completed</span> - Paiement complété</li>
                                            <li><span class="badge bg-danger">failed</span> - Paiement échoué</li>
                                        </ul>
                                    </div>
                                </section>
                                
                                <!-- Section 8: Notifications -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-bell text-warning me-2"></i>
                                        8. Système de notifications
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">8.1 Types de notifications</h5>
                                        <ul class="text-muted">
                                            <li>Nouvelles commandes</li>
                                            <li>Messages des clients</li>
                                            <li>Demandes d'approbation</li>
                                            <li>Alertes système</li>
                                        </ul>
                                        
                                        <h5 class="mt-3 mb-2">8.2 Notification en temps réel</h5>
                                        <p class="text-muted">
                                            Les notifications sont affichées dans le header admin et peuvent être marquées comme lues.
                                        </p>
                                    </div>
                                </section>
                                
                                <!-- Section 9: Rapports -->
                                <section class="mb-5">
                                    <h3 class="text-primary mb-3">
                                        <i class="fas fa-chart-bar text-info me-2"></i>
                                        9. Rapports et statistiques
                                    </h3>
                                    <div class="ps-3">
                                        <h5 class="mt-3 mb-2">9.1 Rapports disponibles</h5>
                                        <ul class="text-muted">
                                            <li>Rapport de ventes</li>
                                            <li>Rapport de produits</li>
                                            <li>Rapport d'utilisateurs</li>
                                            <li>Statistiques générales sur le dashboard</li>
                                        </ul>
                                        
                                        <h5 class="mt-3 mb-2">9.2 Export de données</h5>
                                        <p class="text-muted">
                                            Les rapports peuvent être exportés en différents formats pour analyse externe.
                                        </p>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #documentation-content section {
        border-left: 3px solid #e9ecef;
        padding-left: 1rem;
        margin-left: 0.5rem;
    }
    
    #documentation-content h5 {
        color: #495057;
        font-weight: 600;
    }
    
    #documentation-content code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
</style>
@endsection

