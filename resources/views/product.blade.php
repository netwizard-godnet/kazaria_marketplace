@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
        <section class="bg-light py-2">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb" class="">
                    <ol class="breadcrumb" class="">
                        <li class="breadcrumb-item mb-0"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                        @if($product->categories && $product->categories->count() > 0)
                            <li class="breadcrumb-item mb-0"><a href="{{ route('categorie', $product->categories->first()->slug) }}" class="fs-7">{{ $product->categories->first()->name }}</a></li>
                        @elseif($product->category)
                            <li class="breadcrumb-item mb-0"><a href="{{ route('categorie', $product->category->slug) }}" class="fs-7">{{ $product->category->name }}</a></li>
                        @else
                            <li class="breadcrumb-item mb-0"><span class="fs-7">Produits</span></li>
                        @endif
                        <li class="breadcrumb-item mb-0 active fs-7" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
        </section>
        <!-- SECTION BREADCRUMB END -->

        <!-- SECTION -->
        <section class="pb-5 border-top">
            <div class="container-fluid bg-light">
                <div class="container py-2">
                    <div class="row g-4">
                        <div class="col-md-5 bg-light-subtle p-4">
                            <div class="row g-3 d-flex align-items-center justify-content-center">
                                <div class="col-12">
                                    @php
                                        $mainImageUrl = asset('images/produit.jpg');
                                        
                                        // Priorité 1: Champ image principale
                                        if ($product->image) {
                                            if (filter_var($product->image, FILTER_VALIDATE_URL)) {
                                                $mainImageUrl = $product->image;
                                            } elseif (strpos($product->image, 'storage/') === 0) {
                                                $mainImageUrl = asset($product->image);
                                            } elseif (strpos($product->image, 'products/') === 0) {
                                                $mainImageUrl = asset('storage/' . $product->image);
                                            } elseif (str_starts_with($product->image, 'images/')) {
                                                $mainImageUrl = asset($product->image);
                                            } else {
                                                $mainImageUrl = asset($product->image);
                                            }
                                        // Priorité 2: Première image du tableau
                                        } elseif ($product->images && is_array($product->images) && count($product->images) > 0) {
                                            $firstImg = $product->images[0];
                                            if (filter_var($firstImg, FILTER_VALIDATE_URL)) {
                                                $mainImageUrl = $firstImg;
                                            } elseif (strpos($firstImg, 'products/') === 0) {
                                                $mainImageUrl = asset('storage/' . $firstImg);
                                            } elseif (str_starts_with($firstImg, 'images/')) {
                                                $mainImageUrl = asset($firstImg);
                                            } else {
                                                $mainImageUrl = asset('images/' . $firstImg);
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $mainImageUrl }}" 
                                         id="mainProductImage" 
                                         class="w-100 h-400px object-fit-contain" 
                                         alt="{{ $product->name }}"
                                         style="cursor: zoom-in;"
                                         onclick="openImageModal()">
                                </div>
                                @php
                                    // Construire la liste complète des images (image principale + images additionnelles)
                                    $allImages = [];
                                    
                                    // Ajouter l'image principale si elle existe
                                    if ($product->image) {
                                        $allImages[] = $product->image;
                                    }
                                    
                                    // Ajouter les autres images (sans dupliquer l'image principale)
                                    if ($product->images && is_array($product->images)) {
                                        foreach ($product->images as $img) {
                                            if ($img !== $product->image && $img) {
                                                $allImages[] = $img;
                                            }
                                        }
                                    }
                                @endphp
                                @if(count($allImages) > 0)
                                    @foreach($allImages as $index => $image)
                                    <div class="col-2">
                                        @php
                                            $thumbUrl = asset('images/produit.jpg');
                                            if (filter_var($image, FILTER_VALIDATE_URL)) {
                                                $thumbUrl = $image;
                                            } elseif (strpos($image, 'products/') === 0) {
                                                $thumbUrl = asset('storage/' . $image);
                                            } elseif (str_starts_with($image, 'images/')) {
                                                $thumbUrl = asset($image);
                                            } else {
                                                $thumbUrl = asset('images/' . $image);
                                            }
                                        @endphp
                                        <img src="{{ $thumbUrl }}" 
                                             class="w-100 h-100 object-fit-contain product-thumbnail {{ $index === 0 ? 'active' : '' }}" 
                                             alt="{{ $product->name }}"
                                             style="cursor: pointer; border: 2px solid {{ $index === 0 ? 'var(--main-color)' : 'transparent' }};"
                                             onclick="changeMainImage('{{ $thumbUrl }}', this)">
                                    </div>
                                    @endforeach
                                @else
                                @for ($i = 0; $i < 6; $i++)
                                <div class="col-2">
                                        <img src="{{ $mainImageUrl }}" 
                                             class="w-100 h-100 object-fit-contain product-thumbnail {{ $i === 0 ? 'active' : '' }}" 
                                             alt="{{ $product->name }}"
                                             style="cursor: pointer; border: 2px solid {{ $i === 0 ? 'var(--main-color)' : 'transparent' }};"
                                             onclick="changeMainImage('{{ $mainImageUrl }}', this)">
                                </div>
                                @endfor
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 p-4">
                            <div>
                                @if($product->is_featured)
                                <a class="btn btn-sm fs-8 blue-bg text-white px-1 py-0">Boutique Officielle</a>
                                @endif
                                @if($product->old_price && $product->old_price > $product->price)
                                <span class="badge bg-danger ms-2">-{{ round((($product->old_price - $product->price) / $product->old_price) * 100) }}%</span>
                                @endif
                                <!-- NOM PRODUIT -->
                                <p class="mb-0 mt-3 fs-5">{{ $product->name }}</p>
                                <p class="mb-0 mt-3 fs-8">
                                    @if($product->brand)
                                    Marque : <span class="fw-bold">{{ $product->brand }}</span> | 
                                    @endif
                                    @if($product->categories && $product->categories->count() > 0)
                                        <span>Catégories : {{ $product->categories->pluck('name')->implode(', ') }}</span>
                                    @elseif($product->category)
                                        <span>{{ $product->category->name }}</span>
                                    @else
                                        <span>Non catégorisé</span>
                                    @endif
                                    @if($product->subcategories && $product->subcategories->count() > 0)
                                        <br><small class="text-muted">Sous-catégories : {{ $product->subcategories->pluck('name')->implode(', ') }}</small>
                                    @elseif($product->subcategory)
                                        <br><small class="text-muted">{{ $product->subcategory->name }}</small>
                                    @endif
                                </p>
                                <!-- NOM PRODUIT END -->
                                <hr>
                                <div class="d-flex align-items-center justify-content-start" id="product-price-container">
                                    @php
                                        // Toujours utiliser le prix du produit par défaut (pas de variation pré-sélectionnée)
                                        $initialPrice = $product->price;
                                        $initialOldPrice = ($product->old_price && $product->old_price > $product->price) ? $product->old_price : null;
                                        if ($initialOldPrice) {
                                            $initialPrice = $product->price; // Prix promo
                                        }
                                    @endphp
                                    @if($initialOldPrice && $initialOldPrice > $initialPrice)
                                        {{-- Produit en promo: price = prix actuel, old_price = ancien prix --}}
                                        <span class="fs-3 orange-color fw-bold text-nowrap me-2" id="current-price">{{ number_format($initialPrice, 0, ',', ' ') }} FCFA</span>
                                        <span class="fs-6 text-decoration-line-through text-secondary text-nowrap" id="old-price">{{ number_format($initialOldPrice, 0, ',', ' ') }} FCFA</span>
                                    @else
                                        {{-- Produit sans promo: afficher seulement le prix --}}
                                        <span class="fs-3 orange-color fw-bold text-nowrap" id="current-price">{{ number_format($initialPrice, 0, ',', ' ') }} FCFA</span>
                                        <span class="fs-6 text-decoration-line-through text-secondary text-nowrap" id="old-price" style="display: none;"></span>
                                    @endif
                                </div>
                                <div class="mb-3" id="savings-container" style="{{ ($initialOldPrice && $initialOldPrice > $initialPrice) ? '' : 'display: none;' }}">
                                    <span class="fs-8 fw-bold orange-color mb-3" id="savings-amount">Vous avez épargné {{ number_format(($initialOldPrice ?? 0) - ($initialPrice ?? 0), 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="hstack gap-1">
                                    <div id="priceStars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star {{ $i <= floor($product->rating) ? 'text-warning' : 'text-secondary' }} fs-7"></i>
                                        @endfor
                                    </div>
                                    <p class="mb-0 fs-8" id="reviewsCountText">
                                        @if($product->reviews_count > 0)
                                            (<span id="reviewsCount">{{ $product->reviews_count }}</span> avis)
                                        @else
                                            (Pas d'avis pour le moment)
                                        @endif
                                    </p>
                                </div>
                                <hr>
                                
                                <!-- Attributs du produit -->
                                @if($product->attributeValues->count() > 0)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Options disponibles :</h6>
                                    <form id="attributesForm">
                                        @php
                                            $groupedAttributes = $product->attributeValues->groupBy('attribute.name');
                                            
                                            // Vérifier si le produit a des variations
                                            $hasVariations = $product->variations && $product->variations->count() > 0;
                                            
                                            // Si le produit a des variations, tous les attributs doivent être des radios (un seul choix)
                                            // Sinon, respecter le type défini dans la base de données
                                        @endphp
                                        @foreach($groupedAttributes as $attributeName => $values)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ $attributeName }}:</label>
                                            <div class="row">
                                                @foreach($values as $value)
                                                <div class="col-auto">
                                                    <div class="form-check">
                                                        @php
                                                            // Si le produit a des variations, forcer tous les attributs à être des radios
                                                            // Sinon, utiliser le type défini dans la base de données
                                                            $attributeType = $values->first()->attribute->type;
                                                            $useRadio = ($hasVariations || $attributeType === 'radio') ? true : false;
                                                        @endphp
                                                        @if($useRadio)
                                                        <input class="form-check-input attribute-radio" 
                                                               type="radio" 
                                                               name="attribute_{{ $value->attribute->id }}" 
                                                               value="{{ $value->id }}" 
                                                               id="attr_{{ $value->id }}"
                                                               data-attribute="{{ $value->attribute->name }}"
                                                               data-value="{{ $value->value }}">
                                                        @else
                                                        <input class="form-check-input attribute-checkbox" 
                                                               type="checkbox" 
                                                               name="attribute_{{ $value->attribute->id }}[]" 
                                                               value="{{ $value->id }}" 
                                                               id="attr_{{ $value->id }}"
                                                               data-attribute="{{ $value->attribute->name }}"
                                                               data-value="{{ $value->value }}">
                                                        @endif
                                                        <label class="form-check-label" for="attr_{{ $value->id }}">
                                                            {{ $value->value }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endforeach
                                    </form>
                                </div>
                                @endif
                                
                                <div class="mb-3" id="stock-container">
                                    @php
                                        // Toujours utiliser le stock du produit par défaut (pas de variation pré-sélectionnée)
                                        $initialStock = $product->stock;
                                    @endphp
                                    @if($initialStock > 0)
                                        <span class="badge bg-success" id="stock-badge">En stock (<span id="stock-count">{{ number_format($initialStock, 0, ',', ' ') }}</span> disponibles)</span>
                                    @else
                                        <span class="badge bg-danger" id="stock-badge">Rupture de stock</span>
                                    @endif
                                </div>
                                
                                @if($initialStock > 0)
                                <!-- Sélecteur de quantité -->
                                <div class="mb-3" id="quantity-container">
                                    <label for="quantityInput" class="form-label fw-bold">Quantité:</label>
                                    <div class="input-group" style="max-width: 150px;">
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" id="quantityInput" value="1" min="1" max="{{ $initialStock }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <button class="btn orange-bg text-white flex-grow-1" onclick="addToCartFromProduct()">
                                        <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
                                    </button>
                                    <button class="btn btn-outline-danger favorite-btn-product" id="favoriteBtn" onclick="toggleFavoriteProduct()" style="width: 50px;">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>
                                @else
                                <div class="mb-3">
                                    <span class="badge bg-danger">Rupture de stock</span>
                                </div>
                                <button class="btn btn-secondary" disabled>
                                    <i class="bi bi-cart-x me-2"></i>Produit indisponible
                                </button>
                                @endif
                                
                                <script>
                                    // Changer la quantité
                                    function changeQuantity(change) {
                                        const input = document.getElementById('quantityInput');
                                        if (!input) return;
                                        
                                        const currentValue = parseInt(input.value);
                                        // Obtenir le stock max depuis la variation actuelle ou le produit
                                        let maxStock = {{ $product->stock }};
                                        @if($product->variations && $product->variations->count() > 0)
                                        if (typeof getCurrentVariation === 'function') {
                                            const variation = getCurrentVariation();
                                            if (variation) {
                                                maxStock = variation.stock;
                                            }
                                        }
                                        @endif
                                        
                                        let newValue = currentValue + change;
                                        
                                        if (newValue < 1) newValue = 1;
                                        if (newValue > maxStock) {
                                            if (typeof showToast === 'function') {
                                                showToast('error', 'Stock maximum atteint');
                                            } else if (typeof showNotification === 'function') {
                                                showNotification('warning', 'Stock maximum atteint');
                                            }
                                            newValue = maxStock;
                                        }
                                        
                                        input.value = newValue;
                                        input.max = maxStock; // Mettre à jour le max
                                    }
                                    
                                    // Ajouter au panier depuis la page produit
                                    function addToCartFromProduct() {
                                        const quantity = parseInt(document.getElementById('quantityInput')?.value || 1);
                                        
                                        // Récupérer les attributs sélectionnés
                                        const selectedAttributes = getSelectedAttributes();
                                        
                                        @if($product->variations && $product->variations->count() > 0)
                                        // Si des variations existent, ajouter l'ID de la variation
                                        const variation = getCurrentVariation();
                                        if (variation && variation.id) {
                                            selectedAttributes.variation_id = variation.id;
                                            console.log('Variation sélectionnée:', variation);
                                        }
                                        @endif
                                        
                                        console.log('Ajout au panier avec attributs:', selectedAttributes);
                                        
                                        addToCart({{ $product->id }}, quantity, selectedAttributes);
                                    }
                                    
                                    // Récupérer les attributs sélectionnés
                                    function getSelectedAttributes() {
                                        const attributes = {};
                                        
                                        // Récupérer les boutons radio sélectionnés
                                        const radios = document.querySelectorAll('.attribute-radio:checked');
                                        console.log('Radios sélectionnés:', radios.length);
                                        radios.forEach(radio => {
                                            const attributeName = radio.dataset.attribute;
                                            const value = radio.dataset.value;
                                            console.log('Radio trouvé:', attributeName, '=', value);
                                            if (!attributes[attributeName]) {
                                                attributes[attributeName] = [];
                                            }
                                            attributes[attributeName].push(value);
                                        });
                                        
                                        // Récupérer les cases à cocher sélectionnées
                                        const checkboxes = document.querySelectorAll('.attribute-checkbox:checked');
                                        console.log('Checkboxes sélectionnés:', checkboxes.length);
                                        checkboxes.forEach(checkbox => {
                                            const attributeName = checkbox.dataset.attribute;
                                            const value = checkbox.dataset.value;
                                            console.log('Checkbox trouvé:', attributeName, '=', value);
                                            if (!attributes[attributeName]) {
                                                attributes[attributeName] = [];
                                            }
                                            attributes[attributeName].push(value);
                                        });
                                        
                                        console.log('Attributs récupérés:', attributes);
                                        console.log('Clés des attributs:', Object.keys(attributes));
                                        
                                        return attributes;
                                    }
                                    
                                    // Toggle favori sur la page produit
                                    function toggleFavoriteProduct() {
                                        const btn = document.getElementById('favoriteBtn');
                                        toggleFavorite({{ $product->id }}, btn);
                                    }
                                    
                                    // La fonction showNotification est maintenant globale via cart.js
                                    // Pas besoin de la redéfinir ici
                                </script>
                                
                                <!-- Script pour gérer les variations dynamiques -->
                                @if($product->variations && $product->variations->count() > 0)
                                @php
                                    $variationsData = $product->variations->map(function($variation) {
                                        return [
                                            'id' => $variation->id,
                                            'price' => (float) $variation->price,
                                            'old_price' => $variation->old_price ? (float) $variation->old_price : null,
                                            'stock' => (int) $variation->stock,
                                            'sku' => $variation->sku,
                                            'is_default' => $variation->is_default,
                                            'attribute_values' => $variation->attributeValues->pluck('id')->toArray()
                                        ];
                                    })->toArray();
                                @endphp
                                <script>
                                    // Données des variations disponibles
                                    const productVariations = @json($variationsData);
                                    
                                    // Variable globale pour stocker la variation actuelle
                                    let currentVariation = null;
                                    
                                    // Données du produit de base (pour affichage par défaut)
                                    const productBaseData = {
                                        price: {{ (float)$product->price }},
                                        old_price: {{ $product->old_price && $product->old_price > $product->price ? (float)$product->old_price : 'null' }},
                                        stock: {{ (int)$product->stock }}
                                    };
                                    
                                    // Trouver la variation correspondant aux attributs sélectionnés
                                    function findMatchingVariation() {
                                        const selectedAttrValueIds = getSelectedAttributesForVariation();
                                        
                                        // Si aucun attribut n'est sélectionné, retourner null (utiliser les données du produit)
                                        if (selectedAttrValueIds.length === 0) {
                                            return null;
                                        }
                                        
                                        // Trier les IDs pour faciliter la comparaison
                                        selectedAttrValueIds.sort((a, b) => a - b);
                                        
                                        // Trouver la variation qui correspond exactement aux attributs sélectionnés
                                        for (let variation of productVariations) {
                                            const varAttrIds = [...variation.attribute_values].sort((a, b) => a - b);
                                            
                                            // Vérifier si les deux tableaux sont identiques (même longueur et mêmes éléments)
                                            if (varAttrIds.length === selectedAttrValueIds.length &&
                                                varAttrIds.every((id, index) => id === selectedAttrValueIds[index])) {
                                                return variation;
                                            }
                                        }
                                        
                                        // Si aucune variation exacte n'est trouvée, retourner null (utiliser les données du produit)
                                        return null;
                                    }
                                    
                                    // Récupérer les attributs sélectionnés (pour les variations) - retourne un tableau d'IDs
                                    function getSelectedAttributesForVariation() {
                                        const attrValueIds = [];
                                        const radios = document.querySelectorAll('.attribute-radio:checked');
                                        const checkboxes = document.querySelectorAll('.attribute-checkbox:checked');
                                        
                                        radios.forEach(radio => {
                                            const attrValueId = parseInt(radio.value);
                                            if (attrValueId && !attrValueIds.includes(attrValueId)) {
                                                attrValueIds.push(attrValueId);
                                            }
                                        });
                                        
                                        checkboxes.forEach(checkbox => {
                                            const attrValueId = parseInt(checkbox.value);
                                            if (attrValueId && !attrValueIds.includes(attrValueId)) {
                                                attrValueIds.push(attrValueId);
                                            }
                                        });
                                        
                                        return attrValueIds;
                                    }
                                    
                                    // Mettre à jour le prix et le stock selon la variation ou le produit de base
                                    function updatePriceAndStock() {
                                        const variation = findMatchingVariation();
                                        currentVariation = variation;
                                        
                                        // Utiliser la variation si elle existe, sinon utiliser les données du produit de base
                                        let displayPrice, oldPrice, stock;
                                        
                                        if (variation) {
                                            // Calculer le prix (promo ou normal) depuis la variation
                                            displayPrice = variation.price;
                                            oldPrice = variation.old_price;
                                            
                                            if (oldPrice && oldPrice > variation.price) {
                                                displayPrice = variation.price; // Prix promo
                                            } else {
                                                oldPrice = null;
                                            }
                                            
                                            stock = variation.stock;
                                        } else {
                                            // Utiliser les données du produit de base
                                            displayPrice = productBaseData.price;
                                            oldPrice = productBaseData.old_price;
                                            
                                            if (oldPrice && oldPrice > productBaseData.price) {
                                                displayPrice = productBaseData.price; // Prix promo
                                            } else {
                                                oldPrice = null;
                                            }
                                            
                                            stock = productBaseData.stock;
                                        }
                                        
                                        // Mettre à jour l'affichage du prix
                                        const currentPriceEl = document.getElementById('current-price');
                                        const oldPriceEl = document.getElementById('old-price');
                                        const savingsContainer = document.getElementById('savings-container');
                                        const savingsAmount = document.getElementById('savings-amount');
                                        
                                        if (currentPriceEl) {
                                            currentPriceEl.textContent = formatPrice(displayPrice) + ' FCFA';
                                        }
                                        
                                        if (oldPriceEl) {
                                            if (oldPrice) {
                                                oldPriceEl.textContent = formatPrice(oldPrice) + ' FCFA';
                                                oldPriceEl.style.display = 'inline-block';
                                            } else {
                                                oldPriceEl.style.display = 'none';
                                            }
                                        }
                                        
                                        if (savingsContainer && savingsAmount) {
                                            if (oldPrice && oldPrice > displayPrice) {
                                                const savings = oldPrice - displayPrice;
                                                savingsAmount.textContent = `Vous avez épargné ${formatPrice(savings)} FCFA`;
                                                savingsContainer.style.display = 'block';
                                            } else {
                                                savingsContainer.style.display = 'none';
                                            }
                                        }
                                        
                                        // Mettre à jour le stock
                                        const stockBadge = document.getElementById('stock-badge');
                                        const stockCount = document.getElementById('stock-count');
                                        const quantityContainer = document.getElementById('quantity-container');
                                        const quantityInput = document.getElementById('quantityInput');
                                        
                                        if (stockBadge && stockCount) {
                                            if (stock > 0) {
                                                stockBadge.className = 'badge bg-success';
                                                stockBadge.innerHTML = `En stock (<span id="stock-count">${formatPrice(stock)}</span> disponibles)`;
                                                
                                                if (quantityContainer) {
                                                    quantityContainer.style.display = 'block';
                                                }
                                                
                                                if (quantityInput) {
                                                    const currentQty = parseInt(quantityInput.value) || 1;
                                                    quantityInput.max = stock;
                                                    if (currentQty > stock) {
                                                        quantityInput.value = stock;
                                                    }
                                                }
                                            } else {
                                                stockBadge.className = 'badge bg-danger';
                                                stockBadge.textContent = 'Rupture de stock';
                                                
                                                if (quantityContainer) {
                                                    quantityContainer.style.display = 'none';
                                                }
                                            }
                                        }
                                        
                                        if (variation) {
                                            console.log('✅ Prix et stock mis à jour pour la variation:', variation);
                                        } else {
                                            console.log('✅ Prix et stock mis à jour avec les données du produit de base');
                                        }
                                    }
                                    
                                    // Formater le prix avec séparateurs de milliers
                                    function formatPrice(price) {
                                        return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                                    }
                                    
                                    // Initialiser lors du chargement de la page
                                    document.addEventListener('DOMContentLoaded', function() {
                                        // Écouter les changements dans la sélection des attributs
                                        document.querySelectorAll('.attribute-radio, .attribute-checkbox').forEach(input => {
                                            input.addEventListener('change', function() {
                                                updatePriceAndStock();
                                            });
                                        });
                                        
                                        // Aucune initialisation avec une variation par défaut
                                        // Le prix et le stock du produit sont déjà affichés par défaut
                                    });
                                    
                                    // Fonction pour obtenir la variation actuelle (utilisée dans addToCartFromProduct)
                                    function getCurrentVariation() {
                                        return currentVariation || findMatchingVariation();
                                    }
                                </script>
                                @endif
                                
                                <hr>
                                <div>
                                    <p class="mb-2 text-uppercase fs-7 fw-bold">Partager ce produit</p>
                                    <div class="hstack gap-2">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('product-page', $product->slug)) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-facebook"></i></a>
                                        <a href="https://wa.me/?text={{ urlencode($product->name . ' - ' . route('product-page', $product->slug)) }}" target="_blank" class="btn btn-outline-success"><i class="bi bi-whatsapp"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light-subtle p-2 rounded-2 mb-3">
                                <p class="mb-3 fw-bold text-uppercase">Livraison en 24H partout à :</p>
                                <hr>
                                <span class="orange-bg text-white rounded-5 px-2 py-1 fs-8">Abidjan</span><br>
                                <a href="" class="btn btn-sm orange-color fs-8 mt-2">Termes & Condition</a>
                            </div>
                            <div class="bg-light-subtle p-2 rounded-2">
                                <p class="mb-3 fw-bold text-uppercase">Livraison & Retours</p>
                                <hr>
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-truck fa-2x orange-color me-3"></i>
                                    <p class="mb-0 fs-8">
                                        <span class="orange-color fs-6 fw-bold">Livraison</span><br>
                                        Livraison express disponible uniquement pour les produits KAZARIA <br>
                                        <span class="orange-color fs-7 fw-bold">Livraison le jour même :</span> veuillez passer votre commande avant 15h (sauf le dimanche). <br>
                                        <span class="orange-color fs-7 fw-bold">Livraison le lendemain :</span> les commandes passées après 15h seront livrées le lendemain.<br>
                                        <span class="orange-color fs-7 fw-bold">Remarque :</span> la disponibilité peut varier selon la région. <br>
                                    </p>
                                </div>
                                <hr>
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-arrow-clockwise fa-2x orange-color me-3"></i>
                                    <p class="mb-0 fs-8">
                                        <span class="orange-color fs-6 fw-bold">Politique de retour</span><br>
                                        <span class="fs-7 fw-bold">Retour garanti sous 7 jours</span><br>
                                        Pour plus d'informations sur les options de retour, veuillez consulter la politique de retour de Konga.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light-subtle p-2 rounded-2">
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <button class="nav-link blue-color active" id="nav-descripProduit-tab" data-bs-toggle="tab" data-bs-target="#nav-descripProduit" type="button" role="tab" aria-controls="nav-descripProduit" aria-selected="true">Description</button>
                                        <button class="nav-link blue-color" id="nav-ficheTech-tab" data-bs-toggle="tab" data-bs-target="#nav-ficheTech" type="button" role="tab" aria-controls="nav-ficheTech" aria-selected="false">Fiche Technique</button>
                                        <button class="nav-link blue-color" id="nav-avisProduit-tab" data-bs-toggle="tab" data-bs-target="#nav-avisProduit" type="button" role="tab" aria-controls="nav-avisProduit" aria-selected="false">Avis (<span id="navReviewsCount">{{ $product->reviews_count }}</span>)</button>
                                    </div>
                                </nav>
                                <div class="tab-content p-3" id="nav-tabContent">
                                    <div class="tab-pane fade show active" id="nav-descripProduit" role="tabpanel" aria-labelledby="nav-descripProduit-tab" tabindex="0">
                                        <h5 class="fw-bold mb-3">Description du produit</h5>
                                        <div style="white-space: pre-line;">{{ $product->description ?? 'Aucune description disponible pour ce produit.' }}</div>
                                        
                                        @if($product->brand)
                                        <div class="mt-4">
                                            <h6 class="fw-bold">Marque</h6>
                                            <p>{{ $product->brand }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="tab-pane fade" id="nav-ficheTech" role="tabpanel" aria-labelledby="nav-ficheTech-tab" tabindex="0">
                                        <h5 class="fw-bold mb-4">Fiche Technique</h5>
                                        
                                        <!-- Informations générales -->
                                        <h6 class="fw-bold mb-3 text-uppercase" style="color: var(--main-color); font-size: 0.9rem;">Informations Générales</h6>
                                        <table class="table table-striped table-hover mb-4">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 35%;">Nom du produit</th>
                                                    <td>{{ $product->name }}</td>
                                                </tr>
                                                @if($product->brand)
                                                <tr>
                                                    <th>Marque</th>
                                                    <td>{{ $product->brand }}</td>
                                                </tr>
                                                @endif
                                                @if($product->model)
                                                <tr>
                                                    <th>Modèle</th>
                                                    <td>{{ $product->model }}</td>
                                                </tr>
                                                @endif
                                                @if($product->category || ($product->categories && $product->categories->count() > 0))
                                                <tr>
                                                    <th>Catégorie</th>
                                                    <td>
                                                        @if($product->categories && $product->categories->count() > 0)
                                                            {{ $product->categories->pluck('name')->implode(', ') }}
                                                        @elseif($product->category)
                                                            {{ $product->category->name }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endif
                                                @if($product->subcategories && $product->subcategories->count() > 0)
                                                <tr>
                                                    <th>Sous-catégories</th>
                                                    <td>{{ $product->subcategories->pluck('name')->implode(', ') }}</td>
                                                </tr>
                                                @elseif($product->subcategory)
                                                <tr>
                                                    <th>Sous-catégorie</th>
                                                    <td>{{ $product->subcategory->name }}</td>
                                                </tr>
                                                @endif
                                                @if($product->store)
                                                <tr>
                                                    <th>Boutique</th>
                                                    <td>{{ $product->store->name }}</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>

                                        <!-- Informations commerciales -->
                                        <h6 class="fw-bold mb-3 text-uppercase" style="color: var(--main-color); font-size: 0.9rem;">Informations Commerciales</h6>
                                        <table class="table table-striped table-hover mb-4">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 35%;">Prix actuel</th>
                                                    <td>
                                                        <span class="fw-bold fs-5 orange-color">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                                                        @if($product->old_price && $product->old_price > $product->price)
                                                            <br><small class="text-decoration-line-through text-muted">{{ number_format($product->old_price, 0, ',', ' ') }} FCFA</small>
                                                            @php
                                                                $discountAmount = $product->old_price - $product->price;
                                                                $discountPercent = round(($discountAmount / $product->old_price) * 100);
                                                            @endphp
                                                            <span class="badge bg-danger ms-2">-{{ $discountPercent }}%</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if($product->old_price && $product->old_price > $product->price)
                                                <tr>
                                                    <th>Prix avant réduction</th>
                                                    <td>{{ number_format($product->old_price, 0, ',', ' ') }} FCFA</td>
                                                </tr>
                                                @endif
                                                @if($product->discount_percentage && $product->discount_percentage > 0)
                                                <tr>
                                                    <th>Réduction</th>
                                                    <td><span class="badge bg-danger">{{ $product->discount_percentage }}%</span></td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th>Disponibilité</th>
                                                    <td>
                                                        @if($product->stock > 0)
                                                            <span class="badge bg-success">{{ number_format($product->stock, 0, ',', ' ') }} en stock</span>
                                                        @else
                                                            <span class="badge bg-danger">Rupture de stock</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if($product->warranty)
                                                <tr>
                                                    <th>Garantie</th>
                                                    <td>{{ $product->warranty }}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th>Note moyenne</th>
                                                    <td>
                                                        <span class="fw-bold">{{ number_format($product->rating, 1) }}/5</span>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa-solid fa-star {{ $i <= floor($product->rating) ? 'text-warning' : 'text-secondary' }} fs-7"></i>
                                                        @endfor
                                                        <span class="text-muted ms-2">({{ $product->reviews_count }} avis)</span>
                                                    </td>
                                                </tr>
                                                @if(isset($product->views) && $product->views > 0)
                                                <tr>
                                                    <th>Vues</th>
                                                    <td>{{ number_format($product->views, 0, ',', ' ') }}</td>
                                                </tr>
                                                @endif
                                                @if(isset($product->views_count) && $product->views_count > 0)
                                                <tr>
                                                    <th>Vues</th>
                                                    <td>{{ number_format($product->views_count, 0, ',', ' ') }}</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>

                                        <!-- Attributs techniques -->
                                        @if($product->attributeValues && $product->attributeValues->count() > 0)
                                        <h6 class="fw-bold mb-3 text-uppercase" style="color: var(--main-color); font-size: 0.9rem;">Caractéristiques Techniques</h6>
                                        <table class="table table-striped table-hover mb-4">
                                            <tbody>
                                                @php
                                                    $groupedAttributes = $product->attributeValues->groupBy(function($item) {
                                                        return $item->attribute->name ?? 'Autres';
                                                    });
                                                @endphp
                                                @foreach($groupedAttributes as $attributeName => $values)
                                                    <tr>
                                                        <th style="width: 35%;">{{ $attributeName }}</th>
                                                        <td>
                                                            @foreach($values as $value)
                                                                <span class="badge bg-primary me-1 mb-1">{{ $value->value }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        <!-- Attributs JSON (si disponibles) -->
                                        @if($product->attributes && is_array($product->attributes) && count($product->attributes) > 0)
                                        <h6 class="fw-bold mb-3 text-uppercase" style="color: var(--main-color); font-size: 0.9rem;">Caractéristiques Supplémentaires</h6>
                                        <table class="table table-striped table-hover mb-4">
                                            <tbody>
                                                @foreach($product->attributes as $key => $value)
                                                    @if($value !== null && $value !== '')
                                                        <tr>
                                                            <th style="width: 35%;">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                                            <td>
                                                                @if(is_array($value))
                                                                    {{ implode(', ', $value) }}
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        <!-- Tags -->
                                        @if($product->tags && is_array($product->tags) && count($product->tags) > 0)
                                        <h6 class="fw-bold mb-3 text-uppercase" style="color: var(--main-color); font-size: 0.9rem;">Tags</h6>
                                        <div class="mb-4">
                                            @foreach($product->tags as $tag)
                                                <span class="badge bg-secondary me-1 mb-1">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                        @endif

                                        <!-- Informations complémentaires -->
                                        <h6 class="fw-bold mb-3 text-uppercase" style="color: var(--main-color); font-size: 0.9rem;">Informations Complémentaires</h6>
                                        <table class="table table-striped table-hover">
                                            <tbody>
                                                @if($product->is_featured)
                                                <tr>
                                                    <th style="width: 35%;">Boutique</th>
                                                    <td><span class="badge blue-bg text-white">Boutique Officielle</span></td>
                                                </tr>
                                                @endif
                                                @if($product->is_new)
                                                <tr>
                                                    <th>Statut</th>
                                                    <td><span class="badge bg-info">Nouveau</span></td>
                                                </tr>
                                                @endif
                                                @if($product->is_trending)
                                                <tr>
                                                    <th>Tendance</th>
                                                    <td><span class="badge bg-warning text-dark">Tendance</span></td>
                                                </tr>
                                                @endif
                                                @if($product->is_best_offer)
                                                <tr>
                                                    <th>Offre</th>
                                                    <td><span class="badge bg-danger">Meilleure Offre</span></td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th>Date d'ajout</th>
                                                    <td>{{ $product->created_at->format('d/m/Y') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade" id="nav-avisProduit" role="tabpanel" aria-labelledby="nav-avisProduit-tab" tabindex="0">
                                        <!-- Statistiques des avis -->
                                        <div class="row mb-4">
                                            <div class="col-md-4">
                                                <div class="text-center p-4 bg-light rounded">
                                                    <div class="display-4 fw-bold orange-color" id="averageRating">{{ $product->rating }}</div>
                                                    <div class="mb-2" id="averageStars">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa-solid fa-star {{ $i <= floor($product->rating) ? 'text-warning' : 'text-secondary' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <p class="text-muted mb-0"><span id="totalReviews">{{ $product->reviews_count }}</span> avis</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="mb-3">Distribution des notes</h6>
                                                <div id="ratingDistribution">
                                                    <!-- Sera rempli dynamiquement -->
                                                    @for($i = 5; $i >= 1; $i--)
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="me-2">{{ $i }} <i class="fa-solid fa-star text-warning fs-8"></i></span>
                                                            <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                                                <div class="progress-bar orange-bg" role="progressbar" style="width: 0%" id="rating-{{ $i }}"></div>
                                                            </div>
                                                            <span class="text-muted" id="count-{{ $i }}">0</span>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bouton ajouter un avis -->
                                        <div class="mb-4">
                                            <button class="btn orange-bg text-white" onclick="showReviewForm()" id="addReviewBtn">
                                                <i class="bi bi-star-fill me-2"></i>Donner votre avis
                                            </button>
                                        </div>

                                        <!-- Formulaire d'ajout d'avis (caché par défaut) -->
                                        <div id="reviewFormContainer" style="display: none;" class="mb-4">
                                            <div class="card">
                                                <div class="card-header orange-bg text-white">
                                                    <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Votre avis</h6>
                                                </div>
                                                <div class="card-body">
                                                    <form id="reviewForm">
                                                        <input type="hidden" id="reviewProductId" value="{{ $product->id }}">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Votre note <span class="text-danger">*</span></label>
                                                            <div class="rating-input">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <i class="fa-star rating-star" data-rating="{{ $i }}" onclick="setRating({{ $i }})"></i>
                                                                @endfor
                                                            </div>
                                                            <input type="hidden" id="reviewRating" value="5">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="reviewTitle" class="form-label fw-bold">Titre (optionnel)</label>
                                                            <input type="text" class="form-control" id="reviewTitle" placeholder="Résumez votre expérience">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="reviewComment" class="form-label fw-bold">Votre commentaire <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" id="reviewComment" rows="4" placeholder="Partagez votre expérience avec ce produit..." required></textarea>
                                                            <small class="text-muted">Minimum 10 caractères</small>
                                                        </div>

                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn orange-bg text-white" id="submitReviewBtn">
                                                                <i class="bi bi-send me-2"></i>Publier mon avis
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary" onclick="hideReviewForm()">
                                                                Annuler
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Filtres et tri -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Tous les avis</h6>
                                            <select class="form-select form-select-sm" style="width: 200px;" id="reviewSort" onchange="loadReviews()">
                                                <option value="recent">Plus récents</option>
                                                <option value="helpful">Plus utiles</option>
                                                <option value="rating_high">Note décroissante</option>
                                                <option value="rating_low">Note croissante</option>
                                            </select>
                                        </div>

                                        <!-- Liste des avis -->
                                        <div id="reviewsContainer">
                                            <div id="reviewsLoadingSpinner" class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                                <p class="mt-2">Chargement des avis...</p>
                                            </div>
                                        </div>

                                        <!-- Pagination -->
                                        <div id="reviewsPagination" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION END -->

        <!-- SECTION PRODUITS SIMILAIRES -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Produits similaires</h5>
            </div>
            <div class="multi-carousel-track d-flex">
                @forelse ($similarProducts as $similarProduct)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $similarProduct])
                        </div>
                @empty
                <div class="col-12">
                    <p class="text-muted text-center">Aucun produit similaire disponible pour le moment.</p>
                </div>
                @endforelse
            </div>
            @if($similarProducts->count() > 0)
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
            @endif
        </section>
        <!-- SECTION PRODUITS SIMILAIRES END -->

        <!-- SECTION VUES RECENTES -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Vues récentes</h5>
            </div>
            <div class="multi-carousel-track d-flex">
                @foreach ($recentProducts as $recentProduct)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $recentProduct])
                </div>
                @endforeach
            </div>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
        </section>
        <!-- SECTION VUES RECENTES END -->
    </main>

    <!-- Modal pour visionner les images en grand -->
    <div class="modal fade z-index-9x" id="imageModal" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" onclick="event.stopPropagation()">
            <div class="modal-content" style="background: rgba(0,0,0,0.95); border: none;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img id="modalImage" src="" class="img-fluid" style="max-height: 80vh; object-fit: contain;" alt="{{ $product->name }}">
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <small class="text-white-50"><i class="bi bi-info-circle me-2"></i>Cliquez en dehors de l'image pour fermer</small>
                </div>
            </div>
        </div>
    </div>

    <style>
        .product-thumbnail {
            transition: all 0.3s ease;
            border-radius: 4px;
        }
        
        .product-thumbnail:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        
        .product-thumbnail.active {
            box-shadow: 0 0 10px rgba(240, 78, 38, 0.5);
        }
        
        #mainProductImage {
            transition: transform 0.3s ease;
        }
        
        #mainProductImage:hover {
            transform: scale(1.02);
        }
    </style>

    <!-- Script pour la galerie d'images -->
    <script>
        // Changer l'image principale
        function changeMainImage(imageUrl, element) {
            const mainImage = document.getElementById('mainProductImage');
            if (mainImage) {
                // Changer l'image principale
                mainImage.src = imageUrl;
                
                // Retirer la bordure active de toutes les miniatures
                document.querySelectorAll('.product-thumbnail').forEach(thumb => {
                    thumb.style.border = '2px solid transparent';
                    thumb.classList.remove('active');
                });
                
                // Ajouter la bordure active à la miniature cliquée
                if (element) {
                    element.style.border = '2px solid var(--main-color)';
                    element.classList.add('active');
                }
                
                console.log('✅ Image principale changée pour:', imageUrl);
            }
        }

        // Ouvrir le modal pour voir l'image en grand
        function openImageModal() {
            const mainImage = document.getElementById('mainProductImage');
            const modalImage = document.getElementById('modalImage');
            
            if (mainImage && modalImage) {
                // Utiliser l'URL de l'image principale actuelle
                modalImage.src = mainImage.src;
                console.log('🔍 Ouverture du modal avec image:', mainImage.src);
            }
            
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'), {
                backdrop: true,  // Permet de fermer en cliquant dehors
                keyboard: true   // Permet de fermer avec Échap
            });
            imageModal.show();
        }
    </script>

    <!-- Script pour les avis -->
    <script>
        const productId = {{ $product->id }};
        const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        let currentPage = 1;

        // Charger les avis au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM chargé, initialisation du système d\'avis');
            
            // Initialiser le formulaire
            const reviewForm = document.getElementById('reviewForm');
            if (reviewForm) {
                reviewForm.addEventListener('submit', submitReview);
                console.log('✅ Formulaire d\'avis initialisé');
            }
            
            // Charger les avis quand l'onglet "Avis" est cliqué
            const avisTab = document.querySelector('button[data-bs-target="#nav-avisProduit"]');
            if (avisTab) {
                console.log('✅ Onglet Avis trouvé, ajout de l\'événement');
                avisTab.addEventListener('shown.bs.tab', function() {
                    console.log('🎯 Onglet Avis activé, chargement des avis...');
                    setTimeout(loadReviews, 100);
                });
            } else {
                console.error('❌ Onglet Avis non trouvé !');
            }
            
            // Vérifier si l'onglet "Avis" est déjà actif
            const avisTabPane = document.getElementById('nav-avisProduit');
            if (avisTabPane && avisTabPane.classList.contains('active')) {
                console.log('🎯 Onglet Avis déjà actif, chargement immédiat');
                setTimeout(loadReviews, 100);
            }
        });

            // Charger les avis
            async function loadReviews(page = 1) {
                console.log('🚀 loadReviews: Démarrage du chargement des avis');
                
                const sortElement = document.getElementById('reviewSort');
                const container = document.getElementById('reviewsContainer');
                
                console.log('🔍 loadReviews: Éléments DOM:', {
                    sortElement: !!sortElement,
                    container: !!container,
                    productId: productId
                });
                
                if (!container) {
                    console.error('❌ Container manquant pour les avis');
                    return Promise.reject(new Error('Container non trouvé'));
                }
                
                // Afficher le spinner dans le container
                container.innerHTML = '<div id="reviewsLoadingSpinner" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div><p class="mt-2">Chargement des avis...</p></div>';
            
            const sort = sortElement ? sortElement.value : 'recent';
            console.log('📊 loadReviews: Tri sélectionné:', sort);
            
            try {
                console.log('🌐 loadReviews: Envoi requête API vers:', `/api/products/${productId}/reviews?sort=${sort}&page=${page}`);
                
                const response = await fetch(`/api/products/${productId}/reviews?sort=${sort}&page=${page}`);
                
                console.log('📡 loadReviews: Réponse reçue:', {
                    status: response.status,
                    ok: response.ok,
                    statusText: response.statusText
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('📦 loadReviews: Données JSON reçues:', data);

                if (data.success) {
                    console.log('✅ loadReviews: API success = true');
                    console.log('📈 loadReviews: Mise à jour des statistiques...');
                    
                    // Mettre à jour les statistiques
                    updateStats(data.stats);
                    
                    console.log('📝 loadReviews: Traitement des avis...', {
                        hasReviews: !!(data.reviews && data.reviews.data),
                        reviewsCount: data.reviews?.data?.length || 0
                    });
                    
                    // Afficher les avis
                    if (data.reviews.data && data.reviews.data.length > 0) {
                        console.log('📋 loadReviews: Affichage des avis trouvés');
                        container.innerHTML = '';
                        
                        data.reviews.data.forEach((review, index) => {
                            console.log(`📄 loadReviews: Rendu avis ${index + 1}:`, review);
                            container.innerHTML += createReviewHTML(review);
                        });
                        
                        console.log('📄 loadReviews: Rendu de la pagination');
                        // Pagination
                        createPagination(data.reviews);
                        
                        console.log('✅ loadReviews: Affichage des avis terminé');
                    } else {
                        console.log('📭 loadReviews: Aucun avis trouvé, affichage du message');
                        container.innerHTML = `
                            <div class="text-center py-5">
                                <i class="bi bi-chat-left-text text-muted" style="font-size: 3rem;"></i>
                                <p class="mt-3 text-muted">Aucun avis pour le moment.</p>
                                <p class="text-muted">Soyez le premier à donner votre avis !</p>
                            </div>
                        `;
                    }
                } else {
                    console.error('❌ loadReviews: API success = false:', data.message);
                    throw new Error(data.message || 'Erreur inconnue de l\'API');
                }
            } catch (error) {
                console.error('💥 loadReviews: Erreur fatale:', error);
                if (container) {
                    container.innerHTML = `<div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des avis: ${error.message}
                        <br><small>Vérifiez la console pour plus de détails.</small>
                    </div>`;
                }
                throw error; // Re-throw pour que .catch() fonctionne
            } finally {
                // Le spinner est automatiquement remplacé quand on met à jour container.innerHTML
                console.log('🔄 loadReviews: Fin du chargement (spinner remplacé par le contenu)');
            }
            
            console.log('🏁 loadReviews: Fin de la fonction');
        }

        // Mettre à jour les statistiques
        function updateStats(stats) {
            console.log('📊 updateStats: Démarrage avec stats:', stats);
            
            // Vérifier que stats existe
            if (!stats) {
                console.error('❌ updateStats: stats est null ou undefined');
                return;
            }
            
            const averageRatingEl = document.getElementById('averageRating');
            const totalReviewsEl = document.getElementById('totalReviews');
            const averageStarsEl = document.getElementById('averageStars');
            
            console.log('📊 updateStats: Éléments DOM trouvés:', {
                averageRatingEl: !!averageRatingEl,
                totalReviewsEl: !!totalReviewsEl,
                averageStarsEl: !!averageStarsEl
            });
            
            if (averageRatingEl) {
                // Convertir en nombre si c'est une string, avec protection
                const avgRating = stats.average_rating !== null && stats.average_rating !== undefined ? 
                    parseFloat(stats.average_rating) : 0;
                const rating = isNaN(avgRating) ? '0.0' : avgRating.toFixed(1);
                console.log('📊 updateStats: Mise à jour note moyenne:', rating);
                averageRatingEl.textContent = rating;
            }
            if (totalReviewsEl) {
                const totalReviews = stats.total_reviews !== null && stats.total_reviews !== undefined ? 
                    parseInt(stats.total_reviews) : 0;
                console.log('📊 updateStats: Mise à jour nombre total:', totalReviews);
                totalReviewsEl.textContent = totalReviews;
            }
            
            // Mettre à jour les étoiles
            if (averageStarsEl) {
                console.log('⭐ updateStats: Mise à jour des étoiles');
                let starsHTML = '';
                const avgRating = parseFloat(stats.average_rating);
                for (let i = 1; i <= 5; i++) {
                    starsHTML += `<i class="fa-solid fa-star ${i <= Math.floor(avgRating) ? 'text-warning' : 'text-secondary'}"></i>`;
                }
                averageStarsEl.innerHTML = starsHTML;
            }
            
            // Mettre à jour les étoiles à côté du prix
            const priceStarsEl = document.getElementById('priceStars');
            if (priceStarsEl) {
                console.log('⭐ updateStats: Mise à jour des étoiles du prix');
                let priceStarsHTML = '';
                const avgRating = parseFloat(stats.average_rating);
                for (let i = 1; i <= 5; i++) {
                    priceStarsHTML += `<i class="fa-solid fa-star ${i <= Math.floor(avgRating) ? 'text-warning' : 'text-secondary'} fs-7"></i>`;
                }
                priceStarsEl.innerHTML = priceStarsHTML;
                console.log('✅ updateStats: Étoiles du prix mises à jour');
            }
            
            // Mettre à jour les compteurs dans la navigation et les étoiles
            const navReviewsCount = document.getElementById('navReviewsCount');
            const reviewsCount = document.getElementById('reviewsCount');
            const reviewsCountText = document.getElementById('reviewsCountText');
            
            const totalReviews = stats.total_reviews !== null && stats.total_reviews !== undefined ? 
                parseInt(stats.total_reviews) : 0;
            
            console.log('🔢 updateStats: Mise à jour des compteurs d\'avis:', totalReviews);
            
            // Onglet navigation
            if (navReviewsCount) {
                navReviewsCount.textContent = totalReviews;
                console.log('✅ updateStats: Onglet navigation mis à jour');
            }
            
            // Compteur à côté des étoiles
            if (reviewsCount) {
                reviewsCount.textContent = totalReviews;
                console.log('✅ updateStats: Compteur étoiles mis à jour');
            }
            
            // Gérer le texte "Pas d'avis pour le moment"
            if (reviewsCountText) {
                if (totalReviews === 0) {
                    reviewsCountText.innerHTML = '(Pas d\'avis pour le moment)';
                } else {
                    reviewsCountText.innerHTML = `(${totalReviews} avis)`;
                }
                console.log('✅ updateStats: Texte compteur mis à jour');
            }
            
            // Distribution
            console.log('📈 updateStats: Mise à jour de la distribution');
            const total = stats.total_reviews || 0;
            const distribution = stats.distribution || {};
            
            for (let i = 5; i >= 1; i--) {
                const count = parseInt(distribution[i]) || 0;
                const percentage = total > 0 ? (count / total * 100) : 0;
                
                const ratingBar = document.getElementById(`rating-${i}`);
                const countEl = document.getElementById(`count-${i}`);
                
                console.log(`📈 updateStats: Rating ${i}: count=${count}, percentage=${percentage}%`);
                
                if (ratingBar) {
                    ratingBar.style.width = percentage + '%';
                }
                if (countEl) {
                    countEl.textContent = count;
                }
            }
            
            console.log('✅ updateStats: Terminé');
        }

        // Créer le HTML d'un avis
        function createReviewHTML(review) {
            const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
            const verifiedBadge = review.is_verified_purchase ? '<span class="badge bg-success ms-2"><i class="bi bi-check-circle me-1"></i>Achat vérifié</span>' : '';
            const title = review.title ? `<h6 class="mb-2">${review.title}</h6>` : '';
            const date = new Date(review.created_at).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
            
            // Vérifier si c'est un avis récent (moins de 5 minutes)
            const now = new Date();
            const reviewDate = new Date(review.created_at);
            const isRecent = (now - reviewDate) < 5 * 60 * 1000; // 5 minutes
            const recentClass = isRecent ? 'review-item-recent' : '';
            
            return `
                <div class="review-item border-bottom pb-3 mb-3 ${recentClass}" data-review-id="${review.id}">
                    ${isRecent ? '<div class="badge bg-primary mb-2"><i class="bi bi-star-fill me-1"></i>Nouvel avis</div>' : ''}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong>${review.user.prenoms} ${review.user.nom.charAt(0)}.</strong>
                            ${verifiedBadge}
                            <div class="text-warning">${stars}</div>
                        </div>
                        <small class="text-muted">${date}</small>
                    </div>
                    ${title}
                    <p class="mb-2">${review.comment}</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="voteReview(${review.id}, true)">
                            <i class="bi bi-hand-thumbs-up me-1"></i>Utile (${review.helpful_count})
                        </button>
                    </div>
                </div>
            `;
        }

        // Pagination
        function createPagination(reviews) {
            const container = document.getElementById('reviewsPagination');
            if (reviews.last_page <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '<nav><ul class="pagination justify-content-center">';
            
            // Précédent
            html += `<li class="page-item ${reviews.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadReviews(${reviews.current_page - 1}); return false;">Précédent</a>
            </li>`;
            
            // Pages
            for (let i = 1; i <= reviews.last_page; i++) {
                if (i === 1 || i === reviews.last_page || (i >= reviews.current_page - 2 && i <= reviews.current_page + 2)) {
                    html += `<li class="page-item ${i === reviews.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadReviews(${i}); return false;">${i}</a>
                    </li>`;
                } else if (i === reviews.current_page - 3 || i === reviews.current_page + 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Suivant
            html += `<li class="page-item ${reviews.current_page === reviews.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadReviews(${reviews.current_page + 1}); return false;">Suivant</a>
            </li>`;
            
            html += '</ul></nav>';
            container.innerHTML = html;
        }

        // Afficher le formulaire
        function showReviewForm() {
            if (!isAuthenticated) {
                const redirectUrl = `/login?redirect=${encodeURIComponent(window.location.pathname + '#reviews')}`;
                if (typeof window.showNotification === 'function') {
                    window.showNotification('warning', 'Veuillez vous connecter pour donner votre avis.');
                } else {
                    alert('Veuillez vous connecter pour donner votre avis.');
                }
                window.location.href = redirectUrl;
                return;
            }

            const formContainer = document.getElementById('reviewFormContainer');
            const addButton = document.getElementById('addReviewBtn');

            if (formContainer && addButton) {
                formContainer.style.display = 'block';
                addButton.style.display = 'none';
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Masquer le formulaire
        function hideReviewForm() {
            document.getElementById('reviewFormContainer').style.display = 'none';
            document.getElementById('addReviewBtn').style.display = 'block';
            
            // Réinitialiser le formulaire
            document.getElementById('reviewForm').reset();
            
            // Remettre les étoiles à 5 par défaut
            setRating(5);
            
            console.log('✅ Formulaire d\'avis masqué et réinitialisé');
        }

        // Définir la note
        function setRating(rating) {
            document.getElementById('reviewRating').value = rating;
            const stars = document.querySelectorAll('.rating-star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('fa-regular');
                    star.classList.add('fa-solid', 'text-warning');
                } else {
                    star.classList.remove('fa-solid', 'text-warning');
                    star.classList.add('fa-regular');
                }
            });
        }

        // Soumettre l'avis
        async function submitReview(e) {
            e.preventDefault();
            
            if (!isAuthenticated) {
                showNotification('warning', 'Vous devez être connecté pour laisser un avis');
                return;
            }
            
            const rating = document.getElementById('reviewRating').value;
            const title = document.getElementById('reviewTitle').value;
            const comment = document.getElementById('reviewComment').value;
            
            if (comment.length < 10) {
                showNotification('warning', 'Votre commentaire doit contenir au moins 10 caractères');
                return;
            }
            
            const btn = document.getElementById('submitReviewBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Publication...';
            
            try {
                const response = await fetch('/reviews', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        product_id: productId,
                        rating: rating,
                        title: title,
                        comment: comment
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showNotification('success', '🎉 ' + data.message);
                    hideReviewForm();

                    setTimeout(() => {
                        const container = document.getElementById('reviewsContainer');
                        if (container) {
                            container.innerHTML = `
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <span class="ms-2">Mise à jour des avis...</span>
                                </div>
                            `;
                        }

                        loadReviews().then(() => {
                            setTimeout(() => {
                                const newReview = document.querySelector('.review-item-recent');
                                if (newReview) {
                                    newReview.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }, 300);

                            showNotification('success', '✅ Avis publié et affiché !');
                        }).catch(error => {
                            console.error('❌ Erreur lors du rechargement:', error);
                            showNotification('warning', 'Avis ajouté mais erreur d\'affichage. Actualisez la page.');
                        });
                    }, 500);
                } else {
                    const message = data?.message || 'Erreur lors de la publication de votre avis';
                    showNotification('danger', message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('danger', 'Erreur lors de la publication de votre avis');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-2"></i>Publier mon avis';
            }
        }

        // Voter pour un avis
        async function voteReview(reviewId, isHelpful) {
            const token = localStorage.getItem('auth_token');
            const sessionId = localStorage.getItem('guest_session_id');
            
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };
            
            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            } else if (sessionId) {
                headers['X-Session-ID'] = sessionId;
            }
            
            try {
                const response = await fetch(`/api/reviews/${reviewId}/vote`, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ is_helpful: isHelpful })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('success', data.message);
                    loadReviews();
                } else {
                    showNotification('warning', data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('danger', 'Erreur lors du vote');
            }
        }

        // Initialiser les étoiles
        setRating(5);

        // Fonction de notification robuste pour cette page
        function showNotification(type, message) {
            console.log(`[${type.toUpperCase()}] ${message}`);
            
            // Vérifier si la fonction globale existe
            if (typeof window.showNotification === 'function') {
                try {
                    window.showNotification(type, message);
                    return;
                } catch (e) {
                    console.error('Erreur avec showNotification globale:', e);
                }
            }
            
            // Fallback : créer une notification Bootstrap simple
            const alertContainer = document.getElementById('alertContainer');
            if (!alertContainer) {
                // Créer le container s'il n'existe pas
                const container = document.createElement('div');
                container.id = 'alertContainer';
                container.style.position = 'fixed';
                container.style.top = '20px';
                container.style.right = '20px';
                container.style.zIndex = '9999';
                container.style.maxWidth = '400px';
                document.body.appendChild(container);
            }
            
            const alertDiv = document.createElement('div');
            const alertClass = type === 'error' || type === 'danger' ? 'danger' : 
                              type === 'success' ? 'success' : 
                              type === 'warning' ? 'warning' : 'info';
            
            alertDiv.className = `alert alert-${alertClass} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.getElementById('alertContainer');
            container.appendChild(alertDiv);
            
            // Auto-supprimer après 4 secondes
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 4000);
        }
    </script>

    <!-- CSS pour les étoiles -->
    <style>
        .rating-star {
            font-size: 2rem;
            cursor: pointer;
            color: #ccc;
            transition: color 0.2s;
        }
        
        .rating-star:hover {
            color: #ffc107;
        }
        
        .review-item:hover {
            background-color: rgba(240, 78, 39, 0.05);
            border-radius: 8px;
            padding: 12px !important;
            margin: 0 -12px 12px -12px !important;
        }
        
        /* Animation pour les nouveaux avis */
        .review-item-recent {
            animation: newReviewPulse 2s ease-in-out;
            border: 2px solid rgba(13, 110, 253, 0.3) !important;
            border-radius: 8px;
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        @keyframes newReviewPulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
            }
            50% {
                transform: scale(1.02);
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }
    </style>

@endsection