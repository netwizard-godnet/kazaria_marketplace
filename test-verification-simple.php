<?php

/**
 * Test simple de vérification d'email
 * Usage: php test-verification-simple.php email@example.com
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Test de Vérification Email Simple ===\n\n";

// Obtenir l'email depuis la ligne de commande
$email = $argv[1] ?? null;

if (!$email) {
    echo "❌ Usage: php test-verification-simple.php email@example.com\n";
    echo "\nExemple: php test-verification-simple.php test@example.com\n";
    exit(1);
}

// Chercher l'utilisateur
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ Aucun utilisateur trouvé avec l'email: {$email}\n";
    echo "\nUtilisateurs existants:\n";
    $users = User::all();
    foreach ($users as $u) {
        echo "  - {$u->email} (ID: {$u->id})\n";
    }
    exit(1);
}

echo "✅ Utilisateur trouvé:\n";
echo "  ID: {$user->id}\n";
echo "  Nom: {$user->prenoms} {$user->nom}\n";
echo "  Email: {$user->email}\n";
echo "  Vérifié: " . ($user->email_verified_at ? 'OUI ✓' : 'NON ✗') . "\n";
echo "\n";

// Générer le hash
$hash = sha1($user->email);

echo "📧 Lien de vérification:\n";
echo str_repeat("-", 70) . "\n";

$baseUrl = config('app.url');
$verificationLink = "{$baseUrl}/email/verify/{$user->id}/{$hash}";

echo $verificationLink . "\n\n";

echo "💡 Instructions:\n";
echo "1. Copiez le lien ci-dessus\n";
echo "2. Collez-le dans votre navigateur\n";
echo "3. Appuyez sur Entrée\n";
echo "4. L'email devrait être vérifié !\n\n";

// Option pour vérifier directement
echo "Voulez-vous vérifier l'email MAINTENANT ? (o/n): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));

if (strtolower($response) === 'o' || strtolower($response) === 'oui' || strtolower($response) === 'y') {
    echo "\nVérification en cours...\n";
    
    if ($user->hasVerifiedEmail()) {
        echo "ℹ️ L'email est déjà vérifié.\n";
    } else {
        $user->email_verified_at = now();
        $user->is_verified = true;
        $user->save();
        
        echo "✅ Email vérifié avec succès !\n";
        echo "Vous pouvez maintenant vous connecter.\n";
    }
} else {
    echo "\nUtilisez le lien ci-dessus dans votre navigateur.\n";
}

echo "\n=== Fin du Test ===\n";

