<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'category_id',
        'subcategory_id',
        'phone',
        'email',
        'address',
        'city',
        'logo',
        'banner',
        'dfe_document',
        'commerce_register',
        'status',
        'is_verified',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'approved_by',
        'rejected_by',
        'is_official',
        'commission_rate',
        'business_hours',
        'social_links',
        'total_products',
        'total_orders',
        'total_sales',
        'rating',
        'reviews_count',
        'validation_notes',
        'validated_at',
        'validated_by',
        'crm_scoring',
        'crm_commission_rate',
        'crm_kyc_status',
        'crm_validated_at',
        'crm_validated_by',
        'crm_validation_notes',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_official' => 'boolean',
        'commission_rate' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'rating' => 'decimal:2',
        'business_hours' => 'array',
        'social_links' => 'array',
        'validated_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'crm_commission_rate' => 'decimal:2',
        'crm_validated_at' => 'datetime',
        'crm_scoring' => 'decimal:2',
    ];

    protected $appends = [
        'effective_kyc_status',
        'effective_commission_rate',
    ];

    /**
     * Boot function
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
                
                // S'assurer que le slug est unique
                $originalSlug = $store->slug;
                $count = 1;
                while (static::where('slug', $store->slug)->exists()) {
                    $store->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    /**
     * Relation avec l'utilisateur propriétaire
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la catégorie
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation avec la sous-catégorie
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Relation avec les produits
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relation avec les commandes
     */
    public function orders()
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }

    /**
     * Vérifier si la boutique est active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si la boutique est en attente
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si la boutique est rejetée
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Vérifier si la boutique est suspendue
     */
    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    /**
     * Portée pour ne conserver que les boutiques validées par le CRM (ou actives historiquement).
     */
    public function scopeKycValidated($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'active')
              ->orWhereIn('crm_kyc_status', ['active', 'validated', 'approved', 'approve']);
        });
    }

    /**
     * Statut KYC issu du CRM (avec repli sur le statut local).
     */
    public function getEffectiveKycStatusAttribute(): ?string
    {
        return $this->crm_kyc_status ?? $this->status;
    }

    /**
     * Taux de commission effectif (CRM prioritaire).
     */
    public function getEffectiveCommissionRateAttribute(): float
    {
        $crmRate = $this->crm_commission_rate;

        if ($crmRate !== null) {
            return (float) $crmRate;
        }

        return (float) ($this->getRawOriginal('commission_rate') ?? 0);
    }

    /**
     * Détermine si le CRM considère la boutique comme validée.
     */
    public function isKycValidated(): bool
    {
        $status = strtolower($this->effective_kyc_status ?? '');

        return in_array($status, ['active', 'validated', 'valide', 'approve', 'approved', 'actif']);
    }

    /**
     * Détermine si la boutique est en cours de validation côté CRM.
     */
    public function isKycPending(): bool
    {
        $status = strtolower($this->effective_kyc_status ?? '');

        return in_array($status, ['pending', 'in_review', 'processing', 'submitted', 'en_attente', 'waiting', 'attente']);
    }

    /**
     * Détermine si la boutique est rejetée/suspendue côté CRM.
     */
    public function isKycRejected(): bool
    {
        $status = strtolower($this->effective_kyc_status ?? '');

        return in_array($status, ['rejected', 'rejete', 'refused', 'refuse', 'blocked', 'suspended', 'suspendu', 'denied']);
    }

    /**
     * Redéfinit l'attribut is_verified pour refléter le statut CRM.
     */
    public function getIsVerifiedAttribute($value): bool
    {
        if ($this->crm_kyc_status !== null) {
            return $this->isKycValidated();
        }

        return (bool) $value;
    }

    /**
     * Relation avec l'admin qui a validé la boutique
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Utilisateur qui a approuvé la boutique
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Utilisateur qui a rejeté la boutique
     */
    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Obtenir l'URL complète du logo
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return asset('images/logo-orange.png');
        }
        
        // Vérifier si c'est une URL externe
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }
        
        // Vérifier si le fichier existe dans storage
        $storagePath = storage_path('app/public/' . $this->logo);
        if (file_exists($storagePath)) {
            // Utiliser le lien symbolique standard de Laravel
            return asset('storage/' . $this->logo);
        }
        
        // Fallback vers une image par défaut
        return asset('images/logo-orange.png');
    }

    /**
     * Obtenir l'URL complète de la bannière
     */
    public function getBannerUrlAttribute()
    {
        if (!$this->banner) {
            return asset('images/bg-1.jpg');
        }
        
        // Vérifier si c'est une URL externe
        if (filter_var($this->banner, FILTER_VALIDATE_URL)) {
            return $this->banner;
        }
        
        // Vérifier si le fichier existe dans storage
        $storagePath = storage_path('app/public/' . $this->banner);
        if (file_exists($storagePath)) {
            // Utiliser le lien symbolique standard de Laravel
            return asset('storage/' . $this->banner);
        }
        
        // Fallback vers une image par défaut
        return asset('images/bg-1.jpg');
    }

    /**
     * Obtenir l'URL complète du document DFE
     */
    public function getDfeDocumentUrlAttribute()
    {
        if (!$this->dfe_document) {
            return null;
        }
        
        // Vérifier si c'est une URL externe
        if (filter_var($this->dfe_document, FILTER_VALIDATE_URL)) {
            return $this->dfe_document;
        }
        
        // Vérifier si le fichier existe dans storage
        $storagePath = storage_path('app/public/' . $this->dfe_document);
        if (file_exists($storagePath)) {
            return asset('storage/' . $this->dfe_document);
        }
        
        return null;
    }

    /**
     * Obtenir l'URL complète du registre de commerce
     */
    public function getCommerceRegisterUrlAttribute()
    {
        if (!$this->commerce_register) {
            return null;
        }
        
        // Vérifier si c'est une URL externe
        if (filter_var($this->commerce_register, FILTER_VALIDATE_URL)) {
            return $this->commerce_register;
        }
        
        // Vérifier si le fichier existe dans storage
        $storagePath = storage_path('app/public/' . $this->commerce_register);
        if (file_exists($storagePath)) {
            return asset('storage/' . $this->commerce_register);
        }
        
        return null;
    }

    /**
     * Calculer et mettre à jour la note du vendeur basée sur les notes de ses produits
     */
    public function calculateRating()
    {
        // Calculer la note moyenne des produits du vendeur
        $averageRating = $this->products()
            ->where('rating', '>', 0) // Seulement les produits avec des notes
            ->avg('rating') ?? 0;
        
        // Compter le nombre total d'avis sur tous les produits du vendeur
        $totalReviews = \App\Models\Review::whereHas('product', function($query) {
                $query->where('store_id', $this->id);
            })
            ->approved()
            ->count();
        
        $this->update([
            'rating' => round($averageRating, 2),
            'reviews_count' => $totalReviews,
        ]);
        
        return [
            'rating' => round($averageRating, 2),
            'reviews_count' => $totalReviews,
        ];
    }
}
