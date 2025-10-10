<footer class="container-fluid pt-2">
    <div class="container py-5 border-top border-bottom">
        <div class="row g-3">
            <div class="col-md-3">
                <p class="mb-2 fw-bold">BESOIN D'AIDE ?</p>
                <div class="vstack gap-1 text-start ms-2">
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Discuter avec nous</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Aide & FAQ</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Contactez-nous</a>
                </div>
                <p class="mt-3 mb-2 fw-bold">LIENS UTILES</p>
                <div class="vstack gap-1 ms-2">
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Suivre sa commande</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Expédition & Livraison</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Politique de retour</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Comment commander ?</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Agences & Points de relais KAZARIA?</a>
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
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Espace vendeur</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Devenez consultant KAZARIA</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Devenez partenaire de service loqistique</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Toutes les boutiques officielles</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Vente Flash</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Directives relatives informations de paiements sur KAZARIA</a>
                </div>
            </div>
            <div class="col-md-3">
                <p class="mb-2 fw-bold">CATEGORES</p>
                <div class="vstack gap-1 ms-2">
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Téléphones et tablettes</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">TV et Electronique</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Electroménager</a>
                    <a href="" class="btn btn-sm text-secondary text-start fs-8">Ordinateurs et accessoires</a>
                </div>
            </div>
        </div>
    </div>
    <div class="container py-2 d-flex flex-column flex-sm-row align-items-center justify-content-between">
        <p class="mb-0 fs-8">&copy; {{ \Carbon\Carbon::now()->format('Y') }}. Tous droits réservés</>
        <div class="d-flex align-items-center justify-content-start">
            <p class="mb-0 me-2 fs-8">Paiement sécurisé avec :</p>
            <img src="{{ asset('images/mastercard.jpg') }}" class="me-2" alt="">
            <img src="{{ asset('images/visa.jpg') }}" alt="">
        </div>
    </div>
</footer>

<footer class="bg-light px-2 py-4 d-sm-none container-fluid shadow" style="position: sticky; bottom: 0;">
    <div class="row g-2 text-center">
        <div class="col-3">
            <div class="vstack gap-1 align-items-center">
                <i class="fa-solid fa-bars orange-color fa-2x"></i>
                <span class="fs-8">Menu</span>
            </div>
        </div>
        <div class="col-3">
            <div class="vstack gap-1 align-items-center">
                <i class="fa-solid fa-ellipsis orange-color fa-2x"></i>
                <span class="fs-8">Catégorie</span>
            </div>
        </div>
        <div class="col-3">
            <div class="vstack gap-1 align-items-center">
                <i class="fa-solid fa-heart orange-color fa-2x"></i>
                <span class="fs-8">Favoris</span>
            </div>
        </div>
        <div class="col-3">
            <div class="vstack gap-1 align-items-center">
                <i class="fa-solid fa-bag-shopping orange-color fa-2x"></i>
                <span class="fs-8">Panier</span>
            </div>
        </div>
    </div>
</footer>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<!-- MAIN JS -->
 <script src="{{ asset('js/main.js') }}"></script>
 <script src="{{ asset('js/carousel.js') }}"></script>
 <script>
    document.addEventListener("DOMContentLoaded", () => {
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
    });
</script>
</body>
</html>