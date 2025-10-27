<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Test avec un utilisateur sans boutique:\n\n";

// Créer un utilisateur test sans boutique
$user = \App\Models\User::create([
    'prenoms' => 'Test',
    'nom' => 'User',
    'email' => 'test.user@example.com',
    'password' => bcrypt('password'),
    'is_seller' => true,
    'phone' => '1234567890'
]);

echo "Utilisateur créé:\n";
echo "ID: {$user->id}\n";
echo "Email: {$user->email}\n";
echo "is_seller: " . ($user->is_seller ? 'true' : 'false') . "\n";
echo "Store: " . ($user->store ? $user->store->name : 'Aucune') . "\n\n";

// Simuler une requête vers /store/create
$request = \Illuminate\Http\Request::create('/store/create', 'GET');
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
} else {
    echo "Vue retournée (peut créer une boutique)\n";
}

// Nettoyer
$user->delete();
echo "\nUtilisateur test supprimé.\n";

