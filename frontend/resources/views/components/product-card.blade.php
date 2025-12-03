<div class="px-1 position-relative product-card">
    <a class="text-decoration-none" href="{{ route('product-page', $product->slug) }}">
        <div class="position-relative">
            @php
                // Logique pour gérer image (singulier) et images (pluriel)
                $displayImage = asset('images/produit.jpg'); // Par défaut
                
                // Priorité 1: images (array)
                if (isset($product->images) && is_array($product->images) && count($product->images) > 0) {
                    $firstImg = $product->images[0];
                    if (filter_var($firstImg, FILTER_VALIDATE_URL)) {
                        // URL externe
                        $displayImage = $firstImg;
                    } elseif (strpos($firstImg, 'products/') === 0) {
                        // Storage: products/xxx.jpg
                        $displayImage = asset('storage/' . $firstImg);
                    } elseif (str_starts_with($firstImg, 'images/')) {
                        // Public: images/produits/xxx.jpg
                        $displayImage = asset($firstImg);
                    } else {
                        // Autre cas
                        $displayImage = asset($firstImg);
                    }
                }
                // Priorité 2: image (string)
                elseif (isset($product->image) && !empty($product->image)) {
                    if (filter_var($product->image, FILTER_VALIDATE_URL)) {
                        $displayImage = $product->image;
                    } elseif (str_starts_with($product->image, 'storage/')) {
                        $displayImage = asset($product->image);
                    } elseif (strpos($product->image, 'products/') === 0) {
                        $displayImage = asset('storage/' . $product->image);
                    } else {
                        $displayImage = asset($product->image);
                    }
                }
            @endphp
            <img src="{{ $displayImage }}" class="h-200px w-100 object-fit-contain" alt="{{ $product->name }}">
            @if($product->discount_percentage)
            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">-{{ $product->discount_percentage }}%</span>
            @endif
            
            <!-- Bouton Favori -->
            <button class="btn btn-sm position-absolute top-0 end-0 m-2 bg-white border-0 shadow-sm favorite-btn" 
                    data-product-id="{{ $product->id }}" 
                    onclick="event.preventDefault(); toggleFavorite({{ $product->id }}, this)">
                <i class="bi bi-heart fs-6"></i>
            </button>
        </div>
        <div class="py-1">
            <div class="d-flex align-items-center justify-content-start fs-7">
                @if($product->old_price && $product->old_price > $product->price)
                    {{-- Produit en promo: price = prix actuel, old_price = ancien prix --}}
                    <span class="fs-7 text-danger fw-bold text-nowrap me-2">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                    <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">{{ number_format($product->old_price, 0, ',', ' ') }} FCFA</span>
                @else
                    {{-- Produit sans promo: afficher seulement le prix --}}
                    <span class="fs-7 text-danger fw-bold text-nowrap">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                @endif
            </div>
            <p class="fs-7 my-2 orange-color product-name-truncate" title="{{ $product->name }}">{{ $product->name }}</p>
            <div class="hstack gap-1 mb-2">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star {{ $i <= floor($product->rating) ? 'text-warning' : 'text-secondary' }} fs-8"></i>
                @endfor
            </div>
        </div>
    </a>
    
    <!-- Bouton Ajouter au panier -->
    <button class="btn btn-sm orange-bg text-white w-100 add-to-cart-btn" 
            data-product-id="{{ $product->id }}" 
            onclick="event.preventDefault(); addToCart({{ $product->id }})">
        <i class="bi bi-cart-plus me-1"></i>Ajouter au panier
    </button>
</div>

