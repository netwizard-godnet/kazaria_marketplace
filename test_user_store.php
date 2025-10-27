<?php

echo "Début du script...\n";

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

echo "App chargée...\n";

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Bootstrap terminé...\n";
echo "Test des utilisateurs et leurs boutiques:\n\n";

// Récupérer tous les utilisateurs vendeurs
$users = \App\Models\User::where('is_seller', true)->with('store')->get();

foreach ($users as $user) {
    echo "=== Utilisateur ID: {$user->id} ===\n";
    echo "Nom: {$user->prenoms} {$user->nom}\n";
    echo "Email: {$user->email}\n";
    echo "is_seller: " . ($user->is_seller ? 'true' : 'false') . "\n";
    echo "Store: " . ($user->store ? $user->store->name . " (ID: " . $user->store->id . ", Status: " . $user->store->status . ")" : 'Aucune') . "\n";
    
    // Tester la logique de redirection
    if ($user->store) {
        $store = $user->store;
        switch ($store->status) {
            case 'active':
                echo "→ Redirection vers: store.dashboard\n";
                break;
            case 'pending':
                echo "→ Redirection vers: store.pending\n";
                break;
            case 'rejected':
                echo "→ Redirection vers: store.rejected\n";
                break;
            default:
                echo "→ Statut inconnu: {$store->status}\n";
                break;
        }
    } else {
        echo "→ Pas de boutique - Peut créer\n";
    }
    echo "---\n\n";
}