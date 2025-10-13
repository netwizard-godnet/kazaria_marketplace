@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- Hero Section -->
        <section class="bg-light py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <i class="bi bi-shield-check orange-color" style="font-size: 50px;"></i>
                        <h3 class="fw-bold mt-3">Politique de Confidentialité</h3>
                        <p class="fs-8 lead text-muted">Dernière mise à jour : {{ date('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contenu -->
        <section class="container py-5">
            <div class="row">
                <!-- Sommaire -->
                <div class="col-lg-3 mb-4">
                    <div class="position-sticky" style="top: 100px;">
                        <div class="card">
                            <div class="card-header orange-bg text-white">
                                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Sommaire</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="#introduction" class="list-group-item list-group-item-action">Introduction</a>
                                <a href="#donnees-collectees" class="list-group-item list-group-item-action">Données collectées</a>
                                <a href="#utilisation" class="list-group-item list-group-item-action">Utilisation des données</a>
                                <a href="#partage" class="list-group-item list-group-item-action">Partage des données</a>
                                <a href="#securite" class="list-group-item list-group-item-action">Sécurité</a>
                                <a href="#cookies" class="list-group-item list-group-item-action">Cookies</a>
                                <a href="#droits" class="list-group-item list-group-item-action">Vos droits</a>
                                <a href="#modifications" class="list-group-item list-group-item-action">Modifications</a>
                                <a href="#contact" class="list-group-item list-group-item-action">Contact</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu Principal -->
                <div class="col-lg-9">
                    <!-- Introduction -->
                    <section id="introduction" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-info-circle orange-color me-2"></i>Introduction
                        </h4>
                        <p>
                            Bienvenue sur <strong class="orange-color">KAZARIA</strong>. Nous respectons votre vie privée et nous nous engageons 
                            à protéger vos données personnelles. Cette politique de confidentialité vous informe sur la manière dont 
                            nous traitons vos données personnelles lorsque vous visitez notre site web et vous informe de vos droits 
                            en matière de confidentialité et de la manière dont la loi vous protège.
                        </p>
                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Important :</strong> En utilisant notre marketplace, vous acceptez la collecte et l'utilisation 
                            de vos informations conformément à cette politique.
                        </div>
                    </section>

                    <!-- Données collectées -->
                    <section id="donnees-collectees" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-database orange-color me-2"></i>Données Collectées
                        </h4>
                        <p>Nous collectons différents types de données vous concernant :</p>
                        
                        <h5 class="mt-4"><i class="bi bi-person-badge me-2"></i>1. Données d'identification</h5>
                        <ul>
                            <li>Nom et prénom</li>
                            <li>Adresse email</li>
                            <li>Numéro de téléphone</li>
                            <li>Adresse postale</li>
                        </ul>

                        <h5 class="mt-4"><i class="bi bi-credit-card me-2"></i>2. Données de paiement</h5>
                        <ul>
                            <li>Informations de carte bancaire (cryptées)</li>
                            <li>Historique des transactions</li>
                            <li>Méthodes de paiement enregistrées</li>
                        </ul>

                        <h5 class="mt-4"><i class="bi bi-graph-up me-2"></i>3. Données de navigation</h5>
                        <ul>
                            <li>Adresse IP</li>
                            <li>Type de navigateur</li>
                            <li>Pages visitées</li>
                            <li>Produits consultés</li>
                            <li>Cookies et technologies similaires</li>
                        </ul>
                    </section>

                    <!-- Utilisation des données -->
                    <section id="utilisation" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-gear orange-color me-2"></i>Utilisation des Données
                        </h4>
                        <p>Nous utilisons vos données personnelles pour :</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title orange-color">
                                            <i class="bi bi-cart-check me-2"></i>Traitement des commandes
                                        </h6>
                                        <p class="card-text small">Gérer vos achats, livraisons et retours</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title orange-color">
                                            <i class="bi bi-envelope me-2"></i>Communication
                                        </h6>
                                        <p class="card-text small">Vous envoyer des confirmations et mises à jour</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title orange-color">
                                            <i class="bi bi-star me-2"></i>Amélioration du service
                                        </h6>
                                        <p class="card-text small">Personnaliser votre expérience d'achat</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title orange-color">
                                            <i class="bi bi-shield-check me-2"></i>Sécurité
                                        </h6>
                                        <p class="card-text small">Prévenir la fraude et protéger votre compte</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Partage des données -->
                    <section id="partage" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-share orange-color me-2"></i>Partage des Données
                        </h4>
                        <p>Nous ne vendons jamais vos données personnelles. Nous pouvons les partager uniquement avec :</p>
                        <ul>
                            <li><strong>Prestataires de services :</strong> Transporteurs, processeurs de paiement (avec cryptage)</li>
                            <li><strong>Partenaires logistiques :</strong> Pour assurer la livraison de vos commandes</li>
                            <li><strong>Autorités légales :</strong> Si requis par la loi</li>
                        </ul>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Garantie :</strong> Tous nos partenaires sont contractuellement tenus de respecter la confidentialité 
                            de vos données et de les utiliser uniquement aux fins spécifiées.
                        </div>
                    </section>

                    <!-- Sécurité -->
                    <section id="securite" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-lock orange-color me-2"></i>Sécurité des Données
                        </h4>
                        <p>Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles pour protéger vos données :</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="bi bi-shield-lock orange-color" style="font-size: 40px;"></i>
                                    <h6 class="mt-2">Cryptage SSL/TLS</h6>
                                    <small class="text-muted">Toutes les données sont cryptées</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="bi bi-server orange-color" style="font-size: 40px;"></i>
                                    <h6 class="mt-2">Serveurs sécurisés</h6>
                                    <small class="text-muted">Hébergement certifié</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="bi bi-fingerprint orange-color" style="font-size: 40px;"></i>
                                    <h6 class="mt-2">Accès contrôlé</h6>
                                    <small class="text-muted">Personnel autorisé uniquement</small>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Cookies -->
                    <section id="cookies" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-cookie orange-color me-2"></i>Cookies et Technologies Similaires
                        </h4>
                        <p>Nous utilisons des cookies pour améliorer votre expérience. Les types de cookies utilisés :</p>
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Durée</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Essentiels</strong></td>
                                    <td>Nécessaires au fonctionnement du site</td>
                                    <td>Session</td>
                                </tr>
                                <tr>
                                    <td><strong>Préférences</strong></td>
                                    <td>Mémorisation de vos choix</td>
                                    <td>1 an</td>
                                </tr>
                                <tr>
                                    <td><strong>Analytiques</strong></td>
                                    <td>Statistiques de navigation</td>
                                    <td>2 ans</td>
                                </tr>
                                <tr>
                                    <td><strong>Marketing</strong></td>
                                    <td>Publicités personnalisées</td>
                                    <td>1 an</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="text-muted small">
                            Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.
                        </p>
                    </section>

                    <!-- Vos droits -->
                    <section id="droits" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-person-check orange-color me-2"></i>Vos Droits
                        </h4>
                        <p>Conformément au RGPD et aux lois locales, vous disposez des droits suivants :</p>
                        <div class="accordion" id="rightsAccordion">
                            <div class="accordion-item">
                                <h4 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#right1">
                                        <i class="bi bi-eye me-2"></i>Droit d'accès
                                    </button>
                                </h4>
                                <div id="right1" class="accordion-collapse collapse show" data-bs-parent="#rightsAccordion">
                                    <div class="accordion-body">
                                        Vous pouvez demander une copie de toutes les données personnelles que nous détenons sur vous.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h4 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#right2">
                                        <i class="bi bi-pencil me-2"></i>Droit de rectification
                                    </button>
                                </h4>
                                <div id="right2" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                    <div class="accordion-body">
                                        Vous pouvez demander la correction de données incorrectes ou incomplètes.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h4 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#right3">
                                        <i class="bi bi-trash me-2"></i>Droit à l'effacement
                                    </button>
                                </h4>
                                <div id="right3" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                    <div class="accordion-body">
                                        Vous pouvez demander la suppression de vos données personnelles dans certaines circonstances.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h4 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#right4">
                                        <i class="bi bi-box-arrow-right me-2"></i>Droit à la portabilité
                                    </button>
                                </h4>
                                <div id="right4" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                    <div class="accordion-body">
                                        Vous pouvez recevoir vos données dans un format structuré et les transférer à un autre service.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h4 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#right5">
                                        <i class="bi bi-hand-stop me-2"></i>Droit d'opposition
                                    </button>
                                </h4>
                                <div id="right5" class="accordion-collapse collapse" data-bs-parent="#rightsAccordion">
                                    <div class="accordion-body">
                                        Vous pouvez vous opposer au traitement de vos données à des fins marketing.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Pour exercer vos droits, contactez-nous à : <strong>privacy@kazaria.ci</strong>
                        </div>
                    </section>

                    <!-- Modifications -->
                    <section id="modifications" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-arrow-repeat orange-color me-2"></i>Modifications de cette Politique
                        </h4>
                        <p>
                            Nous pouvons mettre à jour cette politique de confidentialité de temps en temps. Toute modification 
                            sera publiée sur cette page avec une date de mise à jour. Nous vous encourageons à consulter 
                            régulièrement cette page pour rester informé.
                        </p>
                        <p class="text-muted">
                            En cas de changements importants, nous vous en informerons par email ou via une notification sur le site.
                        </p>
                    </section>

                    <!-- Contact -->
                    <section id="contact" class="mb-5">
                        <h4 class="border-bottom pb-3 mb-4">
                            <i class="bi bi-envelope orange-color me-2"></i>Nous Contacter
                        </h4>
                        <p>Pour toute question concernant cette politique de confidentialité, contactez-nous :</p>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6 class="orange-color"><i class="bi bi-geo-alt me-2"></i>Adresse</h6>
                                        <p class="mb-0">KAZARIA Marketplace<br>Abidjan, Cocody<br>Côte d'Ivoire</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="orange-color"><i class="bi bi-envelope me-2"></i>Email</h6>
                                        <p class="mb-0">
                                            Support : <a href="mailto:support@kazaria.ci">support@kazaria.ci</a><br>
                                            Confidentialité : <a href="mailto:privacy@kazaria.ci">privacy@kazaria.ci</a>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="orange-color"><i class="bi bi-telephone me-2"></i>Téléphone</h6>
                                        <p class="mb-0">+225 XX XX XX XX XX</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="orange-color"><i class="bi bi-clock me-2"></i>Horaires</h6>
                                        <p class="mb-0">Lundi - Vendredi : 8h - 18h<br>Samedi : 9h - 17h</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Call to Action -->
                    <section class="text-center py-5 bg-light rounded">
                        <h3 class="mb-3">Des questions ?</h3>
                        <p class="mb-4">Notre équipe est là pour vous aider</p>
                        <a href="{{ route('accueil') }}" class="btn btn-sm orange-bg text-white me-2">
                            <i class="bi bi-house me-2"></i>Retour à l'accueil
                        </a>
                        <a href="mailto:privacy@kazaria.ci" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-envelope me-2"></i>Nous contacter
                        </a>
                    </section>
                </div>
            </div>
        </section>
    </main>
@endsection

