(function (global, factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        module.exports = factory();
    } else {
        global.MultiCarousel = factory();
    }
})(this, function () {

    class MultiCarousel {
        constructor(container, options = {}) {
        this.container = container;
        this.track = container.querySelector(".multi-carousel-track");
        this.items = Array.from(container.querySelectorAll(".multi-carousel-item"));
        this.prevBtn = container.querySelector(".multi-carousel-prev");
        this.nextBtn = container.querySelector(".multi-carousel-next");
        this.dotsContainer = container.querySelector(".multi-carousel-dots");

        // Vérifier que les éléments essentiels existent
        if (!this.track || !this.items || this.items.length === 0) {
            console.warn('MultiCarousel: Pas assez d\'éléments pour initialiser le carousel');
            return;
        }

        this.options = Object.assign({
            slidesToShow: 4,
            slidesToScroll: 1,
            gap: 10,
            autoplay: false,
            autoplaySpeed: 3000,
            pauseOnHover: true,
            responsive: []
        }, options);

        this.currentIndex = 0;
        this.isTransitioning = false;
        this.timer = null;
        this.setupAttempts = 0;
        this.maxSetupAttempts = 10;

        // Attendre que le DOM soit complètement chargé avant de setup
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.setup();
                this.attachEvents();
            });
        } else {
            // Utiliser un petit délai pour s'assurer que les éléments sont rendus
            setTimeout(() => {
                this.setup();
                this.attachEvents();
            }, 0);
        }
        
        window.addEventListener("resize", () => {
            // Réinitialiser le compteur à chaque resize pour permettre de nouvelles tentatives
            this.setupAttempts = 0;
            // Utiliser un debounce pour éviter trop d'appels
            clearTimeout(this.resizeTimer);
            this.resizeTimer = setTimeout(() => {
                this.setup();
            }, 100);
        });
        
        if (this.options.autoplay) {
            // Démarrer l'autoplay après le setup
            setTimeout(() => {
                if (this.items && this.items.length > 0) {
                    this.startAutoplay();
                }
            }, 100);
        }
        }

        getSlidesToShow() {
        let slidesToShow = this.options.slidesToShow;
        const width = window.innerWidth;
        if (this.options.responsive && Array.isArray(this.options.responsive)) {
            const sorted = this.options.responsive.sort((a,b) => b.breakpoint - a.breakpoint);
            for (const bp of sorted) {
            if (width <= bp.breakpoint) slidesToShow = bp.settings.slidesToShow ?? slidesToShow;
            }
        }
        return slidesToShow;
        }

        setup() {
        this.slidesToShow = this.getSlidesToShow();

        // Vérifier qu'il y a des éléments essentiels
        if (!this.track || !this.items || this.items.length === 0) {
            return;
        }

        // Vérifier que le container a une largeur
        if (!this.container || this.container.offsetWidth === 0) {
            // Réessayer après un court délai si le container n'est pas encore rendu
            this.setupAttempts++;
            if (this.setupAttempts < this.maxSetupAttempts) {
                setTimeout(() => this.setup(), 50);
            }
            return;
        }
        
        // Réinitialiser le compteur si le setup réussit
        this.setupAttempts = 0;

        // Clear track and clone for infinite loop
        this.track.innerHTML = "";
        
        // Pour la boucle infinie, toujours cloner même s'il y a peu d'éléments
        const actualSlidesToShow = Math.min(this.slidesToShow, this.items.length);
        
        // Calculer combien d'éléments cloner pour une boucle fluide
        const clonesNeeded = Math.max(actualSlidesToShow, 1);
        
        // Cloner les éléments avant (fin de la liste)
        const clonesBefore = [];
        if (this.items.length > 0) {
            // Cloner suffisamment pour créer l'illusion de boucle infinie
            const itemsToClone = Math.min(clonesNeeded, this.items.length);
            for (let i = 0; i < itemsToClone; i++) {
                const index = this.items.length - itemsToClone + i;
                clonesBefore.push(this.items[index].cloneNode(true));
            }
        }
        
        // Cloner les éléments après (début de la liste)
        const clonesAfter = [];
        if (this.items.length > 0) {
            const itemsToClone = Math.min(clonesNeeded, this.items.length);
            for (let i = 0; i < itemsToClone; i++) {
                clonesAfter.push(this.items[i].cloneNode(true));
            }
        }

        // Assembler: clones avant + éléments originaux + clones après
        [...clonesBefore, ...this.items, ...clonesAfter].forEach(i => this.track.appendChild(i));
        this.allItems = Array.from(this.track.children);

        // Vérifier qu'il y a des éléments dans le track
        if (this.allItems.length === 0) {
            return;
        }

        // Set widths
        const containerWidth = this.container.offsetWidth;
        if (containerWidth === 0) {
            this.setupAttempts++;
            if (this.setupAttempts < this.maxSetupAttempts) {
                setTimeout(() => this.setup(), 50);
            }
            return;
        }
        
        const itemWidth = (containerWidth / actualSlidesToShow) - this.options.gap;
        this.allItems.forEach(i => {
            if (i && i.style) {
                i.style.minWidth = `${itemWidth}px`;
                i.style.marginRight = `${this.options.gap}px`;
            }
        });

        // Reset position au début des éléments originaux (après les clones de début)
        this.currentIndex = clonesBefore.length;
        this.updatePosition(false);

        // Dots
        this.updateDots();
        }

        attachEvents() {
        if (this.nextBtn) this.nextBtn.addEventListener("click", () => this.next());
        if (this.prevBtn) this.prevBtn.addEventListener("click", () => this.prev());

        if (this.options.pauseOnHover && this.options.autoplay) {
            this.container.addEventListener("mouseenter", () => this.stopAutoplay());
            this.container.addEventListener("mouseleave", () => this.startAutoplay());
        }
        }

        updatePosition(animate = true) {
        if (!this.track || !this.allItems || this.allItems.length === 0) {
            return;
        }
        
        const firstItem = this.allItems[0];
        if (!firstItem) {
            return;
        }
        
        // Vérifier que l'élément a une largeur calculée
        let itemWidth = firstItem.offsetWidth || 0;
        
        // Si la largeur n'est pas disponible, essayer de la calculer depuis le container
        if (itemWidth === 0 && this.container && this.container.offsetWidth > 0) {
            const actualSlidesToShow = Math.min(this.slidesToShow, this.items.length);
            itemWidth = (this.container.offsetWidth / actualSlidesToShow) - this.options.gap;
        }
        
        // Si toujours 0, utiliser une valeur par défaut pour éviter les erreurs
        if (itemWidth === 0) {
            itemWidth = 200; // Largeur par défaut
        }
        
        this.track.style.transition = animate ? "transform 0.5s" : "none";
        const offset = -(this.currentIndex * (itemWidth + this.options.gap));
        this.track.style.transform = `translateX(${offset}px)`;
        }

        next() {
        if (this.isTransitioning || !this.items || this.items.length === 0) return;
        this.isTransitioning = true;
        const actualSlidesToShow = Math.min(this.slidesToShow, this.items.length);
        const clonesNeeded = Math.max(actualSlidesToShow, 1);
        
        this.currentIndex += this.options.slidesToScroll;
        this.updatePosition(true);

        this.track.addEventListener("transitionend", () => {
            // Si on dépasse la fin (après les clones de fin), revenir au début des vrais éléments
            if (this.currentIndex >= this.items.length + clonesNeeded) {
                this.currentIndex = clonesNeeded;
                this.updatePosition(false);
            }
            this.isTransitioning = false;
            this.updateActiveDot();
        }, { once: true });
        }

        prev() {
        if (this.isTransitioning || !this.items || this.items.length === 0) return;
        this.isTransitioning = true;
        const actualSlidesToShow = Math.min(this.slidesToShow, this.items.length);
        const clonesNeeded = Math.max(actualSlidesToShow, 1);
        
        this.currentIndex -= this.options.slidesToScroll;
        this.updatePosition(true);

        this.track.addEventListener("transitionend", () => {
            // Si on dépasse le début (avant les clones de début), aller à la fin des vrais éléments
            if (this.currentIndex < clonesNeeded) {
                this.currentIndex = this.items.length + clonesNeeded - 1;
                this.updatePosition(false);
            }
            this.isTransitioning = false;
            this.updateActiveDot();
        }, { once: true });
        }

        updateDots() {
        if (!this.dotsContainer || !this.items || this.items.length === 0) return;
        this.dotsContainer.innerHTML = "";
        const actualSlidesToShow = Math.min(this.slidesToShow, this.items.length);
        const totalDots = Math.ceil(this.items.length / this.options.slidesToScroll);
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement("button");
            dot.classList.add("carousel-dot");
            if (i === 0) dot.classList.add("active");
            dot.addEventListener("click", () => {
            this.currentIndex = actualSlidesToShow + i * this.options.slidesToScroll;
            this.updatePosition(true);
            this.updateActiveDot();
            });
            this.dotsContainer.appendChild(dot);
        }
        }

        updateActiveDot() {
        if (!this.dotsContainer || !this.items || this.items.length === 0) return;
        const dots = this.dotsContainer.querySelectorAll(".carousel-dot");
        dots.forEach(dot => dot.classList.remove("active"));
        const actualSlidesToShow = Math.min(this.slidesToShow, this.items.length);
        const activeIndex = Math.floor((this.currentIndex - actualSlidesToShow) / this.options.slidesToScroll);
        if (dots[activeIndex]) dots[activeIndex].classList.add("active");
        }

        startAutoplay() {
        this.stopAutoplay();
        this.timer = setInterval(() => this.next(), this.options.autoplaySpeed);
        }

        stopAutoplay() {
        if (this.timer) clearInterval(this.timer);
        }

        static initAll(selector = "[data-multi-carousel]", options = {}) {
        document.querySelectorAll(selector).forEach(container => {
            new MultiCarousel(container, options);
        });
        }
    }

    return MultiCarousel;
});