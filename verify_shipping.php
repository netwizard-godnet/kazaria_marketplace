<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

echo "=== VÉRIFICATION DES PARAMÈTRES DE LIVRAISON ===\n\n";

// 1. Vérifier que les paramètres existent dans la base de données
echo "1. Paramètres dans la base de données:\n";
$dbSettings = Setting::whereIn('key', ['shipping_cost', 'free_shipping_threshold', 'min_order_quantity', 'currency_symbol'])
    ->get(['key', 'value', 'type']);

foreach ($dbSettings as $setting) {
    echo "   {$setting->key}: {$setting->value} (type: {$setting->type})\n";
}

echo "\n2. Test de récupération via Setting::get():\n";
$settings = [
    'min_order_quantity' => (int) Setting::get('min_order_quantity', 1),
    'currency_symbol' => Setting::get('currency_symbol', 'FCFA'),
    'shipping_cost' => (float) Setting::get('shipping_cost', 5000),
    'free_shipping_threshold' => (float) Setting::get('free_shipping_threshold', 50000)
];

foreach ($settings as $key => $value) {
    echo "   {$key}: {$value} (" . gettype($value) . ")\n";
}

echo "\n3. Test de calcul de livraison:\n";
$testAmounts = [25000, 50000, 75000];

foreach ($testAmounts as $amount) {
    $shipping = $amount >= $settings['free_shipping_threshold'] ? 0 : $settings['shipping_cost'];
    $total = $amount + $shipping;
    $shippingText = $shipping == 0 ? "Gratuite" : number_format($shipping) . " " . $settings['currency_symbol'];
    
    echo "   Montant: " . number_format($amount) . " " . $settings['currency_symbol'] . 
         " → Livraison: {$shippingText} → Total: " . number_format($total) . " " . $settings['currency_symbol'] . "\n";
}

echo "\n4. Vérification du contrôleur CartController:\n";
try {
    $controller = new \App\Http\Controllers\CartController();
    $request = new \Illuminate\Http\Request();
    $response = $controller->index($request);
    
    if (method_exists($response, 'getData')) {
        $data = $response->getData();
        if (isset($data->settings)) {
            echo "   Paramètres passés à la vue: OK\n";
            foreach ($data->settings as $key => $value) {
                echo "     {$key}: {$value}\n";
            }
        } else {
            echo "   Paramètres passés à la vue: NON TROUVÉS\n";
        }
    } else {
        echo "   Réponse du contrôleur: " . get_class($response) . "\n";
    }
} catch (Exception $e) {
    echo "   Erreur dans le contrôleur: " . $e->getMessage() . "\n";
}

echo "\n=== VÉRIFICATION TERMINÉE ===\n";
