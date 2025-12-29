<div id="popup-launcher-root" data-version="{{ $version }}"></div>
<script type="application/json" id="popup-launcher-data">@json($payload)</script>

<!-- Modal Bootstrap pour les popups -->
<div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true" style="z-index: 999999999 !important; backdrop-filter: blur(8px) !important;">
    <div class="modal-dialog modal-dialog-centered" id="popupModalDialog">
        <div class="modal-content" style="max-width: 100%; max-height: 100%;">
            <div class="modal-body position-relative" id="popupModalBody">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Fermer" style="z-index: 10;"></button>
                <!-- Contenu du popup sera injecté ici -->
            </div>
            <div class="modal-footer justify-content-center align-items-center" id="popupModalFooter" style="display: none;">
                <!-- Boutons d'action seront injectés ici -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Popup System v{{ $version ?? 'dev' }} - Cache busting
(function() {
    if (typeof document === 'undefined' || typeof document.addEventListener !== 'function') {
        return;
    }
    
    // Fonction d'initialisation
    function initPopups() {
        const root = document.getElementById('popup-launcher-root');
        const modalElement = document.getElementById('popupModal');

        if (!root || !modalElement) {
            console.error('[Popups] Éléments DOM manquants');
            return;
        }

        // Vérifier la version pour forcer le rechargement si nécessaire
        const currentVersion = root.getAttribute('data-version');
        if (currentVersion) {
            const storedVersion = sessionStorage.getItem('popup_system_version');
            if (storedVersion && storedVersion !== currentVersion) {
                // Version différente détectée - les popups ont changé
                // Vider les stats en cache
                sessionStorage.removeItem('kazaria_popup_stats');
                // Vider aussi toutes les clés de session liées aux popups
                Object.keys(sessionStorage).forEach(key => {
                    if (key.startsWith('kazaria_popup_seen_session_')) {
                        sessionStorage.removeItem(key);
                    }
                });
                console.log('[Popups] Nouvelle version détectée, cache vidé');
            }
            sessionStorage.setItem('popup_system_version', currentVersion);
        }

        let popups = [];
        try {
            const dataScript = document.getElementById('popup-launcher-data');
            if (dataScript) {
                popups = JSON.parse(dataScript.textContent);
                console.log('[Popups] Popups chargés:', popups.length);
            } else {
                console.error('[Popups] Script de données introuvable');
                return;
            }
        } catch (e) {
            console.error('[Popups] Erreur de parsing JSON:', e);
            return;
        }

        if (!Array.isArray(popups) || popups.length === 0) {
            console.log('[Popups] Aucun popup à afficher');
            return;
        }

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
            if (!popup || !popup.slug) {
                return false;
            }
            
            const stats = state.stats[popup.slug] ?? { count: 0, lastShown: 0 };

            if (popup.max_impressions && stats.count >= popup.max_impressions) {
                return false;
            }

            const checker = frequencyChecks[popup.frequency] || frequencyChecks.always;
            return checker(popup, stats);
        }

        function updateStats(popup) {
            if (!popup || !popup.slug) return;
            
            const stats = state.stats[popup.slug] ?? { count: 0, lastShown: 0 };
            stats.count += 1;
            stats.lastShown = Date.now();
            state.stats[popup.slug] = stats;
            try {
                localStorage.setItem(storageKey, JSON.stringify(state.stats));
            } catch (e) {
                console.warn('[Popups] Erreur de sauvegarde', e);
            }
        }

        // Fonction helper pour appliquer les dimensions
        function applyDimensions(modalDialog, modalBody, modalContent, modalFooter, width, height) {
            if (!modalDialog || !modalBody) return;
            
            // Largeur - responsive avec max-width 100%
            if (width) {
                const w = parseInt(width);
                const maxW = Math.min(w, window.innerWidth);
                modalDialog.style.maxWidth = maxW + 'px';
                modalDialog.style.width = '100%';
                if (modalContent) {
                    modalContent.style.width = '100%';
                    modalContent.style.maxWidth = '100%';
                }
                modalBody.style.width = '100%';
                modalBody.style.maxWidth = '100%';
            } else {
                modalDialog.style.maxWidth = '100%';
                modalDialog.style.width = '100%';
                if (modalContent) {
                    modalContent.style.width = '100%';
                    modalContent.style.maxWidth = '100%';
                }
                modalBody.style.width = '100%';
                modalBody.style.maxWidth = '100%';
            }
            
            // Hauteur - responsive avec max-height 100%
            if (height && modalContent) {
                const maxScreenHeight = window.innerHeight * 0.95;
                const h = Math.min(parseInt(height), maxScreenHeight);
                modalContent.style.height = h + 'px';
                modalContent.style.maxHeight = '100%';
                
                // Calculer la hauteur du body (hauteur totale - footer)
                const footerHeight = modalFooter && modalFooter.style.display !== 'none' ? modalFooter.offsetHeight : 0;
                const bodyHeight = Math.max(100, h - footerHeight - 2);
                modalBody.style.height = bodyHeight + 'px';
                modalBody.style.maxHeight = '100%';
                modalBody.style.overflowY = 'auto';
                modalBody.style.boxSizing = 'border-box';
            } else if (modalContent) {
                modalContent.style.height = '';
                modalContent.style.maxHeight = '100%';
                modalBody.style.height = '';
                modalBody.style.maxHeight = '100%';
                modalBody.style.overflowY = '';
            }
        }

        function showPopup(popup) {
            const modalBody = document.getElementById('popupModalBody');
            const modalFooter = document.getElementById('popupModalFooter');
            const modalDialog = document.getElementById('popupModalDialog');
            const modalContent = modalDialog ? modalDialog.querySelector('.modal-content') : null;

            if (!modalBody || !modalDialog) {
                console.error('[Popups] Éléments DOM manquants');
                return;
            }

            // Réinitialiser les styles du modalBody
            modalBody.className = 'modal-body position-relative';
            modalBody.style.backgroundImage = '';
            modalBody.style.backgroundSize = '';
            modalBody.style.backgroundPosition = '';
            modalBody.style.backgroundRepeat = '';
            modalBody.style.minHeight = '';

            const layout = popup.layout || 'top-bottom';
            const hasImage = !!popup.image;
            const hasTitle = !!popup.title;
            const hasContent = !!popup.content;

            // Construire le contenu HTML selon le layout
            let bodyContent = '';
            const closeButtonHtml = '<button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Fermer" style="z-index: 10;"></button>';

            if (layout === 'stacked' && hasImage) {
                // Layout stacked : image en arrière-plan
                modalBody.className = 'modal-body position-relative p-0';
                modalBody.style.backgroundImage = `url(${popup.image})`;
                modalBody.style.backgroundSize = 'cover';
                modalBody.style.backgroundPosition = 'center';
                modalBody.style.backgroundRepeat = 'no-repeat';
                modalBody.style.minHeight = '300px';
                
                // Overlay semi-transparent pour améliorer la lisibilité
                bodyContent = '<div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.4);"></div>';
                let stackedContent = '<div class="position-relative p-4 text-white" style="z-index: 1; height: 100%; display: flex; flex-direction: column; justify-content: center;">';
                if (hasTitle) {
                    stackedContent += `<h5 class="modal-title mb-3">${popup.title}</h5>`;
                }
                if (hasContent) {
                    stackedContent += `<div>${popup.content}</div>`;
                }
                stackedContent += '</div>';
                
                // Si image_url existe, rendre toute la zone cliquable
                if (popup.image_url) {
                    bodyContent += `<a href="${popup.image_url}" target="_blank" rel="noopener noreferrer" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; display: block;">${stackedContent}</a>`;
                } else {
                    bodyContent += stackedContent;
                }
            } else if (layout === 'left-right' && hasImage) {
                // Layout left-right : image à gauche, contenu à droite
                modalBody.className = 'modal-body position-relative p-0';
                bodyContent = '<div class="d-flex flex-row" style="height: 100%;">';
                const imageLeft = `<img src="${popup.image}" alt="${popup.title || 'Popup'}" class="img-fluid h-100" style="object-fit: cover; width: 100%; height: 100%;">`;
                if (popup.image_url) {
                    bodyContent += `<div class="flex-shrink-0" style="width: 40%; min-width: 200px;"><a href="${popup.image_url}" target="_blank" rel="noopener noreferrer" style="display: block; width: 100%; height: 100%;">${imageLeft}</a></div>`;
                } else {
                    bodyContent += `<div class="flex-shrink-0" style="width: 40%; min-width: 200px;">${imageLeft}</div>`;
                }
                bodyContent += '<div class="flex-grow-1 p-4 d-flex flex-column justify-content-center">';
                if (hasTitle) {
                    bodyContent += `<h5 class="modal-title mb-3">${popup.title}</h5>`;
                }
                if (hasContent) {
                    bodyContent += `<div>${popup.content}</div>`;
                }
                bodyContent += '</div></div>';
            } else if (layout === 'right-left' && hasImage) {
                // Layout right-left : image à droite, contenu à gauche
                modalBody.className = 'modal-body position-relative p-0';
                bodyContent = '<div class="d-flex flex-row" style="height: 100%;">';
                bodyContent += '<div class="flex-grow-1 p-4 d-flex flex-column justify-content-center">';
                if (hasTitle) {
                    bodyContent += `<h5 class="modal-title mb-3">${popup.title}</h5>`;
                }
                if (hasContent) {
                    bodyContent += `<div>${popup.content}</div>`;
                }
                bodyContent += '</div>';
                const imageRight = `<img src="${popup.image}" alt="${popup.title || 'Popup'}" class="img-fluid h-100" style="object-fit: cover; width: 100%; height: 100%;">`;
                if (popup.image_url) {
                    bodyContent += `<div class="flex-shrink-0" style="width: 40%; min-width: 200px;"><a href="${popup.image_url}" target="_blank" rel="noopener noreferrer" style="display: block; width: 100%; height: 100%;">${imageRight}</a></div>`;
                } else {
                    bodyContent += `<div class="flex-shrink-0" style="width: 40%; min-width: 200px;">${imageRight}</div>`;
                }
                bodyContent += '</div>';
            } else {
                // Layout top-bottom (par défaut) : image en haut, contenu en bas
                // Si pas d'image, on utilise aussi ce layout
                modalBody.className = 'modal-body position-relative';
                if (hasImage) {
                    const imageTop = `<img src="${popup.image}" alt="${popup.title || 'Popup'}" class="img-fluid rounded" style="max-height: 300px; width: 100%; object-fit: cover;">`;
                    if (popup.image_url) {
                        bodyContent += `<div class="text-center mb-3"><a href="${popup.image_url}" target="_blank" rel="noopener noreferrer" style="display: inline-block;">${imageTop}</a></div>`;
                    } else {
                        bodyContent += `<div class="text-center mb-3">${imageTop}</div>`;
                    }
                }
                if (hasTitle) {
                    bodyContent += `<h5 class="modal-title mb-3">${popup.title}</h5>`;
                }
                if (hasContent) {
                    bodyContent += `<div>${popup.content}</div>`;
                }
            }

            // Injecter le contenu
            modalBody.innerHTML = closeButtonHtml + bodyContent;

            // Appliquer les dimensions (sans hauteur du footer pour l'instant)
            applyDimensions(modalDialog, modalBody, modalContent, null, popup.width, popup.height);

            // Boutons d'action - masquer le footer s'il n'y a pas de CTA
            if (modalFooter) {
                modalFooter.innerHTML = '';
                if (popup.cta_text && popup.cta_url) {
                    const ctaButton = document.createElement('a');
                    ctaButton.href = popup.cta_url;
                    ctaButton.target = '_blank';
                    ctaButton.rel = 'noopener noreferrer';
                    ctaButton.className = 'btn btn-primary';
                    ctaButton.textContent = popup.cta_text;
                    ctaButton.addEventListener('click', function() {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.hide();
                    });
                    modalFooter.appendChild(ctaButton);
                    modalFooter.style.display = 'flex';
                } else {
                    // Masquer le footer s'il est vide (fonctionne pour tous les layouts)
                    modalFooter.style.display = 'none';
                }
            }

            // Réajuster la hauteur maintenant que le footer est configuré
            if (popup.height) {
                applyDimensions(modalDialog, modalBody, modalContent, modalFooter, popup.width, popup.height);
            }

            // Afficher le modal
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
                updateStats(popup);
                state.active = true;
                
                // Réajuster la hauteur après l'affichage pour les dimensions réelles
                if (popup.height) {
                    setTimeout(() => {
                        applyDimensions(modalDialog, modalBody, modalContent, modalFooter, popup.width, popup.height);
                    }, 50);
                }
                
                // Gérer la fermeture
                modalElement.addEventListener('hidden.bs.modal', function onHidden() {
                    modalElement.removeEventListener('hidden.bs.modal', onHidden);
                    state.active = false;
                    processQueue();
                }, { once: true });
            } else {
                console.error('[Popups] Bootstrap Modal n\'est pas disponible');
            }
        }

        function processQueue() {
            if (state.active) return;

            const next = state.queue.shift();
            if (!next) return;

            showPopup(next);
        }

        // Traiter les popups
        console.log('[Popups] Traitement de', popups.length, 'popups');
        popups.forEach((popup, index) => {
            const delay = Math.max(0, Number(popup.delay) || 0);
            console.log('[Popups] Popup', index, ':', popup.slug, 'delay:', delay);
            setTimeout(() => {
                console.log('[Popups] Tentative d\'affichage:', popup.slug);
                if (canDisplay(popup)) {
                    console.log('[Popups] Popup ajouté à la queue:', popup.slug);
                    state.queue.push(popup);
                    processQueue();
                } else {
                    console.log('[Popups] Popup non affiché (filtre):', popup.slug);
                }
            }, delay * 1000);
        });

        // Fonction d'urgence pour fermer le popup
        window.forceClosePopup = function() {
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
            state.active = false;
            state.queue = [];
        };
    }
    
    // Initialiser immédiatement si le DOM est déjà chargé, sinon attendre DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPopups);
    } else {
        // Le DOM est déjà chargé, initialiser immédiatement
        initPopups();
    }
})();
</script>
@endpush
