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

        this.setup();
        this.attachEvents();
        window.addEventListener("resize", () => this.setup());
        if (this.options.autoplay) this.startAutoplay();
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

        // Clear track and clone for infinite
        this.track.innerHTML = "";
        const clonesBefore = this.items.slice(-this.slidesToShow).map(i => i.cloneNode(true));
        const clonesAfter = this.items.slice(0, this.slidesToShow).map(i => i.cloneNode(true));

        [...clonesBefore, ...this.items, ...clonesAfter].forEach(i => this.track.appendChild(i));
        this.allItems = Array.from(this.track.children);

        // Set widths
        const containerWidth = this.container.offsetWidth;
        const itemWidth = (containerWidth / this.slidesToShow) - this.options.gap;
        this.allItems.forEach(i => {
            i.style.minWidth = `${itemWidth}px`;
            i.style.marginRight = `${this.options.gap}px`;
        });

        // Reset position
        this.currentIndex = this.slidesToShow;
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
        this.track.style.transition = animate ? "transform 0.5s" : "none";
        const offset = -(this.currentIndex * (this.allItems[0].offsetWidth + this.options.gap));
        this.track.style.transform = `translateX(${offset}px)`;
        }

        next() {
        if (this.isTransitioning) return;
        this.isTransitioning = true;
        this.currentIndex += this.options.slidesToScroll;
        this.updatePosition(true);

        this.track.addEventListener("transitionend", () => {
            if (this.currentIndex >= this.items.length + this.slidesToShow) {
            this.currentIndex = this.slidesToShow;
            this.updatePosition(false);
            }
            this.isTransitioning = false;
            this.updateActiveDot();
        }, { once: true });
        }

        prev() {
        if (this.isTransitioning) return;
        this.isTransitioning = true;
        this.currentIndex -= this.options.slidesToScroll;
        this.updatePosition(true);

        this.track.addEventListener("transitionend", () => {
            if (this.currentIndex < this.slidesToShow) {
            this.currentIndex = this.items.length;
            this.updatePosition(false);
            }
            this.isTransitioning = false;
            this.updateActiveDot();
        }, { once: true });
        }

        updateDots() {
        if (!this.dotsContainer) return;
        this.dotsContainer.innerHTML = "";
        const totalDots = Math.ceil(this.items.length / this.options.slidesToScroll);
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement("button");
            dot.classList.add("carousel-dot");
            if (i === 0) dot.classList.add("active");
            dot.addEventListener("click", () => {
            this.currentIndex = this.slidesToShow + i * this.options.slidesToScroll;
            this.updatePosition(true);
            this.updateActiveDot();
            });
            this.dotsContainer.appendChild(dot);
        }
        }

        updateActiveDot() {
        if (!this.dotsContainer) return;
        const dots = this.dotsContainer.querySelectorAll(".carousel-dot");
        dots.forEach(dot => dot.classList.remove("active"));
        const activeIndex = Math.floor((this.currentIndex - this.slidesToShow) / this.options.slidesToScroll);
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