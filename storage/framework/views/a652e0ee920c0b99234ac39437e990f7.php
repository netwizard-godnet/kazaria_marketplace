<footer class="container-fluid pt-2">
    <div class="container py-5 border-top border-bottom">
        <div class="row g-3">
            <div class="col-md-3">
                <p class="mb-2 fw-bold">BESOIN D'AIDE ?</p>
                <div class="vstack gap-1 text-start ms-2">
                    <?php
                        $whatsappPhone = str_replace(['+', ' ', '-'], '', $settings['contact_phone'] ?? '+225 07 00 00 00 00');
                    ?>
                    <a href="https://wa.me/<?php echo e($whatsappPhone); ?>" class="btn btn-sm text-secondary text-start fs-8" target="_blank">Discuter avec nous</a>
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
                    <a href="<?php echo e(route('qui-nous-sommes')); ?>" class="btn btn-sm text-secondary text-start fs-8">Qui nous sommes ?</a>
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

<!-- Pop-up Rappel Panier (en bas à droite) -->
<div id="cartReminderPopup" class="cart-reminder-popup z-index-9x" style="display: none;">
    <div class="cart-reminder-content">
        <div class="cart-reminder-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="bi bi-bag-check-fill me-2"></i>
                    <h6 class="mb-0 fw-bold">Votre panier</h6>
                    <span class="badge bg-danger ms-2" id="cartReminderCount">0</span>
                </div>
                <button type="button" class="btn-close btn-close-white" id="closeCartReminder" aria-label="Fermer"></button>
            </div>
        </div>
        <div class="cart-reminder-body">
            <p class="text-muted small mb-3">
                <i class="bi bi-clock-history me-1"></i>
                Ne ratez pas votre commande !
            </p>
            <div id="cartReminderProducts" class="cart-reminder-products">
                <!-- Les produits seront chargés ici -->
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="cart-reminder-footer">
            <a href="<?php echo e(route('product-cart')); ?>" class="btn orange-bg text-white w-100 fw-bold" onclick="if (window.hideCartReminderPopup) window.hideCartReminderPopup();">
                <i class="bi bi-cart-check-fill me-2"></i>Voir mon panier
            </a>
        </div>
    </div>
</div>

<!-- Styles pour le pop-up rappel panier -->
<style>
    .cart-reminder-popup {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 380px;
        max-width: calc(100vw - 40px);
        max-height: calc(100vh - 40px);
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        z-index: 1050;
        overflow: hidden;
        animation: slideInFromRight 0.4s ease-out;
    }
    
    .cart-reminder-content {
        display: flex;
        flex-direction: column;
        height: 100%;
        max-height: 600px;
    }
    
    .cart-reminder-header {
        background: linear-gradient(135deg, #ff8c00 0%, #ffa64d 100%);
        color: white;
        padding: 1rem 1.25rem;
        border-bottom: none;
    }
    
    .cart-reminder-header .btn-close-white {
        filter: invert(1);
        opacity: 0.9;
    }
    
    .cart-reminder-header .btn-close-white:hover {
        opacity: 1;
    }
    
    .cart-reminder-body {
        padding: 1.25rem;
        overflow-y: auto;
        flex: 1;
        min-height: 200px;
        max-height: 400px;
    }
    
    .cart-reminder-products {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .cart-reminder-product-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    
    .cart-reminder-product-item:hover {
        background: #f0f0f0;
        transform: translateX(4px);
    }
    
    .cart-reminder-product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        background: #fff;
        border: 1px solid #e9ecef;
    }
    
    .cart-reminder-product-info {
        flex: 1;
        min-width: 0;
    }
    
    .cart-reminder-product-name {
        font-size: 0.9rem;
        font-weight: 500;
        color: #212529;
        margin: 0 0 0.25rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .cart-reminder-product-details {
        font-size: 0.8rem;
        color: #6c757d;
        margin: 0;
    }
    
    .cart-reminder-product-price {
        font-size: 0.95rem;
        font-weight: 600;
        color: #ff8c00;
        white-space: nowrap;
    }
    
    .cart-reminder-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid #e9ecef;
        background: #f8f9fa;
    }
    
    .cart-reminder-footer .btn {
        padding: 0.75rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .cart-reminder-footer .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 140, 0, 0.3);
    }
    
    @keyframes slideInFromRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutToRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .cart-reminder-popup.hiding {
        animation: slideOutToRight 0.3s ease-in forwards;
    }
    
    /* Styles pour mobile */
    @media (max-width: 576px) {
        .cart-reminder-popup {
            bottom: 80px; /* Au-dessus du footer mobile */
            right: 10px;
            left: 10px;
            width: auto;
            max-width: calc(100vw - 20px);
            max-height: calc(100vh - 100px);
        }
        
        .cart-reminder-header {
            padding: 0.875rem 1rem;
        }
        
        .cart-reminder-header h6 {
            font-size: 0.9rem;
        }
        
        .cart-reminder-body {
            padding: 1rem;
            max-height: 300px;
        }
        
        .cart-reminder-product-item {
            padding: 0.625rem;
        }
        
        .cart-reminder-product-image {
            width: 50px;
            height: 50px;
        }
        
        .cart-reminder-product-name {
            font-size: 0.85rem;
        }
        
        .cart-reminder-product-price {
            font-size: 0.85rem;
        }
        
        .cart-reminder-footer {
            padding: 0.875rem 1rem;
        }
        
        .cart-reminder-footer .btn {
            font-size: 0.9rem;
            padding: 0.625rem;
        }
    }
    
    /* Scrollbar personnalisée */
    .cart-reminder-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .cart-reminder-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .cart-reminder-body::-webkit-scrollbar-thumb {
        background: #ff8c00;
        border-radius: 3px;
    }
    
    .cart-reminder-body::-webkit-scrollbar-thumb:hover {
        background: #ff9500;
    }
</style>

<!-- Backdrop pour le pop-up cookies -->
<div id="cookieConsentBackdrop" class="cookie-consent-backdrop" style="display: none;"></div>

<!-- Pop-up Consentement Cookies -->
<div id="cookieConsentBanner" class="cookie-consent-banner" style="display: none;">
    <div class="cookie-consent-content">
        <div class="cookie-consent-header">
            <i class="bi bi-cookie me-2"></i>
            <h6 class="mb-0 fw-bold">Nous utilisons des cookies</h6>
        </div>
        <div class="cookie-consent-body">
            <p class="mb-3">Nous utilisons des cookies pour améliorer votre expérience de navigation, analyser le trafic du site et personnaliser le contenu. En continuant à utiliser notre site, vous acceptez notre utilisation des cookies.</p>
            <div class="cookie-options mb-3">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="cookieNecessary" checked disabled>
                    <label class="form-check-label" for="cookieNecessary">
                        <strong>Cookies nécessaires</strong> <small class="text-muted">(obligatoires)</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="cookieAnalytics">
                    <label class="form-check-label" for="cookieAnalytics">
                        <strong>Cookies analytiques</strong>
                        <small class="text-muted d-block">Nous aident à comprendre comment vous utilisez le site</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="cookieMarketing">
                    <label class="form-check-label" for="cookieMarketing">
                        <strong>Cookies marketing</strong>
                        <small class="text-muted d-block">Pour personnaliser vos publicités et mesurer leur efficacité</small>
                    </label>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="cookieRejectAll">
                    <i class="bi bi-x-circle me-1"></i>Refuser tout
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" id="cookieCustomize">
                    <i class="bi bi-gear me-1"></i>Personnaliser
                </button>
                <button type="button" class="btn orange-bg text-white btn-sm flex-fill" id="cookieAcceptAll">
                    <i class="bi bi-check-circle me-1"></i>Accepter tout
                </button>
            </div>
            <div class="mt-3">
                <a href="<?php echo e(route('privacy-policy') ?? '#'); ?>" class="small text-muted text-decoration-none">
                    <i class="bi bi-info-circle me-1"></i>En savoir plus sur notre politique de confidentialité
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Styles pour le pop-up cookies -->
<style>
    .cookie-consent-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99998;
        animation: fadeIn 0.3s ease-out;
    }
    
    .cookie-consent-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 3px solid #ff8c00;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        z-index: 99999 !important;
        animation: slideUp 0.5s ease-out;
    }
    
    .cookie-consent-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
    }
    
    .cookie-consent-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: #212529;
    }
    
    .cookie-consent-header i {
        font-size: 1.5rem;
        color: #ff8c00;
    }
    
    .cookie-consent-body {
        font-size: 0.9rem;
        line-height: 1.6;
    }
    
    .cookie-options {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .cookie-options .form-check-label {
        font-size: 0.9rem;
        cursor: pointer;
    }
    
    .cookie-options .form-check-input {
        cursor: pointer;
        margin-top: 0.3rem;
    }
    
    .cookie-options .form-check-input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .cookie-consent-body .btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .cookie-consent-body .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }
    
    .cookie-consent-banner.hiding {
        animation: slideDown 0.3s ease-in forwards;
    }
    
    .cookie-consent-backdrop.hiding {
        animation: fadeOut 0.3s ease-in forwards;
    }
    
    /* Styles pour mobile */
    @media (max-width: 768px) {
        .cookie-consent-content {
            padding: 1rem;
        }
        
        .cookie-consent-header {
            font-size: 1rem;
        }
        
        .cookie-consent-body {
            font-size: 0.85rem;
        }
        
        .cookie-options {
            padding: 0.75rem;
        }
        
        .cookie-consent-body .btn {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }
        
        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }
    }
    
    /* Ajustement pour le footer mobile */
    @media (max-width: 576px) {
        .cookie-consent-backdrop {
            z-index: 99998 !important;
        }
        
        .cookie-consent-banner {
            bottom: 80px !important; /* Au-dessus du footer mobile */
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            z-index: 99999 !important;
        }
        
        /* S'assurer que le footer mobile ne cache pas le pop-up */
        footer[style*="position: fixed"] {
            z-index: 1000 !important;
        }
    }
</style>

<!-- Script de gestion des cookies -->
<script>
(function() {
    const COOKIE_CONSENT_KEY = 'kazaria_cookie_consent';
    
    function getCookieConsent() {
        try {
            const consent = localStorage.getItem(COOKIE_CONSENT_KEY);
            return consent ? JSON.parse(consent) : null;
        } catch (e) {
            return null;
        }
    }
    
    function setCookieConsent(consent) {
        try {
            localStorage.setItem(COOKIE_CONSENT_KEY, JSON.stringify({
                ...consent,
                timestamp: Date.now()
            }));
        } catch (e) {
            console.error('Erreur sauvegarde consentement cookies:', e);
        }
    }
    
    function hideCookieBanner() {
        const banner = document.getElementById('cookieConsentBanner');
        const backdrop = document.getElementById('cookieConsentBackdrop');
        
        if (banner) {
            banner.classList.add('hiding');
            setTimeout(() => {
                banner.style.display = 'none';
            }, 300);
        }
        
        if (backdrop) {
            backdrop.classList.add('hiding');
            setTimeout(() => {
                backdrop.style.display = 'none';
            }, 300);
        }
    }
    
    function showCookieBanner() {
        const banner = document.getElementById('cookieConsentBanner');
        const backdrop = document.getElementById('cookieConsentBackdrop');
        
        if (backdrop) {
            backdrop.style.display = 'block';
        }
        
        if (banner) {
            banner.style.display = 'block';
        }
    }
    
    function acceptAllCookies() {
        setCookieConsent({
            necessary: true,
            analytics: true,
            marketing: true
        });
        hideCookieBanner();
        // Ici vous pouvez initialiser tous les scripts de tracking
        initializeTrackingScripts();
    }
    
    function rejectAllCookies() {
        setCookieConsent({
            necessary: true, // Toujours true car obligatoires
            analytics: false,
            marketing: false
        });
        hideCookieBanner();
    }
    
    function saveCustomConsent() {
        const analytics = document.getElementById('cookieAnalytics').checked;
        const marketing = document.getElementById('cookieMarketing').checked;
        
        setCookieConsent({
            necessary: true, // Toujours true
            analytics: analytics,
            marketing: marketing
        });
        hideCookieBanner();
        
        // Initialiser seulement les scripts autorisés
        if (analytics) initializeAnalytics();
        if (marketing) initializeMarketing();
    }
    
    function initializeTrackingScripts() {
        // Initialiser tous les scripts de tracking
        initializeAnalytics();
        initializeMarketing();
    }
    
    function initializeAnalytics() {
        // Ajouter votre code Google Analytics ou autre ici
        console.log('Analytics initialisés');
    }
    
    function initializeMarketing() {
        // Ajouter votre code de marketing/tracking ici
        console.log('Marketing initialisé');
    }
    
    // Initialisation au chargement
    document.addEventListener('DOMContentLoaded', function() {
        const consent = getCookieConsent();
        
        if (!consent) {
            // Afficher le banner si aucun consentement n'a été donné
            showCookieBanner();
        } else {
            // Initialiser les scripts selon le consentement existant
            if (consent.analytics) initializeAnalytics();
            if (consent.marketing) initializeMarketing();
        }
        
        // Gestion des boutons
        const acceptBtn = document.getElementById('cookieAcceptAll');
        const rejectBtn = document.getElementById('cookieRejectAll');
        const customizeBtn = document.getElementById('cookieCustomize');
        
        if (acceptBtn) {
            acceptBtn.addEventListener('click', acceptAllCookies);
        }
        
        if (rejectBtn) {
            rejectBtn.addEventListener('click', rejectAllCookies);
        }
        
        if (customizeBtn) {
            customizeBtn.addEventListener('click', function() {
                // Sauvegarder les choix personnalisés
                saveCustomConsent();
            });
        }
        
        // Permettre de modifier les préférences en cliquant sur "Personnaliser"
        const cookieOptions = document.querySelectorAll('.cookie-options .form-check-input:not(:disabled)');
        cookieOptions.forEach(input => {
            input.addEventListener('change', function() {
                // Permettre la sauvegarde immédiate si l'utilisateur change les options
                const customizeBtn = document.getElementById('cookieCustomize');
                if (customizeBtn) {
                    customizeBtn.textContent = 'Enregistrer les préférences';
                }
            });
        });
        
        // Empêcher la fermeture du pop-up en cliquant en dehors
        // Le pop-up doit rester visible jusqu'à ce qu'un choix soit fait
    });
    
    // Fonction globale pour vérifier le consentement
    window.hasCookieConsent = function(type) {
        const consent = getCookieConsent();
        if (!consent) return false;
        return consent[type] === true;
    };
})();
</script>

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

<!-- <script>
    window.WIDGET_API_URL = 'http://127.0.0.1:8001/api';
    window.WIDGET_DOMAIN = 'http://127.0.0.1:8001';
</script>
<script src="http://127.0.0.1:8001/widget.js" async></script> -->

<!-- MAIN JS -->

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
    <div id="notificationToast" class="toast z-index-9x" role="alert" aria-live="assertive" aria-atomic="true">
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

<?php if (isset($component)) { $__componentOriginal1c2f983aed04baf4e8352d945f0f624d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c2f983aed04baf4e8352d945f0f624d = $attributes; } ?>
<?php $component = App\View\Components\PopupLauncher::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('popup-launcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PopupLauncher::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c2f983aed04baf4e8352d945f0f624d)): ?>
<?php $attributes = $__attributesOriginal1c2f983aed04baf4e8352d945f0f624d; ?>
<?php unset($__attributesOriginal1c2f983aed04baf4e8352d945f0f624d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c2f983aed04baf4e8352d945f0f624d)): ?>
<?php $component = $__componentOriginal1c2f983aed04baf4e8352d945f0f624d; ?>
<?php unset($__componentOriginal1c2f983aed04baf4e8352d945f0f624d); ?>
<?php endif; ?>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/layouts/footer.blade.php ENDPATH**/ ?>