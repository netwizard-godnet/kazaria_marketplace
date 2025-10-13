@extends('layouts.app')

@section('content')
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                    <li class="breadcrumb-item active fs-7" aria-current="page">Contactez-nous</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- SECTION BREADCRUMB END -->

    <!-- SECTION CONTACT -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="fw-bold mb-3">Contactez-nous</h3>
                    <p class="text-muted">Nous sommes là pour vous aider ! N'hésitez pas à nous contacter</p>
                </div>
            </div>

            <div class="row g-5">
                <!-- Informations de contact -->
                <div class="col-lg-4">
                    <div class="bg-light p-4 rounded h-100">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-telephone me-2 orange-color"></i>
                            Nos coordonnées
                        </h5>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold orange-color">Téléphone</h6>
                            <p class="mb-0">
                                <a href="tel:+2250701234567" class="text-decoration-none">
                                    <i class="bi bi-phone me-2"></i>+225 07 01 23 45 67
                                </a>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold orange-color">Email</h6>
                            <p class="mb-0">
                                <a href="mailto:contact@kazaria.ci" class="text-decoration-none">
                                    <i class="bi bi-envelope me-2"></i>contact@kazaria.ci
                                </a>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold orange-color">Adresse</h6>
                            <p class="mb-0">
                                <i class="bi bi-geo-alt me-2"></i>
                                Cocody, Angré 8ème Tranche<br>
                                Abidjan, Côte d'Ivoire
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold orange-color">Horaires d'ouverture</h6>
                            <p class="mb-0">
                                <i class="bi bi-clock me-2"></i>
                                Lundi - Vendredi : 8h00 - 18h00<br>
                                Samedi : 9h00 - 16h00<br>
                                Dimanche : Fermé
                            </p>
                        </div>

                        <!-- Liens rapides -->
                        <div class="mt-4">
                            <h6 class="fw-bold orange-color mb-3">Contact rapide</h6>
                            <div class="d-grid gap-2">
                                <a href="https://wa.me/2250701234567" class="btn btn-success btn-sm" target="_blank">
                                    <i class="bi bi-whatsapp me-2"></i>WhatsApp
                                </a>
                                <a href="{{ route('help-faq') }}" class="btn blue-bg text-white btn-sm">
                                    <i class="bi bi-question-circle me-2"></i>FAQ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de contact -->
                <div class="col-lg-8">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-chat-dots me-2 orange-color"></i>
                            Envoyez-nous un message
                        </h5>

                        <form id="contactForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone">
                                </div>
                                <div class="col-12">
                                    <label for="sujet" class="form-label">Sujet *</label>
                                    <select class="form-select" id="sujet" name="sujet" required>
                                        <option value="">Choisissez un sujet</option>
                                        <option value="commande">Question sur une commande</option>
                                        <option value="livraison">Livraison</option>
                                        <option value="paiement">Paiement</option>
                                        <option value="retour">Retour/Échange</option>
                                        <option value="produit">Question sur un produit</option>
                                        <option value="compte">Problème de compte</option>
                                        <option value="vendeur">Devenir vendeur</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              placeholder="Décrivez votre demande en détail..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                        <label class="form-check-label" for="newsletter">
                                            Je souhaite recevoir les actualités et offres de KAZARIA
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-sm orange-bg text-white px-4">
                                        <i class="bi bi-send me-2"></i>Envoyer le message
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Messages de statut -->
                        <div id="contactStatus" class="mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- FAQ rapide -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="bg-light p-4 rounded">
                        <h4 class="fw-bold mb-4 text-center">
                            <i class="bi bi-lightbulb me-2 orange-color"></i>
                            Questions fréquentes
                        </h4>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="bi bi-truck blue-color fs-1 mb-3"></i>
                                    <h6 class="fw-bold">Livraison</h6>
                                    <p class="text-muted small">Délais et options de livraison</p>
                                    <a href="{{ route('help-faq') }}#faq2" class="btn blue-bg text-white btn-sm">En savoir plus</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="bi bi-credit-card orange-color fs-1 mb-3"></i>
                                    <h6 class="fw-bold">Paiement</h6>
                                    <p class="text-muted small">Modes de paiement sécurisés</p>
                                    <a href="{{ route('help-faq') }}#faq3" class="btn orange-bg text-white btn-sm">En savoir plus</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="bi bi-arrow-return-left blue-color fs-1 mb-3"></i>
                                    <h6 class="fw-bold">Retours</h6>
                                    <p class="text-muted small">Politique de retour</p>
                                    <a href="{{ route('help-faq') }}#faq4" class="btn blue-bg text-white btn-sm">En savoir plus</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- SECTION CONTACT END -->
</main>

<!-- JavaScript pour le formulaire -->
<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const statusDiv = document.getElementById('contactStatus');
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Afficher un message de chargement
    statusDiv.innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-hourglass-split me-2"></i>
            Envoi en cours...
        </div>
    `;
    statusDiv.style.display = 'block';
    
    // Désactiver le bouton
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Envoi en cours...';
    
    try {
        // Préparer les données pour l'API
        const data = {
            prenom: formData.get('prenom'),
            nom: formData.get('nom'),
            email: formData.get('email'),
            telephone: formData.get('telephone'),
            sujet: formData.get('sujet'),
            message: formData.get('message'),
            newsletter: formData.has('newsletter')
        };
        
        // Envoyer la requête
        const response = await fetch('/api/contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Succès
            statusDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Message envoyé avec succès !</strong><br>
                    Bonjour ${result.data.nom}, nous avons bien reçu votre message concernant "${result.data.sujet}".<br>
                    Nous vous répondrons dans les plus brefs délais.
                </div>
            `;
            
            // Réinitialiser le formulaire
            this.reset();
            
            // Masquer le message après 8 secondes
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 8000);
        } else {
            // Erreur de validation ou autre
            let errorMessage = result.message || 'Une erreur est survenue';
            
            if (result.errors) {
                errorMessage += '<ul class="mb-0 mt-2">';
                Object.values(result.errors).forEach(error => {
                    errorMessage += `<li>${error[0]}</li>`;
                });
                errorMessage += '</ul>';
            }
            
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Erreur :</strong><br>
                    ${errorMessage}
                </div>
            `;
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        statusDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Erreur de connexion</strong><br>
                Impossible d'envoyer le message. Veuillez vérifier votre connexion internet et réessayer.
            </div>
        `;
    } finally {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});
</script>
@endsection
