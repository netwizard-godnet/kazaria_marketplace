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
        'telephone',
        'telephone_verified_at',
        'profile_pic_url',
        'is_verified',
        'adresse',
        'newsletter',
        'termes_condition',
        'statut',
        'password',
        'auth_code',
        'auth_code_expires_at',
        'auth_code_verified',
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
            'auth_code_expires_at' => 'datetime',
            'is_verified' => 'boolean',
            'newsletter' => 'boolean',
            'termes_condition' => 'boolean',
            'auth_code_verified' => 'boolean',
        ];
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
}
