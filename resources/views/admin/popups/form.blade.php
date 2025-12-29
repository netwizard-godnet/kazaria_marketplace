@extends('admin.layouts.app')

@section('title', $popup->exists ? 'Modifier la popup' : 'Nouvelle popup')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">{{ $popup->exists ? 'Modifier la popup' : 'Nouvelle popup' }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.popups.index') }}">Popups</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>{{ $popup->exists ? 'Modifier' : 'Créer' }}</span>
            </li>
        </ul>
    </div>

    <form action="{{ $popup->exists ? route('admin.popups.update', $popup) : route('admin.popups.store') }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        @if($popup->exists)
            @method('PUT')
        @endif

        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">
                <!-- Contenu -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-edit me-2"></i>Contenu de la popup
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Titre <span class="text-muted">(optionnel)</span></label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $popup->title) }}"
                                   placeholder="Titre de la popup">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Le titre sera affiché en haut de la popup.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug <span class="text-muted">(optionnel)</span></label>
                            <input type="text" 
                                   name="slug" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   value="{{ old('slug', $popup->slug) }}"
                                   placeholder="Laissez vide pour générer automatiquement">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Identifiant unique pour la popup. Généré automatiquement si vide.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contenu HTML <span class="text-muted">(optionnel)</span></label>
                            <textarea name="content" 
                                      class="form-control @error('content') is-invalid @enderror" 
                                      rows="8"
                                      placeholder="Contenu HTML de votre popup...">{{ old('content', $popup->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Vous pouvez inclure du HTML, des images, des liens, etc.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Texte du bouton CTA</label>
                                    <input type="text" 
                                           name="cta_text" 
                                           class="form-control @error('cta_text') is-invalid @enderror" 
                                           value="{{ old('cta_text', $popup->cta_text) }}"
                                           placeholder="Ex: Découvrir l'offre">
                                    @error('cta_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">URL du bouton CTA</label>
                                    <input type="url" 
                                           name="cta_url" 
                                           class="form-control @error('cta_url') is-invalid @enderror" 
                                           value="{{ old('cta_url', $popup->cta_url) }}"
                                           placeholder="https://example.com">
                                    @error('cta_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-image me-2"></i>Image
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($popup->image)
                            <div class="mb-3">
                                <label class="form-label">Image actuelle</label>
                                <div>
                                    <img src="{{ asset('storage/' . $popup->image) }}" 
                                         alt="Image popup" 
                                         class="img-thumbnail" 
                                         style="max-width: 300px; max-height: 300px;">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remove_image" 
                                           id="remove_image" 
                                           value="1">
                                    <label class="form-check-label" for="remove_image">
                                        Supprimer l'image actuelle
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">
                                {{ $popup->image ? 'Remplacer l\'image' : 'Ajouter une image' }}
                            </label>
                            <input type="file" 
                                   name="image" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formats acceptés : JPG, PNG, WEBP, GIF. Taille max : 4MB. Format recommandé : 300x300px (carré).</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lien de l'image (optionnel)</label>
                            <input type="url" 
                                   name="image_url" 
                                   class="form-control @error('image_url') is-invalid @enderror" 
                                   value="{{ old('image_url', $popup->image_url ?? '') }}"
                                   placeholder="https://exemple.com">
                            @error('image_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">URL vers laquelle rediriger lorsque l'utilisateur clique sur l'image. Laissez vide si l'image ne doit pas être cliquable.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Largeur (px)</label>
                                    <input type="number" 
                                           name="width" 
                                           class="form-control @error('width') is-invalid @enderror" 
                                           value="{{ old('width', $popup->width ?? 300) }}"
                                           min="200" 
                                           max="1200" 
                                           required>
                                    @error('width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Largeur de la popup en pixels (200-1200px)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hauteur (px)</label>
                                    <input type="number" 
                                           name="height" 
                                           class="form-control @error('height') is-invalid @enderror" 
                                           value="{{ old('height', $popup->height ?? 300) }}"
                                           min="200" 
                                           max="1200" 
                                           required>
                                    @error('height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Hauteur de la popup en pixels (200-1200px)</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Layout</label>
                            <select name="layout" class="form-select @error('layout') is-invalid @enderror">
                                @foreach($layouts as $value => $label)
                                    <option value="{{ $value }}" {{ old('layout', $popup->layout ?? 'stacked') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('layout')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div class="col-lg-4">
                <!-- Paramètres d'affichage -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-cog me-2"></i>Paramètres
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="is_active" 
                                       id="is_active" 
                                       value="1"
                                       {{ old('is_active', $popup->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Popup active</strong>
                                </label>
                            </div>
                            <small class="text-muted">Activez cette popup pour qu'elle soit affichée sur le site.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priorité</label>
                            <input type="number" 
                                   name="priority" 
                                   class="form-control @error('priority') is-invalid @enderror" 
                                   value="{{ old('priority', $popup->priority) }}"
                                   min="0" 
                                   max="1000">
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Plus la valeur est élevée, plus la popup sera prioritaire.</small>
                        </div>
                    </div>
                </div>

                <!-- Planning -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-calendar me-2"></i>Planning
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Date de début</label>
                            <input type="datetime-local" 
                                   name="display_start" 
                                   class="form-control @error('display_start') is-invalid @enderror" 
                                   value="{{ old('display_start', $popup->display_start?->format('Y-m-d\TH:i')) }}">
                            @error('display_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Laissez vide pour commencer immédiatement.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date de fin</label>
                            <input type="datetime-local" 
                                   name="display_end" 
                                   class="form-control @error('display_end') is-invalid @enderror" 
                                   value="{{ old('display_end', $popup->display_end?->format('Y-m-d\TH:i')) }}">
                            @error('display_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Laissez vide pour afficher indéfiniment.</small>
                        </div>
                    </div>
                </div>

                <!-- Fréquence -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-repeat me-2"></i>Fréquence
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Type de fréquence</label>
                            <select name="frequency" class="form-select @error('frequency') is-invalid @enderror">
                                @foreach($frequencies as $value => $label)
                                    <option value="{{ $value }}" {{ old('frequency', $popup->frequency) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Délai d'affichage (secondes)</label>
                            <input type="number" 
                                   name="delay_seconds" 
                                   class="form-control @error('delay_seconds') is-invalid @enderror" 
                                   value="{{ old('delay_seconds', $popup->delay_seconds) }}"
                                   min="0" 
                                   max="86400">
                            @error('delay_seconds')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Temps d'attente avant l'affichage de la popup.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre max d'affichages</label>
                            <input type="number" 
                                   name="max_impressions" 
                                   class="form-control @error('max_impressions') is-invalid @enderror" 
                                   value="{{ old('max_impressions', $popup->max_impressions) }}"
                                   min="1"
                                   placeholder="Illimité">
                            @error('max_impressions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Laissez vide pour un affichage illimité.</small>
                        </div>
                    </div>
                </div>

                <!-- Pages d'affichage -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-globe me-2"></i>Pages d'affichage
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $selectedPages = old('display_pages', $popup->display_pages ?? []);
                            $pagePresetKeys = array_keys($pagePresets);
                            $presetSelection = array_intersect($selectedPages, $pagePresetKeys);
                            $customPages = array_diff($selectedPages, $pagePresetKeys);
                        @endphp

                        <div class="mb-3">
                            @foreach($pagePresets as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="display_pages[]" 
                                           value="{{ $value }}" 
                                           id="page_{{ $value }}"
                                           {{ in_array($value, $presetSelection) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="page_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URLs personnalisées</label>
                            <textarea name="display_pages_custom" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Ex: /promo, /offre-speciale">{{ implode(', ', $customPages) }}</textarea>
                            <small class="text-muted">Séparez les URLs par des virgules. Ex: /promo, /offre-speciale</small>
                        </div>

                        <small class="text-muted d-block mt-2">
                            <i class="fa fa-info-circle me-1"></i>
                            Laissez tout vide pour afficher sur toutes les pages.
                        </small>
                    </div>
                </div>

                <!-- Appareils -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-mobile-alt me-2"></i>Appareils
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $selectedDevices = old('display_devices', $popup->display_devices ?? []);
                        @endphp

                        @foreach($devices as $value => $label)
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="display_devices[]" 
                                       value="{{ $value }}" 
                                       id="device_{{ $value }}"
                                       {{ in_array($value, $selectedDevices) || empty($selectedDevices) ? 'checked' : '' }}>
                                <label class="form-check-label" for="device_{{ $value }}">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach

                        <small class="text-muted d-block mt-2">
                            <i class="fa fa-info-circle me-1"></i>
                            Cochez tous les appareils pour afficher partout.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.popups.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times me-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i>
                                {{ $popup->exists ? 'Mettre à jour' : 'Créer la popup' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

