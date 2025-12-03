// Système de filtrage dynamique sans rechargement de page
class ProductFilter {
    constructor(formId, resultsContainerId) {
        this.form = document.getElementById(formId);
        this.resultsContainer = document.getElementById(resultsContainerId);
        this.loading = false;
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        // Écouter tous les changements de filtres
        this.form.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('change', () => this.applyFilters());
        });
        
        // Empêcher la soumission normale du formulaire
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.applyFilters();
        });
    }
    
    async applyFilters() {
        if (this.loading) return;
        
        this.loading = true;
        this.showLoading();
        
        // Récupérer les données du formulaire
        const formData = new FormData(this.form);
        const params = new URLSearchParams(formData);
        
        // Construire l'URL
        const currentUrl = new URL(window.location.href);
        const baseUrl = currentUrl.pathname;
        const fullUrl = `${baseUrl}?${params.toString()}`;
        
        try {
            // Faire la requête AJAX
            const response = await fetch(fullUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            
            if (!response.ok) throw new Error('Erreur réseau');
            
            const html = await response.text();
            
            // Parser le HTML reçu
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Extraire les produits
            const newResults = doc.getElementById(this.resultsContainer.id);
            if (newResults) {
                this.resultsContainer.innerHTML = newResults.innerHTML;
            }
            
            // Mettre à jour l'URL sans recharger
            window.history.pushState({}, '', fullUrl);
            
            // Scroll vers les résultats
            this.resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
        } catch (error) {
            console.error('Erreur lors du filtrage:', error);
            // En cas d'erreur, recharger normalement
            window.location.href = fullUrl;
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }
    
    showLoading() {
        if (this.resultsContainer) {
            this.resultsContainer.style.opacity = '0.5';
            this.resultsContainer.style.pointerEvents = 'none';
            
            // Ajouter un spinner si pas déjà présent
            if (!document.getElementById('filter-spinner')) {
                const spinner = document.createElement('div');
                spinner.id = 'filter-spinner';
                spinner.className = 'text-center py-5';
                spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>';
                this.resultsContainer.insertAdjacentElement('beforebegin', spinner);
            }
        }
    }
    
    hideLoading() {
        if (this.resultsContainer) {
            this.resultsContainer.style.opacity = '1';
            this.resultsContainer.style.pointerEvents = 'auto';
            
            const spinner = document.getElementById('filter-spinner');
            if (spinner) {
                spinner.remove();
            }
        }
    }
}

// Initialiser les filtres quand la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    // Filtres de la page catégorie
    if (document.getElementById('filterForm')) {
        new ProductFilter('filterForm', 'productResults');
    }
    
    // Filtres de la page recherche
    if (document.getElementById('searchFilterForm')) {
        new ProductFilter('searchFilterForm', 'searchResults');
    }
    
    // Filtres de la boutique officielle
    if (document.getElementById('boutiqueFilterForm')) {
        new ProductFilter('boutiqueFilterForm', 'boutiqueResults');
    }
    
    // Gestion du tri
    document.querySelectorAll('select[name="sort"]').forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form && form.id.includes('Sort')) {
                applySort(form);
            }
        });
    });
});

// Fonction pour appliquer le tri sans rechargement
async function applySort(form) {
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    const currentUrl = new URL(window.location.href);
    const baseUrl = currentUrl.pathname;
    const fullUrl = `${baseUrl}?${params.toString()}`;
    
    // Récupérer l'ID du conteneur de résultats
    let resultsId = 'productResults';
    if (window.location.pathname.includes('search')) {
        resultsId = 'searchResults';
    } else if (window.location.pathname.includes('boutique')) {
        resultsId = 'boutiqueResults';
    }
    
    const resultsContainer = document.getElementById(resultsId);
    if (!resultsContainer) {
        form.submit();
        return;
    }
    
    resultsContainer.style.opacity = '0.5';
    
    try {
        const response = await fetch(fullUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const newResults = doc.getElementById(resultsId);
        if (newResults) {
            resultsContainer.innerHTML = newResults.innerHTML;
        }
        
        window.history.pushState({}, '', fullUrl);
    } catch (error) {
        console.error('Erreur:', error);
        form.submit();
    } finally {
        resultsContainer.style.opacity = '1';
    }
}

// Gestion des liens de pagination AJAX
document.addEventListener('click', function(e) {
    if (e.target.closest('.pagination a')) {
        e.preventDefault();
        const link = e.target.closest('.pagination a');
        const url = link.getAttribute('href');
        
        if (url) {
            loadPage(url);
        }
    }
});

async function loadPage(url) {
    let resultsId = 'productResults';
    if (window.location.pathname.includes('search')) {
        resultsId = 'searchResults';
    } else if (window.location.pathname.includes('boutique')) {
        resultsId = 'boutiqueResults';
    }
    
    const resultsContainer = document.getElementById(resultsId);
    if (!resultsContainer) return;
    
    resultsContainer.style.opacity = '0.5';
    
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const newResults = doc.getElementById(resultsId);
        if (newResults) {
            resultsContainer.innerHTML = newResults.innerHTML;
        }
        
        window.history.pushState({}, '', url);
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
        console.error('Erreur:', error);
        window.location.href = url;
    } finally {
        resultsContainer.style.opacity = '1';
    }
}

