/**
 * Système d'autocomplétion pour la recherche (Desktop et Mobile)
 */
class SearchAutocomplete {
    constructor() {
        this.debounceTimer = null;
        this.selectedIndex = -1;
        this.suggestions = [];
        
        // Initialiser pour desktop
        this.initDesktop();
        
        // Initialiser pour mobile
        this.initMobile();
    }

    initDesktop() {
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');
        const clearButton = document.getElementById('clearSearch');
        const searchForm = document.getElementById('searchForm');
        
        if (searchInput && searchSuggestions && searchForm) {
            this.setupAutocomplete(searchInput, searchSuggestions, clearButton, searchForm);
        }
    }

    initMobile() {
        const mobileSearchInput = document.getElementById('mobileSearchInput');
        const mobileSearchSuggestions = document.getElementById('mobileSearchSuggestions');
        const mobileClearButton = document.getElementById('mobileClearSearch');
        const mobileSearchForm = document.getElementById('mobileSearchForm');
        
        if (mobileSearchInput && mobileSearchSuggestions && mobileSearchForm) {
            this.setupAutocomplete(mobileSearchInput, mobileSearchSuggestions, mobileClearButton, mobileSearchForm);
        }
    }

    setupAutocomplete(input, suggestions, clearButton, form) {
        // Événements sur l'input de recherche
        input.addEventListener('input', (e) => this.handleInput(e, suggestions, clearButton));
        input.addEventListener('keydown', (e) => this.handleKeydown(e, suggestions));
        input.addEventListener('focus', () => this.showSuggestions(suggestions, input));
        input.addEventListener('blur', (e) => this.handleBlur(e, suggestions));

        // Bouton de suppression
        if (clearButton) {
            clearButton.addEventListener('click', () => this.clearSearch(input, suggestions, clearButton));
        }

        // Fermer les suggestions en cliquant ailleurs
        document.addEventListener('click', (e) => {
            if (!form.contains(e.target)) {
                this.hideSuggestions(suggestions);
            }
        });
    }

    handleInput(e, suggestions, clearButton) {
        const query = e.target.value.trim();
        
        // Afficher/masquer le bouton de suppression
        this.toggleClearButton(query.length > 0, clearButton);
        
        // Débounce pour éviter trop de requêtes
        clearTimeout(this.debounceTimer);
        
        if (query.length >= 2) {
            this.debounceTimer = setTimeout(() => {
                this.fetchSuggestions(query, suggestions);
            }, 300);
        } else {
            this.hideSuggestions(suggestions);
        }
    }

    handleKeydown(e, suggestions) {
        if (!suggestions.classList.contains('d-none')) {
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.navigateSuggestions(1, suggestions);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.navigateSuggestions(-1, suggestions);
                    break;
                case 'Enter':
                    e.preventDefault();
                    this.selectSuggestion(suggestions);
                    break;
                case 'Escape':
                    this.hideSuggestions(suggestions);
                    break;
            }
        }
    }

    handleBlur(e, suggestions) {
        // Attendre un peu avant de masquer pour permettre les clics
        setTimeout(() => {
            if (!suggestions.contains(document.activeElement)) {
                this.hideSuggestions(suggestions);
            }
        }, 200);
    }

    async fetchSuggestions(query, suggestions) {
        try {
            const response = await fetch(`/api/search-suggestions?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.suggestions = data;
            this.displaySuggestions(data, suggestions);
            
        } catch (error) {
            console.error('Erreur lors de la récupération des suggestions:', error);
            this.hideSuggestions(suggestions);
        }
    }

    displaySuggestions(suggestions, suggestionsContainer) {
        if (suggestions.length === 0) {
            this.hideSuggestions(suggestionsContainer);
            return;
        }

        let html = '';
        
        suggestions.forEach((suggestion, index) => {
            const isSelected = index === this.selectedIndex ? 'bg-light' : '';
            
            html += `
                <div class="suggestion-item p-2 border-bottom cursor-pointer ${isSelected}" 
                     data-index="${index}" 
                     data-url="${suggestion.url}"
                     data-type="${suggestion.type}">
                    <div class="d-flex align-items-center">
                        <i class="${suggestion.icon} text-muted me-2"></i>
                        <div class="flex-grow-1">
                            <div class="fw-medium text-dark">${this.highlightMatch(suggestion.text, this.getCurrentQuery())}</div>
                            <small class="text-muted">${suggestion.category}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        suggestionsContainer.innerHTML = html;
        this.showSuggestions(suggestionsContainer);

        // Ajouter les événements de clic
        suggestionsContainer.querySelectorAll('.suggestion-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                this.selectedIndex = index;
                this.selectSuggestion(suggestionsContainer);
            });
            
            item.addEventListener('mouseenter', () => {
                this.selectedIndex = index;
                this.updateSelectedItem(suggestionsContainer);
            });
        });
    }

    getCurrentQuery() {
        const desktopInput = document.getElementById('searchInput');
        const mobileInput = document.getElementById('mobileSearchInput');
        
        if (desktopInput && desktopInput.value) {
            return desktopInput.value;
        } else if (mobileInput && mobileInput.value) {
            return mobileInput.value;
        }
        return '';
    }

    highlightMatch(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    navigateSuggestions(direction, suggestions) {
        if (this.suggestions.length === 0) return;

        this.selectedIndex += direction;
        
        if (this.selectedIndex < 0) {
            this.selectedIndex = this.suggestions.length - 1;
        } else if (this.selectedIndex >= this.suggestions.length) {
            this.selectedIndex = 0;
        }

        this.updateSelectedItem(suggestions);
    }

    updateSelectedItem(suggestions) {
        suggestions.querySelectorAll('.suggestion-item').forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('bg-light');
            } else {
                item.classList.remove('bg-light');
            }
        });
    }

    selectSuggestion(suggestions) {
        if (this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
            const suggestion = this.suggestions[this.selectedIndex];
            
            if (suggestion.type === 'product' || suggestion.type === 'category' || suggestion.type === 'subcategory') {
                // Rediriger directement vers la page
                window.location.href = suggestion.url;
            } else {
                // Pour les marques et mots-clés, remplir l'input et soumettre
                const desktopInput = document.getElementById('searchInput');
                const mobileInput = document.getElementById('mobileSearchInput');
                
                if (desktopInput) {
                    desktopInput.value = suggestion.text;
                    document.getElementById('searchForm').submit();
                } else if (mobileInput) {
                    mobileInput.value = suggestion.text;
                    document.getElementById('mobileSearchForm').submit();
                }
            }
        }
    }

    showSuggestions(suggestions, input) {
        const query = input ? input.value.trim() : this.getCurrentQuery();
        if (query.length >= 2) {
            suggestions.classList.remove('d-none');
        }
    }

    hideSuggestions(suggestions) {
        suggestions.classList.add('d-none');
        this.selectedIndex = -1;
    }

    toggleClearButton(show, clearButton) {
        if (clearButton) {
            clearButton.style.display = show ? 'block' : 'none';
        }
    }

    clearSearch(input, suggestions, clearButton) {
        input.value = '';
        this.hideSuggestions(suggestions);
        this.toggleClearButton(false, clearButton);
        input.focus();
    }
}

// Initialiser l'autocomplétion quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    new SearchAutocomplete();
});