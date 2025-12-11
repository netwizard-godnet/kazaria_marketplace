@php
use App\Models\Setting;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Paramètres du site')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Paramètres du site</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Paramètres</span></li>
        </ul>
    </div>

    {{-- Messages de validation --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Les paramètres n'ont pas pu être mis à jour :
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                                <div class="row">
            @php
                // Définir l'ordre d'affichage des groupes
                $groupOrder = ['general', 'contact', 'ecommerce', 'deals', 'homepage', 'social', 'maintenance', 'cinetpay', 'stripe'];
                // Réorganiser les groupes selon l'ordre défini
                $orderedGroups = [];
                foreach ($groupOrder as $orderedGroupName) {
                    if (isset($groups[$orderedGroupName])) {
                        $orderedGroups[$orderedGroupName] = $groups[$orderedGroupName];
                    }
                }
                // Ajouter les groupes qui ne sont pas dans la liste (en fin)
                foreach ($groups as $groupName => $groupSettings) {
                    if (!in_array($groupName, $groupOrder)) {
                        $orderedGroups[$groupName] = $groupSettings;
                    }
                }
            @endphp
            
            @php
                // Séparer les groupes de paiement des autres
                $paymentGroups = [];
                $otherGroups = [];
                foreach ($orderedGroups as $groupName => $groupSettings) {
                    if ($groupName === 'cinetpay' || $groupName === 'stripe') {
                        $paymentGroups[$groupName] = $groupSettings;
                    } else {
                        $otherGroups[$groupName] = $groupSettings;
                    }
                }
            @endphp
            
            {{-- Afficher les groupes non-paiement --}}
            @foreach($otherGroups as $groupName => $groupSettings)
                @php
                    $isGeneral = ($groupName === 'general');
                    $columnClass = $isGeneral ? 'col-12' : 'col-md-6';
                @endphp
            <div class="{{ $columnClass }} mb-4">
                <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                            @switch($groupName)
                                @case('general')
                                    <i class="fas fa-cog me-2"></i>Général
                                    @break
                                @case('contact')
                                    <i class="fas fa-phone me-2"></i>Contact
                                    @break
                                @case('social')
                                    <i class="fas fa-share-alt me-2"></i>Réseaux sociaux
                                    @break
                                @case('ecommerce')
                                    <i class="fas fa-shopping-cart me-2"></i>E-commerce
                                    @break
                                @case('deals')
                                    <i class="fas fa-fire me-2"></i>Deals du jour
                                    @break
                                @case('homepage')
                                    <i class="fas fa-home me-2"></i>Page d'accueil
                                    @break
                                @case('maintenance')
                                    <i class="fas fa-tools me-2"></i>Maintenance
                                    @break
                                @case('cinetpay')
                                    <i class="fas fa-credit-card me-2"></i>CinetPay
                                    @break
                                @case('stripe')
                                    <i class="fab fa-stripe me-2"></i>Stripe
                                    @break
                                @default
                                    <i class="fas fa-cog me-2"></i>{{ ucfirst($groupName) }}
                            @endswitch
                                </h5>
                            </div>
                            <div class="card-body">
                        @php 
                            $isGeneral = ($groupName === 'general');
                            // Clés traitées comme booléens même si le type n'est pas "boolean" en BDD
                            $booleanKeys = [
                                'email_notifications',
                                'push_notifications',
                                'maintenance_mode',
                                'landing_page_enabled',
                            ];
                        @endphp
                        @if($isGeneral)
                                <div class="row">
                        @endif
                        @foreach($groupSettings as $setting)
@php
    $label = $setting->description ?: match($setting->key) {
                                // Général
                                'site_name' => 'Nom du site',
                                'site_description' => 'Description du site',
                                'site_keywords' => 'Mots-clés SEO',
                                'site_logo' => 'Logo du site',
                                'site_favicon' => 'Favicon du site',
                                // Contact
                                'contact_email' => 'Email de contact',
                                'contact_phone' => 'Téléphone de contact',
                                'contact_address' => 'Adresse de contact',
                                // E‑commerce
                                'currency' => 'Devise (code)',
                                'currency_symbol' => 'Symbole de la devise',
                                'min_order_quantity' => 'Quantité minimale de commande',
                                'shipping_cost' => 'Coût de livraison (FCFA)',
                                'free_shipping_threshold' => 'Seuil de livraison gratuite (FCFA)',
                                // Deals
                                'deals_countdown_duration' => 'Durée du countdown des deals (en minutes)',
                                'deals_min_discount' => 'Pourcentage de remise minimum (%)',
                                'deals_max_discount' => 'Pourcentage de remise maximum (%)',
                                'deals_categories' => 'Catégories des deals',
                                'deals_subcategories' => 'Sous-catégories des deals',
                                // Page d'accueil
                                'homepage_categories' => 'Catégories à afficher sur la page d\'accueil',
                                'homepage_subcategories' => 'Sous-catégories à afficher sur la page d\'accueil',
                                'homepage_category_sections' => 'Sections de produits sur la page d\'accueil',
                                // Réseaux sociaux
                                'social_facebook' => 'Page Facebook',
                                'social_twitter' => 'Compte Twitter/X',
                                'social_instagram' => 'Compte Instagram',
                                'social_linkedin' => 'Page LinkedIn',
                                // Maintenance
                                'maintenance_mode' => 'Mode maintenance',
                                'maintenance_message' => 'Message de maintenance',
                                'landing_page_enabled' => 'Activer la landing page',
                                'landing_page_launch_date' => 'Date de lancement (Y-m-d H:i:s)',
                                // Paiement (CinetPay)
                                'cinetpay_api_key' => 'Clé API CinetPay',
                                'cinetpay_site_id' => 'ID du site CinetPay',
                                'cinetpay_currency' => 'Devise CinetPay',
                                // Paiement (Stripe)
                                'stripe_public_key' => 'Clé publique Stripe',
                                'stripe_secret_key' => 'Clé secrète Stripe',
                                // Autres paramètres (généraux / sécurité / email / commandes)
                                'default_commission_rate' => 'Taux de commission par défaut (%)',
                                'default_shipping_cost' => 'Frais de livraison par défaut (FCFA)',
                                'mail_from_address' => 'Adresse expéditeur (email)',
                                'mail_from_name' => 'Nom expéditeur (email)',
                                'mail_support_address' => 'Adresse support (email)',
                                'max_login_attempts' => 'Tentatives de connexion max',
                                'min_order_amount' => 'Montant minimum de commande (FCFA)',
                                'password_min_length' => 'Longueur minimale du mot de passe',
                                'push_notifications' => 'Notifications push',
                                'site_address' => 'Adresse du site',
                                'site_email' => 'Email du site',
                                'site_phone' => 'Téléphone du site',
                                default => ucfirst(str_replace('_', ' ', $setting->key)),
                            };
                        @endphp
                        <div class="form-group mb-3 {{ $isGeneral ? 'col-md-6' : '' }}">
                            <label for="setting_{{ $setting->key }}" class="form-label" style="word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;">
                                {{ $label }}
                                @if($setting->is_public)
                                    <span class="badge badge-success badge-sm">Public</span>
                                @endif
                            </label>
                            
                            @if($setting->key === 'deals_categories' || $setting->key === 'homepage_categories')
                                <select class="form-control" id="setting_{{ $setting->key }}" name="settings[{{ $setting->key }}][]" multiple>
                                    <option value="">Toutes les catégories</option>
                                    @foreach(\App\Models\Category::active()->ordered()->get() as $category)
                                        <option value="{{ $category->id }}" {{ in_array($category->id, $setting->value ? explode(',', $setting->value) : []) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    @if($setting->key === 'homepage_categories')
                                        Sélectionnez les catégories à afficher dans la section "Top Catégories du Mois" sur la page d'accueil. Si aucune sélection, les catégories les plus visitées seront affichées automatiquement.
                                    @else
                                        Sélectionnez plusieurs catégories en maintenant Ctrl (Cmd sur Mac)
                                    @endif
                                </small>
                            @elseif($setting->key === 'homepage_category_sections')
                                @php
                                    // Parser les valeurs existantes pour déterminer ce qui est sélectionné
                                    $selectedValues = [];
                                    if ($setting->value) {
                                        $items = array_map('trim', explode(',', $setting->value));
                                        foreach ($items as $item) {
                                            if (str_starts_with($item, 'category:') || str_starts_with($item, 'subcategory:')) {
                                                $selectedValues[] = $item;
                                            } elseif (is_numeric($item)) {
                                                // Rétrocompatibilité: ancien format
                                                $selectedValues[] = 'category:' . $item;
                                            }
                                        }
                                    }
                                @endphp
                                <select class="form-control" id="setting_{{ $setting->key }}" name="settings[{{ $setting->key }}][]" multiple size="10">
                                    <option value="">Aucune section (masquer toutes les sections)</option>
                                    <optgroup label="Catégories">
                                        @foreach(\App\Models\Category::active()->ordered()->get() as $category)
                                            @php
                                                $value = 'category:' . $category->id;
                                                $isSelected = in_array($value, $selectedValues) || in_array($category->id, $selectedValues);
                                            @endphp
                                            <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                                                📁 {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Sous-catégories">
                                        @foreach(\App\Models\Subcategory::active()->with('category')->ordered()->get() as $subcategory)
                                            @php
                                                $value = 'subcategory:' . $subcategory->id;
                                                $isSelected = in_array($value, $selectedValues);
                                            @endphp
                                            <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                                                📂 {{ $subcategory->category->name ?? 'N/A' }} > {{ $subcategory->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <small class="text-muted">
                                    Sélectionnez les catégories et/ou sous-catégories à afficher comme sections de produits sur la page d'accueil. L'ordre de sélection détermine l'ordre d'affichage. Si aucune sélection, les 4 catégories par défaut seront affichées (Téléphones, TV, Electroménager, Ordinateurs).
                                </small>
                            @elseif($setting->key === 'deals_subcategories' || $setting->key === 'homepage_subcategories')
                                <select class="form-control" id="setting_{{ $setting->key }}" name="settings[{{ $setting->key }}][]" multiple>
                                    <option value="">Toutes les sous-catégories</option>
                                    @foreach(\App\Models\Subcategory::active()->ordered()->get() as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ in_array($subcategory->id, $setting->value ? explode(',', $setting->value) : []) ? 'selected' : '' }}>
                                            {{ $subcategory->category->name ?? 'N/A' }} > {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    @if($setting->key === 'homepage_subcategories')
                                        Sélectionnez les sous-catégories à afficher dans la section "Top Catégories du Mois" sur la page d'accueil. Si aucune sélection, les sous-catégories les plus visitées seront affichées automatiquement.
                                    @else
                                        Sélectionnez plusieurs sous-catégories en maintenant Ctrl (Cmd sur Mac)
                                    @endif
                                </small>
                            @elseif($setting->type === 'boolean' || in_array($setting->key, $booleanKeys))
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_{{ $setting->key }}_1"
                                               name="settings[{{ $setting->key }}]"
                                               value="1" {{ ((string)$setting->value === '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="setting_{{ $setting->key }}_1">Oui</label>
                            </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_{{ $setting->key }}_0"
                                               name="settings[{{ $setting->key }}]"
                                               value="0" {{ ((string)$setting->value === '0') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="setting_{{ $setting->key }}_0">Non</label>
                                        </div>
                                    </div>
                            @elseif($setting->key === 'landing_page_launch_date')
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value ? \Carbon\Carbon::parse($setting->value)->format('Y-m-d\TH:i') : '' }}"
                                       placeholder="2025-12-31 23:59:59">
                                <small class="text-muted">Format: Y-m-d H:i:s (ex: 2025-12-31 23:59:59)</small>
                            @elseif($setting->type === 'integer' || $setting->type === 'float')
                                <input type="number" 
                                       class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value }}"
                                       step="{{ $setting->type === 'float' ? '0.01' : '1' }}">
                            @elseif($setting->type === 'array' || $setting->type === 'json')
                                <textarea class="form-control" 
                                          id="setting_{{ $setting->key }}" 
                                          name="settings[{{ $setting->key }}]" 
                                          rows="3">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                            @else
                                <input type="text" 
                                       class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value }}">
                            @endif
                                        </div>
                        @endforeach
                        @if($isGeneral)
                                    </div>
                        @endif
                                        </div>
                                    </div>
                                </div>
            @endforeach
            
            {{-- Section spéciale pour les groupes de paiement (CinetPay et Stripe) côte à côte --}}
            @if(count($paymentGroups) > 0)
                <div class="col-12 mb-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-money-bill-wave me-2"></i>Configuration des passerelles de paiement
                    </h6>
                </div>
                @foreach($paymentGroups as $groupName => $groupSettings)
            <div class="col-md-6 mb-4">
                <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                            @switch($groupName)
                                @case('cinetpay')
                                    <i class="fas fa-credit-card me-2"></i>CinetPay
                                    @break
                                @case('stripe')
                                    <i class="fab fa-stripe me-2"></i>Stripe
                                    @break
                                @default
                                    <i class="fas fa-cog me-2"></i>{{ ucfirst($groupName) }}
                            @endswitch
                                </h5>
                            </div>
                            <div class="card-body">
                        @php 
                            $isGeneral = false; // Jamais général pour les paiements
                            // Clés traitées comme booléens même si le type n'est pas "boolean" en BDD
                            $booleanKeys = [
                                'email_notifications',
                                'push_notifications',
                                'maintenance_mode',
                                'landing_page_enabled',
                            ];
                        @endphp
                        @foreach($groupSettings as $setting)
@php
    $label = $setting->description ?: match($setting->key) {
                                // Général
                                'site_name' => 'Nom du site',
                                'site_description' => 'Description du site',
                                'site_keywords' => 'Mots-clés SEO',
                                'site_logo' => 'Logo du site',
                                'site_favicon' => 'Favicon du site',
                                // Contact
                                'contact_email' => 'Email de contact',
                                'contact_phone' => 'Téléphone de contact',
                                'contact_address' => 'Adresse de contact',
                                // E‑commerce
                                'currency' => 'Devise (code)',
                                'currency_symbol' => 'Symbole de la devise',
                                'min_order_quantity' => 'Quantité minimale de commande',
                                'shipping_cost' => 'Coût de livraison (FCFA)',
                                'free_shipping_threshold' => 'Seuil de livraison gratuite (FCFA)',
                                // Deals
                                'deals_countdown_duration' => 'Durée du countdown des deals (en minutes)',
                                'deals_min_discount' => 'Pourcentage de remise minimum (%)',
                                'deals_max_discount' => 'Pourcentage de remise maximum (%)',
                                'deals_categories' => 'Catégories des deals',
                                'deals_subcategories' => 'Sous-catégories des deals',
                                // Page d'accueil
                                'homepage_categories' => 'Catégories à afficher sur la page d\'accueil',
                                'homepage_subcategories' => 'Sous-catégories à afficher sur la page d\'accueil',
                                'homepage_category_sections' => 'Sections de produits sur la page d\'accueil',
                                // Réseaux sociaux
                                'social_facebook' => 'Page Facebook',
                                'social_twitter' => 'Compte Twitter/X',
                                'social_instagram' => 'Compte Instagram',
                                'social_linkedin' => 'Page LinkedIn',
                                // Maintenance
                                'maintenance_mode' => 'Mode maintenance',
                                'maintenance_message' => 'Message de maintenance',
                                'landing_page_enabled' => 'Activer la landing page',
                                'landing_page_launch_date' => 'Date de lancement (Y-m-d H:i:s)',
                                // Paiement (CinetPay)
                                'cinetpay_api_key' => 'Clé API CinetPay',
                                'cinetpay_site_id' => 'ID du site CinetPay',
                                'cinetpay_currency' => 'Devise CinetPay',
                                // Paiement (Stripe)
                                'stripe_public_key' => 'Clé publique Stripe',
                                'stripe_secret_key' => 'Clé secrète Stripe',
                                // Autres paramètres (généraux / sécurité / email / commandes)
                                'default_commission_rate' => 'Taux de commission par défaut (%)',
                                'default_shipping_cost' => 'Frais de livraison par défaut (FCFA)',
                                'mail_from_address' => 'Adresse expéditeur (email)',
                                'mail_from_name' => 'Nom expéditeur (email)',
                                'mail_support_address' => 'Adresse support (email)',
                                'max_login_attempts' => 'Tentatives de connexion max',
                                'min_order_amount' => 'Montant minimum de commande (FCFA)',
                                'password_min_length' => 'Longueur minimale du mot de passe',
                                'push_notifications' => 'Notifications push',
                                'site_address' => 'Adresse du site',
                                'site_email' => 'Email du site',
                                'site_phone' => 'Téléphone du site',
                                default => ucfirst(str_replace('_', ' ', $setting->key)),
                            };
                        @endphp
                        <div class="form-group mb-3">
                            <label for="setting_{{ $setting->key }}" class="form-label">
                                {{ $label }}
                                @if($setting->is_public)
                                    <span class="badge badge-success badge-sm">Public</span>
                                @endif
                            </label>
                            
                            @if($setting->type === 'boolean' || in_array($setting->key, $booleanKeys))
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_{{ $setting->key }}_1"
                                               name="settings[{{ $setting->key }}]"
                                               value="1" {{ ((string)$setting->value === '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="setting_{{ $setting->key }}_1">Oui</label>
                            </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_{{ $setting->key }}_0"
                                               name="settings[{{ $setting->key }}]"
                                               value="0" {{ ((string)$setting->value === '0') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="setting_{{ $setting->key }}_0">Non</label>
                                        </div>
                                    </div>
                            @elseif($setting->key === 'landing_page_launch_date')
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value ? \Carbon\Carbon::parse($setting->value)->format('Y-m-d\TH:i') : '' }}"
                                       placeholder="2025-12-31 23:59:59">
                                <small class="text-muted">Format: Y-m-d H:i:s (ex: 2025-12-31 23:59:59)</small>
                            @elseif($setting->type === 'integer' || $setting->type === 'float')
                                <input type="number" 
                                       class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value }}"
                                       step="{{ $setting->type === 'float' ? '0.01' : '1' }}">
                            @elseif($setting->type === 'array' || $setting->type === 'json')
                                <textarea class="form-control" 
                                          id="setting_{{ $setting->key }}" 
                                          name="settings[{{ $setting->key }}]" 
                                          rows="3">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                            @else
                                <input type="text" 
                                       class="form-control" 
                                       id="setting_{{ $setting->key }}" 
                                       name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value }}">
                            @endif
                                        </div>
                        @endforeach
                                        </div>
                                    </div>
                                </div>
                @endforeach
            @endif
                        </div>

        <!-- Section spéciale pour les fichiers -->
                                <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                            <i class="fas fa-image me-2"></i>Images
                                </h5>
                            </div>
                    <div class="card-body bg-success-subtle">
                        <div class="form-group mb-3">
                            <label for="logo" class="form-label">Logo du site</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            @php $siteLogo = Setting::get('site_logo'); @endphp
                            @if($siteLogo)
                                <div class="mt-2">
                                    @php 
                                        $logoPath = ltrim($siteLogo, '/');
                                        $logoExists = Storage::disk('public')->exists($logoPath);
                                        // Ajouter un timestamp pour cache-busting
                                        $logoUrl = $logoExists ? asset('storage/' . $logoPath . '?v=' . time()) : null;
                                    @endphp
                                    @if($logoExists)
                                    <img src="{{ $logoUrl }}" 
                                         alt="Logo actuel" 
                                         style="max-height: 50px;"
                                         class="img-thumbnail"
                                         onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                                    @else
                                    <small class="text-danger d-block">Fichier introuvable: <code>{{ $siteLogo }}</code></small>
                                    @endif
                                    <small class="text-muted d-block">Logo actuel</small>
                                        </div>
                            @endif
                        </div>

                        <div class="form-group mb-3">
                            <label for="favicon" class="form-label">Favicon</label>
                            <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                            @php $siteFavicon = Setting::get('site_favicon'); @endphp
                            @if($siteFavicon)
                                <div class="mt-2">
                                    @php 
                                        $faviconPath = ltrim($siteFavicon, '/');
                                        $faviconExists = Storage::disk('public')->exists($faviconPath);
                                        // Ajouter un timestamp pour cache-busting
                                        $faviconUrl = $faviconExists ? asset('storage/' . $faviconPath . '?v=' . time()) : null;
                                    @endphp
                                    @if($faviconExists)
                                    <img src="{{ $faviconUrl }}" 
                                         alt="Favicon actuel" 
                                         style="max-height: 32px;"
                                         class="img-thumbnail"
                                         onerror="this.onerror=null; this.src='{{ asset('favicon.png') }}';">
                                    @else
                                    <small class="text-danger d-block">Fichier introuvable: <code>{{ $siteFavicon }}</code></small>
                                    @endif
                                    <small class="text-muted d-block">Favicon actuel</small>
                            </div>
                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                                <div class="row">
            <div class="col-12">
                        <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                                        </button>
                        <button type="button" class="btn btn-warning btn-lg" onclick="resetSettings()">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

<!-- Modal de confirmation pour la réinitialisation -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetModalLabel">Confirmer la réinitialisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir réinitialiser tous les paramètres aux valeurs par défaut ? 
                Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="{{ route('admin.settings.reset') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">Réinitialiser</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetSettings() {
    const modal = new bootstrap.Modal(document.getElementById('resetModal'));
    modal.show();
}

// Aperçu des images
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Supprimer l'ancien aperçu s'il existe
            const oldPreview = e.target.parentNode.querySelector('div.preview-logo');
            if (oldPreview) {
                oldPreview.remove();
            }
            
            const preview = document.createElement('div');
            preview.className = 'mt-2 preview-logo';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Aperçu" style="max-height: 50px; border: 2px solid #28a745;">
                <small class="text-success d-block">Aperçu du nouveau logo</small>
            `;
            e.target.parentNode.appendChild(preview);
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('favicon').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Supprimer l'ancien aperçu s'il existe
            const oldPreview = e.target.parentNode.querySelector('div.preview-favicon');
            if (oldPreview) {
                oldPreview.remove();
            }
            
            const preview = document.createElement('div');
            preview.className = 'mt-2 preview-favicon';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Aperçu" style="max-height: 32px; border: 2px solid #28a745;">
                <small class="text-success d-block">Aperçu du nouveau favicon</small>
            `;
            e.target.parentNode.appendChild(preview);
        };
        reader.readAsDataURL(file);
    }
});

// Recharger les images après soumission du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="POST"][action="{{ route('admin.settings.update') }}"]');
    if (form) {
        form.addEventListener('submit', function() {
            // Délai pour permettre la mise à jour du serveur
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        });
    }
});
</script>
@endpush