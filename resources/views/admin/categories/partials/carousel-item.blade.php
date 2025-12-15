<div class="card mb-3 carousel-item" data-carousel-index="{{ $index }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Carrousel #{{ $index + 1 }}</h6>
        <button type="button" class="btn btn-sm btn-danger remove-carousel-btn">
            <i class="fas fa-trash"></i> Supprimer
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Titre du carrousel</label>
                    <input type="text" class="form-control carousel-title" 
                           value="{{ $carousel['title'] ?? '' }}" 
                           placeholder="Titre du carrousel">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Ordre d'affichage</label>
                    <input type="number" class="form-control carousel-order" 
                           value="{{ $carousel['order'] ?? ($index + 1) }}" 
                           min="1">
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Slides à afficher</label>
                <input type="number" class="form-control carousel-slides-to-show" 
                       value="{{ $carousel['slides_to_show'] ?? 6 }}" min="1" max="12">
            </div>
            <div class="col-md-3">
                <label>Slides (Large)</label>
                <input type="number" class="form-control carousel-slides-lg" 
                       value="{{ $carousel['slides_lg'] ?? 4 }}" min="1" max="12">
            </div>
            <div class="col-md-3">
                <label>Slides (Moyen)</label>
                <input type="number" class="form-control carousel-slides-md" 
                       value="{{ $carousel['slides_md'] ?? 3 }}" min="1" max="12">
            </div>
            <div class="col-md-3">
                <label>Slides (Petit)</label>
                <input type="number" class="form-control carousel-slides-sm" 
                       value="{{ $carousel['slides_sm'] ?? 2 }}" min="1" max="12">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Espacement (gap)</label>
                <input type="number" class="form-control carousel-gap" 
                       value="{{ $carousel['gap'] ?? 0 }}" min="0">
            </div>
            <div class="col-md-4">
                <label>Vitesse autoplay (ms)</label>
                <input type="number" class="form-control carousel-autoplay-speed" 
                       value="{{ $carousel['autoplay_speed'] ?? 2000 }}" min="500">
            </div>
            <div class="col-md-4">
                <div class="form-check mt-4">
                    <input class="form-check-input carousel-autoplay" type="checkbox" 
                           {{ ($carousel['autoplay'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label">Autoplay</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input carousel-pause-on-hover" type="checkbox" 
                           {{ ($carousel['pause_on_hover'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label">Pause au survol</label>
                </div>
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Images du carrousel</h6>
            <button type="button" class="btn btn-sm btn-primary add-carousel-image-btn">
                <i class="fas fa-plus"></i> Ajouter une image
            </button>
        </div>

        <div class="carousel-images-container">
            @php
                $carouselImages = $carousel['images'] ?? [];
            @endphp
            @if(!empty($carouselImages))
                @foreach($carouselImages as $imgIndex => $image)
                    <div class="card mb-2 carousel-image-item" data-image-index="{{ $imgIndex }}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    @if(isset($image['url']) && $image['url'])
                                        <img src="{{ str_starts_with($image['url'], 'http') ? $image['url'] : (str_starts_with($image['url'], 'images/') ? asset($image['url']) : asset('storage/' . $image['url'])) }}" 
                                             class="img-thumbnail carousel-image-preview" 
                                             style="max-width: 100px; max-height: 80px;">
                                    @else
                                        <div class="carousel-image-preview-container" style="display: none;">
                                            <img src="" class="img-thumbnail carousel-image-preview" style="max-width: 100px; max-height: 80px;">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control form-control-sm mt-2 carousel-image-input" accept="image/*">
                                    <input type="hidden" class="carousel-image-url" value="{{ $image['url'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label>URL du lien</label>
                                    <input type="url" class="form-control form-control-sm carousel-image-link-url" 
                                           value="{{ $image['link_url'] ?? '' }}" 
                                           placeholder="https://example.com">
                                </div>
                                <div class="col-md-2">
                                    <label>Cible</label>
                                    <select class="form-control form-control-sm carousel-image-link-target">
                                        <option value="_blank" {{ ($image['link_target'] ?? '_blank') === '_blank' ? 'selected' : '' }}>Nouvel onglet</option>
                                        <option value="_self" {{ ($image['link_target'] ?? '_blank') === '_self' ? 'selected' : '' }}>Même onglet</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Texte alternatif</label>
                                    <input type="text" class="form-control form-control-sm carousel-image-alt" 
                                           value="{{ $image['alt'] ?? '' }}" 
                                           placeholder="Description">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-carousel-image-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="form-check mt-3">
            <input class="form-check-input carousel-enabled" type="checkbox" 
                   {{ ($carousel['enabled'] ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Carrousel activé</label>
        </div>
    </div>
</div>

