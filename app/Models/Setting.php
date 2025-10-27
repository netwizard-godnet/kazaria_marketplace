<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    /**
     * Obtenir une valeur de paramètre
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Définir une valeur de paramètre
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $description = null, $isPublic = false)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic
            ]
        );
    }

    /**
     * Caster la valeur selon le type
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Obtenir tous les paramètres d'un groupe
     */
    public static function getGroup($group)
    {
        return self::where('group', $group)->get();
    }

    /**
     * Obtenir tous les paramètres publics
     */
    public static function getPublic()
    {
        return self::where('is_public', true)->get();
    }

    /**
     * Obtenir tous les paramètres publics sous forme de collection clé-valeur
     */
    public static function getPublicSettings()
    {
        return self::where('is_public', true)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
    }

    /**
     * Scope pour les paramètres d'un groupe
     */
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope pour les paramètres publics
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
