<div id="popup-launcher-root" data-popups='<?php echo json_encode($payload, JSON_UNESCAPED_UNICODE, 512) ?>'></div>

<div class="modal fade popup-launcher-modal z-index-9x" id="popupLauncherModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="backdrop-filter: blur(8px) !important; -webkit-backdrop-filter: blur(8px) !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0" style="background: transparent!important;">
            <div class="modal-body p-0" data-popup-body>
                <div class="row g-0 position-relative" data-popup-row>
                    <div class="popup-launcher-image-wrapper d-none" data-popup-image-wrapper>
                        <!-- Close button -->
                         <div class="">
                            <button type="button" class="btn-close popup-close-btn" data-bs-dismiss="modal" aria-label="Fermer"></button>
                         </div>
                        <!-- End Close button -->
                        <img src="" alt="" class="w-100 h-100" data-popup-image>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" data-popup-content-wrapper>
                        <div class="p-4 w-100" data-popup-content-inner>
                            <h3 class="fw-bold mb-3 text-center" data-popup-title></h3>
                            <div class="popup-launcher-content mb-3 text-center" data-popup-content></div>
                            <div class="d-flex flex-wrap gap-2 justify-content-center" data-popup-actions></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    /* Flou de l'arrière-plan de la modal */
    .modal-backdrop.show {
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
    
    /* Cibler spécifiquement le backdrop quand la popup est ouverte */
    body.modal-open .modal-backdrop {
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
    }

    /* Règle globale pour toutes les images - object-fit contain obligatoire */
    .popup-launcher-modal img[data-popup-image],
    .popup-launcher-modal [data-popup-image-wrapper] img,
    .popup-launcher-modal .popup-launcher-image-wrapper img {
        object-fit: contain !important;
        object-position: center !important;
        min-height: 450px !important;
    }
    
    .popup-launcher-modal .modal-dialog {
        max-width: 90%;
        max-height: 90vh;
        height: auto;
        margin: 1.75rem auto;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
    }

    .popup-launcher-modal .modal-content {
        border-radius: 0;
        max-height: 90vh;
        overflow: hidden;
        position: relative;
        padding: 0 !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
    }

    .popup-launcher-modal .modal-body {
        max-height: 90vh;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding: 0 !important;
        margin: 0 !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
    }

    .popup-launcher-modal .popup-launcher-image-wrapper {
        overflow: hidden;
        min-height: 450px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 0;
    }
    
    /* S'assurer que le wrapper a une hauteur pour object-fit contain */
    .popup-launcher-modal .popup-launcher-image-wrapper:not(.d-none) {
        height: auto;
    }

    /* Image fixe pour les layouts horizontaux */
    .popup-launcher-modal .popup-launcher-image-wrapper.col-md-7 {
        position: sticky;
        left: 0;
        z-index: 10;
        align-self: flex-start;
        height: 100%;
    }
    
    /* Images pour layouts horizontaux (left-right, right-left) */
    .popup-launcher-modal .popup-launcher-image-wrapper.col-md-7 img[data-popup-image] {
        width: 100% !important;
        height: 100% !important;
        min-height: 450px !important;
        object-fit: contain !important;
        object-position: center !important;
    }
    
    /* Pour les layouts horizontaux, aligner l'image et le contenu en haut */
    .popup-launcher-modal .row:not(.layout-stacked):not(.layout-top-bottom) {
        align-items: flex-start;
    }
    
    .popup-launcher-modal .row:not(.layout-stacked):not(.layout-top-bottom) [data-popup-content-wrapper] {
        align-self: stretch;
    }

    /* Règle générale pour toutes les images - object-fit contain */
    .popup-launcher-modal img[data-popup-image] {
        object-fit: contain !important;
        object-position: center !important;
        min-height: 450px !important;
        display: block !important;
    }


    /* Layout superposé (overlay) */
    .popup-launcher-modal [data-popup-row].layout-stacked {
        position: relative;
    }

    .popup-launcher-modal [data-popup-row].layout-stacked .popup-launcher-image-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        /* Pas de sticky pour stacked, c'est un overlay */
    }
    
    /* Images pour layout stacked */
    .popup-launcher-modal [data-popup-row].layout-stacked img[data-popup-image] {
        width: 100% !important;
        height: 100% !important;
        min-height: 450px !important;
        object-fit: contain !important;
        object-position: center !important;
    }

    .popup-launcher-modal [data-popup-row].layout-stacked [data-popup-content-wrapper] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 2;
        background: transparent;
    }

    .popup-launcher-modal [data-popup-row].layout-stacked [data-popup-content-inner] {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 0.5rem;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        max-width: 90%;
        margin: 0 auto;
    }

    /* Masquer le fond blanc si pas de contenu texte */
    .popup-launcher-modal [data-popup-content-inner].d-none {
        display: none !important;
        background: transparent !important;
        padding: 0 !important;
    }

    .popup-launcher-modal [data-popup-content-inner]:has([data-popup-title].d-none):has([data-popup-content].d-none) {
        background: transparent !important;
        padding: 0 !important;
    }

    /* Masquer le wrapper parent si pas de contenu texte */
    .popup-launcher-modal [data-popup-content-wrapper]:has([data-popup-content-inner][style*="display: none"]) {
        background: transparent !important;
    }

    .popup-launcher-modal .row:not(.layout-top-bottom) {
        /*min-height: 200px;*/
    }

    .popup-launcher-modal [data-popup-row] {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    .popup-launcher-modal .layout-top-bottom {
        /*min-height: 200px;*/
    }

    /* Pour la disposition top-bottom, permettre le scroll vertical sur le modal */
    .popup-launcher-modal .modal-content.layout-top-bottom {
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Pour top-bottom, l'image doit pouvoir scroller horizontalement */
    .popup-launcher-modal .layout-top-bottom .popup-launcher-image-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
    }

    /* Images pour layout top-bottom */
    .popup-launcher-modal .layout-top-bottom img[data-popup-image] {
        width: 100% !important;
        max-width: 100% !important;
        max-height: 70vh !important;
        height: auto !important;
        min-height: 450px !important;
        object-fit: contain !important;
        object-position: center !important;
    }
    
    /* Images sans layout spécifique (fallback) */
    .popup-launcher-modal .popup-launcher-image-wrapper img[data-popup-image] {
        width: 100% !important;
        height: auto !important;
        max-height: 70vh !important;
        min-height: 450px !important;
        object-fit: contain !important;
        object-position: center !important;
    }

    .popup-launcher-modal [data-popup-content-wrapper].col-md-5 {
        /*min-height: 200px;*/
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .popup-launcher-modal [data-popup-content-inner] {
        width: 100%;
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .popup-launcher-modal .popup-cta-btn {
        background-color: var(--main-color) !important;
        color: white !important;
        border: none !important;
    }

    .popup-launcher-modal .popup-cta-btn:hover {
        background-color: #d93e1f !important;
        color: white !important;
    }

    .popup-launcher-modal .popup-close-btn {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 10;
        margin: 0;
        padding: 0.5rem;
        background-color: rgba(255, 255, 255, 0.8);
        border-radius: 0 0 0 50%;
        opacity: 0.8;
        transition: opacity 0.2s ease;
    }

    .popup-launcher-modal .popup-close-btn:hover {
        opacity: 1;
        background-color: rgba(255, 255, 255, 1);
    }

    @media (max-width: 768px) {
        .popup-launcher-modal .modal-content {
            border-radius: 14px;
        }

        .popup-launcher-modal .popup-launcher-image-wrapper {
            /*min-height: 200px;
            max-height: 250px;*/
        }

        .popup-launcher-modal .row {
            min-height: auto;
        }

        .popup-launcher-modal .col-md-5 {
            min-height: auto;
        }

        .popup-launcher-modal .col-md-5 .p-4 {
            padding: 1.5rem !important;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function() {
    // Vérifier que document est disponible
    if (typeof document === 'undefined' || typeof document.addEventListener !== 'function') {
        console.error('[Popups] Document ou addEventListener non disponible');
        return;
    }
    
document.addEventListener('DOMContentLoaded', function() {
    const root = document.getElementById('popup-launcher-root');
    const modalElement = document.getElementById('popupLauncherModal');

    if (!root || !modalElement) {
        return;
    }

    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        console.warn('[Popups] Bootstrap Modal indisponible');
        return;
    }

    let popups = [];
    try {
        popups = JSON.parse(root.dataset.popups ?? '[]');
    } catch (e) {
        console.error('[Popups] impossible de parser les données', e);
        return;
    }

    if (!Array.isArray(popups) || popups.length === 0) {
        console.info('[Popups] aucune popup active');
        return;
    }

    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false,
    });

    const titleEl = modalElement.querySelector('[data-popup-title]');
    const contentEl = modalElement.querySelector('[data-popup-content]');
    const imageWrapper = modalElement.querySelector('[data-popup-image-wrapper]');
    const imageEl = modalElement.querySelector('[data-popup-image]');
    const actionsEl = modalElement.querySelector('[data-popup-actions]');
    const contentWrapper = modalElement.querySelector('[data-popup-content-wrapper]');
    const popupRow = modalElement.querySelector('[data-popup-row]');
    const modalContent = modalElement.querySelector('.modal-content');
    const modalBody = modalElement.querySelector('.modal-body');

    const storageKey = 'kazaria_popup_stats';
    const sessionKeyPrefix = 'kazaria_popup_seen_session_';
    const state = {
        active: false,
        queue: [],
        stats: {},
        visitSeen: new Set(),
    };

    try {
        state.stats = JSON.parse(localStorage.getItem(storageKey) ?? '{}');
    } catch (e) {
        state.stats = {};
    }

    const frequencyChecks = {
        once_per_session: (popup) => {
            const key = sessionKeyPrefix + popup.slug;
            if (sessionStorage.getItem(key)) {
                return false;
            }
            sessionStorage.setItem(key, '1');
            return true;
        },
        once_per_day: (popup, stats) => {
            const lastShown = stats?.lastShown ?? 0;
            return Date.now() - lastShown >= 24 * 60 * 60 * 1000;
        },
        once_per_visit: (popup) => {
            if (state.visitSeen.has(popup.slug)) {
                return false;
            }
            state.visitSeen.add(popup.slug);
            return true;
        },
        always: () => true,
    };

    function canDisplay(popup) {

        const stats = state.stats[popup.slug] ?? { count: 0, lastShown: 0 };

        if (popup.max_impressions && stats.count >= popup.max_impressions) {
            return false;
        }

        const checker = frequencyChecks[popup.frequency] || frequencyChecks.always;
        return checker(popup, stats);
    }

    function updateStats(popup) {
        const stats = state.stats[popup.slug] ?? { count: 0, lastShown: 0 };
        stats.count += 1;
        stats.lastShown = Date.now();
        state.stats[popup.slug] = stats;
        try {
            localStorage.setItem(storageKey, JSON.stringify(state.stats));
        } catch (e) {
            console.warn('[Popups] impossible de sauvegarder les stats', e);
        }
    }

    function buildActions(popup) {
        actionsEl.innerHTML = '';

        if (popup.cta_text && popup.cta_url) {
            const cta = document.createElement('a');
            cta.href = popup.cta_url;
            cta.target = '_blank';
            cta.rel = 'noopener noreferrer';
            cta.className = 'btn popup-cta-btn';
            cta.textContent = popup.cta_text;
            cta.setAttribute('data-bs-dismiss', 'modal');
            actionsEl.appendChild(cta);
        }
    }

    function applyLayout(layout, hasImage) {
        if (!popupRow || !imageWrapper || !contentWrapper) return;

        // Réinitialiser toutes les classes
        imageWrapper.className = 'popup-launcher-image-wrapper';
        contentWrapper.className = 'd-flex align-items-center justify-content-center';
        popupRow.className = 'row g-0 position-relative';
        if (modalContent) {
            modalContent.classList.remove('layout-top-bottom');
        }
        if (modalBody) {
            modalBody.classList.remove('layout-top-bottom');
        }

        if (!hasImage) {
            imageWrapper.classList.add('d-none');
            contentWrapper.classList.add('col-12');
            return;
        }

        imageWrapper.classList.remove('d-none');
        
        // Layout superposé (overlay)
        if (layout === 'stacked') {
            popupRow.classList.add('layout-stacked');
            imageWrapper.classList.add('col-12');
            contentWrapper.classList.add('col-12');
            return;
        }

        const layoutConfig = {
            'left-right': {
                row: 'row',
                image: 'col-md-7',
                content: 'col-md-5'
            },
            'right-left': {
                row: 'row flex-row-reverse',
                image: 'col-md-7',
                content: 'col-md-5'
            },
            'top-bottom': {
                row: 'flex-column',
                image: 'col-12',
                content: 'col-12'
            }
        };

        const config = layoutConfig[layout] || layoutConfig['left-right'];
        
        if (config.row === 'flex-column') {
            popupRow.classList.add('layout-top-bottom');
            // Pour top-bottom, ajouter la classe sur modal-content pour activer le scroll
            if (layout === 'top-bottom' && modalContent) {
                modalContent.classList.add('layout-top-bottom');
            }
            if (layout === 'top-bottom' && modalBody) {
                modalBody.classList.add('layout-top-bottom');
            }
        } else {
            popupRow.classList.add('row');
            if (config.row.includes('flex-row-reverse')) {
                popupRow.classList.add('flex-row-reverse');
            }
            // Retirer la classe si ce n'est pas top-bottom
            if (modalContent) {
                modalContent.classList.remove('layout-top-bottom');
            }
            if (modalBody) {
                modalBody.classList.remove('layout-top-bottom');
            }
        }

        imageWrapper.classList.add(config.image);
        contentWrapper.classList.add(config.content);
    }

    function showPopup(popup) {
        const hasTitle = popup.title && typeof popup.title === 'string' && popup.title.trim().length > 0;
        const hasContent = popup.content && typeof popup.content === 'string' && popup.content.trim().length > 0;
        const hasTextContent = hasTitle || hasContent;

        if (titleEl) {
            titleEl.textContent = popup.title || '';
            titleEl.classList.toggle('d-none', !hasTitle);
        }

        if (contentEl) {
            contentEl.innerHTML = popup.content || '';
            contentEl.classList.toggle('d-none', !hasContent);
        }

        const contentInner = modalElement.querySelector('[data-popup-content-inner]');
        const contentWrapperEl = modalElement.querySelector('[data-popup-content-wrapper]');
        
        // Masquer le wrapper de contenu si ni titre ni contenu
        if (!hasTextContent) {
            if (contentInner) {
                contentInner.style.display = 'none';
                contentInner.style.background = 'transparent';
                contentInner.style.padding = '0';
            }
            if (contentWrapperEl) {
                contentWrapperEl.style.display = 'none';
            }
        } else {
            if (contentInner) {
                contentInner.style.display = '';
                contentInner.style.background = '';
                contentInner.style.padding = '';
            }
            if (contentWrapperEl) {
                contentWrapperEl.style.display = '';
            }
        }

        const layout = popup.layout || 'left-right';
        const hasImage = !!popup.image;

        if (hasImage) {
            imageEl.src = popup.image;
            imageEl.alt = popup.title || 'Popup';
        } else {
            imageEl.removeAttribute('src');
        }

        applyLayout(layout, hasImage);

        buildActions(popup);
        updateStats(popup);
        modal.show();
    }

    modalElement.addEventListener('hidden.bs.modal', () => {
        state.active = false;
        processQueue();
    });

    function processQueue() {
        if (state.active) {
            console.info('[Popups] modal déjà actif, attente…');
            return;
        }

        const next = state.queue.shift();
        if (!next) {
            console.info('[Popups] file vide');
            return;
        }

        console.info('[Popups] affichage modal', next.slug);
        state.active = true;
        showPopup(next);
    }

    console.info('[Popups] détectées :', popups);
    console.info('[Popups] nombre de popups:', popups.length);

    popups.forEach((popup) => {
        console.info('[Popups] traitement popup:', {
            slug: popup.slug,
            title: popup.title,
            content: popup.content ? (popup.content.substring(0, 50) + '...') : 'vide',
            hasTitle: popup.title && typeof popup.title === 'string' && popup.title.trim().length > 0,
            hasContent: popup.content && typeof popup.content === 'string' && popup.content.trim().length > 0
        });
        const delay = Math.max(0, Number(popup.delay) || 0);
        setTimeout(() => {
            if (canDisplay(popup)) {
                console.info('[Popups] ajout à la file', popup.slug, 'delay', delay);
                state.queue.push(popup);
                processQueue();
            } else {
                console.info('[Popups] filtrée par fréquence/limite ou titre/contenu', popup.slug);
            }
        }, delay * 1000);
    });
    });
})();
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\components\popup-launcher.blade.php ENDPATH**/ ?>