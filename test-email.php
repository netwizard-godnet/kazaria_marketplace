<?php

// Test rapide de configuration email pour KAZARIA
// Exécuter avec: php test-email.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "=== Test Configuration Email KAZARIA ===\n\n";

// Afficher la configuration
echo "Configuration Mail:\n";
echo "Host: " . Config::get('mail.mailers.smtp.host') . "\n";
echo "Port: " . Config::get('mail.mailers.smtp.port') . "\n";
echo "Username: " . Config::get('mail.mailers.smtp.username') . "\n";
echo "Password: " . (Config::get('mail.mailers.smtp.password') ? '***masqué***' : 'NON DÉFINI') . "\n";
echo "Encryption: " . Config::get('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . Config::get('mail.from.address') . "\n";
echo "From Name: " . Config::get('mail.from.name') . "\n\n";

// Vérifier si les identifiants sont définis
$username = Config::get('mail.mailers.smtp.username');
$password = Config::get('mail.mailers.smtp.password');

if (empty($username) || empty($password)) {
    echo "❌ ERREUR: Username ou Password SMTP non défini dans .env\n";
    echo "\nVeuillez configurer dans votre fichier .env:\n";
    echo "MAIL_USERNAME=votre_username_mailtrap\n";
    echo "MAIL_PASSWORD=votre_password_mailtrap\n";
    exit(1);
}

if ($username === 'KAZARIA' || $username === 'null') {
    echo "❌ ERREUR: MAIL_USERNAME n'est pas configuré correctement\n";
    echo "Valeur actuelle: {$username}\n";
    echo "\nAllez sur https://mailtrap.io pour obtenir vos vrais identifiants\n";
    exit(1);
}

echo "Configuration semble correcte ✓\n\n";

// Tenter d'envoyer un email de test
echo "Envoi d'un email de test...\n";

try {
    Mail::raw('Ceci est un email de test depuis KAZARIA', function($message) {
        $message->to('test@example.com')
                ->subject('Test Email KAZARIA');
    });
    
    echo "✅ SUCCESS! Email envoyé avec succès!\n";
    echo "\nSi vous utilisez Mailtrap, vérifiez votre inbox sur https://mailtrap.io\n";
    echo "L'email devrait y apparaître.\n";
    
} catch (\Exception $e) {
    echo "❌ ERREUR lors de l'envoi:\n";
    echo $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'authenticate') !== false) {
        echo "Problème d'authentification SMTP.\n";
        echo "Vérifiez que vos identifiants Mailtrap sont corrects dans .env\n";
    } elseif (strpos($e->getMessage(), 'Connection') !== false) {
        echo "Problème de connexion SMTP.\n";
        echo "Vérifiez MAIL_HOST et MAIL_PORT dans .env\n";
    }
}

echo "\n=== Fin du Test ===\n";

