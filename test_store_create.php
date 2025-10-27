<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Test de l'accès à /store/create:\n\n";

// Simuler une requête vers /store/create
$request = \Illuminate\Http\Request::create('/store/create', 'GET');

// Récupérer l'utilisateur admin (ID: 11) qui a une boutique active
$user = \App\Models\User::find(11);
$user->load('store');

echo "Utilisateur simulé:\n";
echo "ID: {$user->id}\n";
echo "Email: {$user->email}\n";
echo "is_seller: " . ($user->is_seller ? 'true' : 'false') . "\n";
echo "Store: " . ($user->store ? $user->store->name . " (Status: " . $user->store->status . ")" : 'Aucune') . "\n\n";

// Simuler l'authentification
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Appeler le contrôleur
$controller = new \App\Http\Controllers\StoreController();
$response = $controller->create($request);

echo "Réponse du contrôleur:\n";
echo "Type: " . get_class($response) . "\n";

if ($response instanceof \Illuminate\Http\RedirectResponse) {
    echo "Redirection vers: " . $response->getTargetUrl() . "\n";
    echo "Status: " . $response->getStatusCode() . "\n";
} else {
    echo "Vue retournée (pas de redirection)\n";
}