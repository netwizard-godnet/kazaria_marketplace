<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'email_verified_at',
        'email_verification_token',
        'telephone',
        'telephone_verified_at',
        'profile_pic_url',
        'is_verified',
        'is_seller',
        'is_admin',
        'role_id',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'bio',
        'newsletter',
        'termes_condition',
        'statut',
        'password',
        'password_reset_token',
        'password_reset_expires_at',
        'auth_code',
        'auth_code_expires_at',
        'auth_code_verified',
        'provider_name',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'auth_code',
        'password_reset_token',
        'email_verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'telephone_verified_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'auth_code_expires_at' => 'datetime',
            'is_verified' => 'boolean',
            'is_seller' => 'boolean',
            'newsletter' => 'boolean',
            'termes_condition' => 'boolean',
            'auth_code_verified' => 'boolean',
        ];
    }

    /**
     * Accessor pour l'URL de la photo de profil
     */
    public function getProfilePicUrlAttribute($value)
    {
        // Toujours retourner la valeur brute telle quelle
        // L'URL sera construite dans la vue avec /storage/
        return $value;
    }

    /**
     * Générer un code d'authentification à 8 chiffres
     */
    public function generateAuthCode(): string
    {
        $code = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        $this->update([
            'auth_code' => $code,
            'auth_code_expires_at' => Carbon::now()->addMinutes(15),
            'auth_code_verified' => false,
        ]);

        return $code;
    }

    /**
     * Vérifier si le code d'authentification est valide
     */
    public function verifyAuthCode(string $code): bool
    {
        if (!$this->auth_code || !$this->auth_code_expires_at) {
            return false;
        }

        if (Carbon::now()->isAfter($this->auth_code_expires_at)) {
            return false;
        }

        if ($this->auth_code !== $code) {
            return false;
        }

        $this->update([
            'auth_code_verified' => true,
            'auth_code' => null,
            'auth_code_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Vérifier si le code d'authentification a expiré
     */
    public function hasExpiredAuthCode(): bool
    {
        if (!$this->auth_code_expires_at) {
            return true;
        }

        return Carbon::now()->isAfter($this->auth_code_expires_at);
    }

    /**
     * Relation avec la boutique
     */
    /**
     * Relation avec le rôle
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permissionSlug)
    {
        if (!$this->role) {
            return false;
        }
        
        return $this->role->hasPermission($permissionSlug);
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($roleSlug)
    {
        if (!$this->role) {
            return false;
        }
        
        return $this->role->slug === $roleSlug;
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    /**
     * Relation avec les commandes
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relation avec les avis
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relation avec les favoris
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Relation avec les articles du panier
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Vérifier si l'utilisateur est vendeur
     */
    public function isSeller(): bool
    {
        return $this->is_seller && $this->store()->exists();
    }

    /**
     * Vérifier si l'utilisateur a une boutique active
     */
    public function hasActiveStore(): bool
    {
        return $this->store && $this->store->isActive();
    }
}
