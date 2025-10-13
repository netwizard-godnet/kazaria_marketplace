@extends('layouts.app')

@section('content')
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                    <li class="breadcrumb-item active fs-7" aria-current="page">Aide & FAQ</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- SECTION BREADCRUMB END -->

    <!-- SECTION AIDE & FAQ -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="fw-bold mb-3">Aide & FAQ</h3>
                    <p class="text-muted">Trouvez rapidement les réponses à vos questions les plus fréquentes</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- FAQ Accordion -->
                    <div class="accordion" id="faqAccordion">
                        <!-- Question 1 -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="bi bi-question-circle me-2 text-primary"></i>
                                    Comment passer une commande sur KAZARIA ?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Pour passer une commande :</p>
                                    <ol>
                                        <li>Naviguez sur notre site et ajoutez les produits de votre choix au panier</li>
                                        <li>Cliquez sur l'icône panier dans le header pour accéder à votre panier</li>
                                        <li>Vérifiez vos articles et cliquez sur "Procéder au paiement"</li>
                                        <li>Remplissez vos informations de livraison</li>
                                        <li>Choisissez votre méthode de paiement</li>
                                        <li>Confirmez votre commande</li>
                                    </ol>
                                    <p class="mt-3"><strong>Vous recevrez un email de confirmation avec votre numéro de commande.</strong></p>
                                </div>
                            </div>
                        </div>

                        <!-- Question 2 -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="bi bi-truck me-2 text-success"></i>
                                    Quelles sont les options de livraison disponibles ?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Nous proposons plusieurs options de livraison :</p>
                                    <ul>
                                        <li><strong>Livraison standard :</strong> 3-5 jours ouvrés (Gratuite)</li>
                                        <li><strong>Livraison express :</strong> 1-2 jours ouvrés (Frais supplémentaires)</li>
                                        <li><strong>Retrait en magasin :</strong> Disponible pour certains produits</li>
                                    </ul>
                                    <p class="mt-3">Les frais de livraison sont calculés automatiquement selon votre adresse et la méthode choisie.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Question 3 -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="bi bi-credit-card me-2 text-warning"></i>
                                    Quels sont les modes de paiement acceptés ?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Nous acceptons les modes de paiement suivants :</p>
                                    <ul>
                                        <li><strong>Paiement à la livraison :</strong> Payez en espèces ou par carte à la réception</li>
                                        <li><strong>Mobile Money :</strong> Orange Money, MTN Mobile Money, Moov Money</li>
                                        <li><strong>Cartes bancaires :</strong> Visa, Mastercard</li>
                                        <li><strong>Wave :</strong> Service de paiement mobile</li>
                                    </ul>
                                    <p class="mt-3">Tous les paiements sont sécurisés et cryptés.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Question 4 -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="bi bi-arrow-return-left me-2 text-info"></i>
                                    Comment retourner un produit ?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Notre politique de retour :</p>
                                    <ul>
                                        <li><strong>Délai :</strong> 14 jours à partir de la réception</li>
                                        <li><strong>Condition :</strong> Produit non utilisé, dans son emballage d'origine</li>
                                        <li><strong>Processus :</strong> Contactez notre service client pour obtenir un numéro de retour</li>
                                        <li><strong>Remboursement :</strong> Effectué sous 5-7 jours ouvrés après réception</li>
                                    </ul>
                                    <p class="mt-3">Certains produits ne sont pas éligibles aux retours (produits personnalisés, etc.).</p>
                                </div>
                            </div>
                        </div>

                        <!-- Question 5 -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="bi bi-shield-check me-2 text-success"></i>
                                    Comment suivre ma commande ?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Pour suivre votre commande :</p>
                                    <ol>
                                        <li>Connectez-vous à votre compte</li>
                                        <li>Allez dans la section "Mes commandes" de votre profil</li>
                                        <li>Cliquez sur la commande que vous souhaitez suivre</li>
                                    </ol>
                                    <p class="mt-3">Vous pouvez aussi utiliser le bouton "Suivre ma commande" dans le header avec votre numéro de commande et email.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Question 6 -->
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    <i class="bi bi-person-plus me-2 text-primary"></i>
                                    Comment créer un compte vendeur ?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Pour devenir vendeur sur KAZARIA :</p>
                                    <ol>
                                        <li>Créez d'abord un compte utilisateur classique</li>
                                        <li>Cliquez sur "Vendez sur KAZARIA" dans le header</li>
                                        <li>Remplissez le formulaire de création de boutique</li>
                                        <li>Uploadez les documents requis (DFE, registre de commerce)</li>
                                        <li>Attendez la validation de votre boutique</li>
                                    </ol>
                                    <p class="mt-3">Une fois validée, vous pourrez ajouter vos produits et gérer vos ventes depuis votre dashboard vendeur.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Contact -->
                    <div class="mt-5 p-4 bg-light rounded">
                        <h4 class="fw-bold mb-3">
                            <i class="bi bi-headset me-2 orange-color"></i>
                            Besoin d'aide supplémentaire ?
                        </h4>
                        <p class="mb-3">Si vous ne trouvez pas la réponse à votre question, notre équipe est là pour vous aider :</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="https://wa.me/2250701234567" class="btn btn-success w-100" target="_blank">
                                    <i class="bi bi-whatsapp me-2"></i>Discuter sur WhatsApp
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('contact') }}" class="btn orange-bg text-white w-100">
                                    <i class="bi bi-envelope me-2"></i>Nous contacter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- SECTION AIDE & FAQ END -->
</main>
@endsection
