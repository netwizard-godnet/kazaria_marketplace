<?php

if (!function_exists('canAccess')) {
    /**
     * Vérifier si l'utilisateur actuel a la permission d'accéder à une ressource
     * 
     * @param string $permission Le slug de la permission à vérifier
     * @return bool
     */
    function canAccess(string $permission): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Si l'utilisateur est super admin (is_admin = true)
        // On vérifie quand même ses permissions via son rôle si défini
        if ($user->is_admin) {
            // Si pas de rôle assigné, accès total (ancien système)
            if (!$user->role_id) {
                return true;
            }
            
            // Sinon, vérifier les permissions du rôle
            $user->load('role.permissions');
            
            if ($user->role && $user->role->hasPermission($permission)) {
                return true;
            }
            
            // Si le super admin n'a pas la permission via son rôle, on refuse
            // (pour forcer l'utilisation du système de permissions)
            return false;
        }
        
        // Pour les utilisateurs non super admin, vérifier le rôle et les permissions
        if (!$user->role_id) {
            return false;
        }
        
        $user->load('role.permissions');
        
        return $user->role && $user->role->hasPermission($permission);
    }
}

if (!function_exists('canAccessAny')) {
    /**
     * Vérifier si l'utilisateur a au moins une des permissions listées
     * 
     * @param array $permissions Liste des slugs de permissions
     * @return bool
     */
    function canAccessAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (canAccess($permission)) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Vérifier si l'utilisateur est un super admin sans rôle spécifique
     * (ancien système)
     * 
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        $user = auth()->user();
        
        return $user && $user->is_admin && !$user->role_id;
    }
}

