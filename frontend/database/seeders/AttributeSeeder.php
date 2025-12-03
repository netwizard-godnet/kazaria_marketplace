<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Couleur',
                'type' => 'checkbox',
                'order' => 1,
                'values' => ['Noir', 'Blanc', 'Bleu', 'Rouge', 'Vert', 'Gris', 'Rose', 'Or']
            ],
            [
                'name' => 'Marque',
                'type' => 'checkbox',
                'order' => 2,
                'values' => ['Samsung', 'Apple', 'LG', 'Sony', 'HP', 'Dell', 'Xiaomi', 'Huawei', 'Lenovo', 'Asus', 'Bosch', 'Whirlpool', 'Philips']
            ],
            [
                'name' => 'RAM',
                'type' => 'checkbox',
                'order' => 3,
                'values' => ['4GB', '6GB', '8GB', '12GB', '16GB', '32GB', '64GB']
            ],
            [
                'name' => 'Stockage',
                'type' => 'checkbox',
                'order' => 4,
                'values' => ['64GB', '128GB', '256GB', '512GB', '1TB', '2TB']
            ],
            [
                'name' => 'Taille écran',
                'type' => 'checkbox',
                'order' => 5,
                'values' => ['5"', '6"', '6.5"', '7"', '10"', '13"', '14"', '15"', '17"', '32"', '40"', '43"', '50"', '55"', '65"']
            ],
            [
                'name' => 'État',
                'type' => 'radio',
                'order' => 6,
                'values' => ['Neuf', 'Reconditionné', 'Occasion']
            ],
            [
                'name' => 'Connectivité',
                'type' => 'checkbox',
                'order' => 7,
                'values' => ['WiFi', 'Bluetooth', '4G', '5G', 'NFC', 'USB-C', 'HDMI']
            ],
            [
                'name' => 'Système d\'exploitation',
                'type' => 'checkbox',
                'order' => 8,
                'values' => ['Android', 'iOS', 'Windows', 'MacOS', 'Linux', 'Smart TV']
            ]
        ];

        foreach ($attributes as $attributeData) {
            $values = $attributeData['values'];
            unset($attributeData['values']);
            
            $attribute = Attribute::create($attributeData);
            
            // Créer les valeurs d'attributs
            foreach ($values as $index => $value) {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                    'order' => $index + 1
                ]);
            }
        }

        // Assigner aléatoirement des attributs aux produits
        $this->assignAttributesToProducts();
    }

    private function assignAttributesToProducts()
    {
        $products = Product::all();
        
        foreach ($products as $product) {
            // Couleur (1-2 couleurs par produit)
            $colorAttribute = Attribute::where('slug', 'couleur')->first();
            if ($colorAttribute) {
                $colors = $colorAttribute->attributeValues->random(rand(1, 2));
                foreach ($colors as $color) {
                    $product->attributeValues()->attach($color->id);
                }
            }
            
            // Marque (basée sur la marque du produit)
            if ($product->brand) {
                $marqueAttribute = Attribute::where('slug', 'marque')->first();
                if ($marqueAttribute) {
                    $brandValue = $marqueAttribute->attributeValues()
                        ->where('value', $product->brand)
                        ->first();
                    if ($brandValue) {
                        $product->attributeValues()->attach($brandValue->id);
                    }
                }
            }
            
            // RAM (pour téléphones et ordinateurs)
            if (in_array($product->category->slug, ['telephones-et-tablettes', 'ordinateurs-et-accessoires'])) {
                $ramAttribute = Attribute::where('slug', 'ram')->first();
                if ($ramAttribute) {
                    $ram = $ramAttribute->attributeValues->random(1)->first();
                    $product->attributeValues()->attach($ram->id);
                }
            }
            
            // Stockage (pour téléphones, tablettes et ordinateurs)
            if (in_array($product->category->slug, ['telephones-et-tablettes', 'ordinateurs-et-accessoires'])) {
                $stockageAttribute = Attribute::where('slug', 'stockage')->first();
                if ($stockageAttribute) {
                    $stockage = $stockageAttribute->attributeValues->random(1)->first();
                    $product->attributeValues()->attach($stockage->id);
                }
            }
            
            // Taille écran
            $tailleAttribute = Attribute::where('slug', 'taille-ecran')->first();
            if ($tailleAttribute) {
                $categorySlug = $product->category->slug;
                
                if ($categorySlug === 'telephones-et-tablettes') {
                    // Téléphones: 5-7", Tablettes: 7-10"
                    $sizes = $tailleAttribute->attributeValues
                        ->whereIn('value', ['5"', '6"', '6.5"', '7"', '10"']);
                } elseif ($categorySlug === 'tv-et-electronique') {
                    // TV: 32-65"
                    $sizes = $tailleAttribute->attributeValues
                        ->whereIn('value', ['32"', '40"', '43"', '50"', '55"', '65"']);
                } elseif ($categorySlug === 'ordinateurs-et-accessoires') {
                    // Ordinateurs: 13-17"
                    $sizes = $tailleAttribute->attributeValues
                        ->whereIn('value', ['13"', '14"', '15"', '17"']);
                } else {
                    $sizes = collect();
                }
                
                if ($sizes->count() > 0) {
                    $size = $sizes->random(1)->first();
                    $product->attributeValues()->attach($size->id);
                }
            }
            
            // État (tous les produits)
            $etatAttribute = Attribute::where('slug', 'etat')->first();
            if ($etatAttribute) {
                $etat = $product->is_new 
                    ? $etatAttribute->attributeValues->where('value', 'Neuf')->first()
                    : $etatAttribute->attributeValues->random(1)->first();
                if ($etat) {
                    $product->attributeValues()->attach($etat->id);
                }
            }
            
            // Connectivité (pour téléphones et TV)
            if (in_array($product->category->slug, ['telephones-et-tablettes', 'tv-et-electronique'])) {
                $connectiviteAttribute = Attribute::where('slug', 'connectivite')->first();
                if ($connectiviteAttribute) {
                    $connections = $connectiviteAttribute->attributeValues->random(rand(2, 4));
                    foreach ($connections as $connection) {
                        $product->attributeValues()->attach($connection->id);
                    }
                }
            }
            
            // Système d'exploitation
            $osAttribute = Attribute::where('slug', 'systeme-dexploitation')->first();
            if ($osAttribute) {
                $categorySlug = $product->category->slug;
                
                if ($categorySlug === 'telephones-et-tablettes') {
                    if (str_contains($product->brand, 'Apple')) {
                        $os = $osAttribute->attributeValues->where('value', 'iOS')->first();
                    } else {
                        $os = $osAttribute->attributeValues->where('value', 'Android')->first();
                    }
                } elseif ($categorySlug === 'ordinateurs-et-accessoires') {
                    if (str_contains($product->brand, 'Apple')) {
                        $os = $osAttribute->attributeValues->where('value', 'MacOS')->first();
                    } else {
                        $os = $osAttribute->attributeValues->where('value', 'Windows')->first();
                    }
                } elseif ($categorySlug === 'tv-et-electronique') {
                    $os = $osAttribute->attributeValues->where('value', 'Smart TV')->first();
                } else {
                    $os = null;
                }
                
                if ($os) {
                    $product->attributeValues()->attach($os->id);
                }
            }
        }
    }
}
