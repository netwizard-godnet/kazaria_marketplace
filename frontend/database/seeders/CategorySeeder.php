<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Téléphones et tablettes',
                'slug' => 'telephones-et-tablettes',
                'icon' => 'fa-solid fa-mobile-screen-button',
                'image' => 'images/categories/telephones-et-tablettes.jpg',
                'order' => 1,
                'subcategories' => [
                    ['name' => 'Smartphones', 'icon' => 'fa-solid fa-mobile'],
                    ['name' => 'Téléphones basiques', 'icon' => 'fa-solid fa-phone'],
                    ['name' => 'Tablettes', 'icon' => 'fa-solid fa-tablet-screen-button'],
                    ['name' => 'Accessoires téléphones', 'icon' => 'fa-solid fa-mobile-screen'],
                    ['name' => 'Chargeurs et câbles', 'icon' => 'fa-solid fa-plug'],
                    ['name' => 'Écouteurs et casques', 'icon' => 'fa-solid fa-headphones'],
                    ['name' => 'Montres connectées', 'icon' => 'fa-solid fa-watch'],
                    ['name' => 'Protection écran', 'icon' => 'fa-solid fa-shield-halved']
                ]
            ],
            [
                'name' => 'TV et Electronique',
                'slug' => 'tv-et-electronique',
                'icon' => 'fa-solid fa-tv',
                'image' => 'images/categories/tv-et-electronique.jpg',
                'order' => 2,
                'subcategories' => [
                    ['name' => 'Téléviseurs', 'icon' => 'fa-solid fa-tv'],
                    ['name' => 'Home Cinéma', 'icon' => 'fa-solid fa-film'],
                    ['name' => 'Barres de son', 'icon' => 'fa-solid fa-volume-high'],
                    ['name' => 'Décodeurs', 'icon' => 'fa-solid fa-box'],
                    ['name' => 'Projecteurs', 'icon' => 'fa-solid fa-video'],
                    ['name' => 'Antennes', 'icon' => 'fa-solid fa-tower-broadcast'],
                    ['name' => 'Câbles HDMI', 'icon' => 'fa-solid fa-ethernet'],
                    ['name' => 'Consoles de jeux', 'icon' => 'fa-solid fa-gamepad']
                ]
            ],
            [
                'name' => 'Electroménager',
                'slug' => 'electromenager',
                'icon' => 'fa-solid fa-kitchen-set',
                'image' => 'images/categories/electromenager.jpg',
                'order' => 3,
                'subcategories' => [
                    ['name' => 'Réfrigérateurs', 'icon' => 'fa-solid fa-temperature-low'],
                    ['name' => 'Congélateurs', 'icon' => 'fa-solid fa-temperature-arrow-down'],
                    ['name' => 'Cuisinières', 'icon' => 'fa-solid fa-fire-burner'],
                    ['name' => 'Micro-ondes', 'icon' => 'fa-solid fa-microwave'],
                    ['name' => 'Lave-linge', 'icon' => 'fa-solid fa-jug-detergent'],
                    ['name' => 'Climatiseurs', 'icon' => 'fa-solid fa-wind'],
                    ['name' => 'Ventilateurs', 'icon' => 'fa-solid fa-fan'],
                    ['name' => 'Aspirateurs', 'icon' => 'fa-solid fa-broom']
                ]
            ],
            [
                'name' => 'Ordinateurs et accessoires',
                'slug' => 'ordinateurs-et-accessoires',
                'icon' => 'fa-solid fa-laptop',
                'image' => 'images/categories/ordinateurs-et-accessoires.jpg',
                'order' => 4,
                'subcategories' => [
                    ['name' => 'Ordinateurs portables', 'icon' => 'fa-solid fa-laptop'],
                    ['name' => 'Ordinateurs de bureau', 'icon' => 'fa-solid fa-desktop'],
                    ['name' => 'Écrans', 'icon' => 'fa-solid fa-display'],
                    ['name' => 'Souris et claviers', 'icon' => 'fa-solid fa-keyboard'],
                    ['name' => 'Imprimantes', 'icon' => 'fa-solid fa-print'],
                    ['name' => 'Scanners', 'icon' => 'fa-solid fa-scanner'],
                    ['name' => 'Webcams', 'icon' => 'fa-solid fa-camera-web'],
                    ['name' => 'Stockage externe', 'icon' => 'fa-solid fa-hard-drive']
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'];
            unset($categoryData['subcategories']);
            
            $category = Category::create($categoryData);
            
            // Créer les sous-catégories
            foreach ($subcategories as $index => $subcategoryData) {
                $subcategoryName = is_array($subcategoryData) ? $subcategoryData['name'] : $subcategoryData;
                $subcategoryIcon = is_array($subcategoryData) ? $subcategoryData['icon'] : 'fa-solid fa-tag';
                $subcategorySlug = \Illuminate\Support\Str::slug($subcategoryName);
                
                Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                    'icon' => $subcategoryIcon,
                    'image' => 'images/categories/default.jpg',
                    'order' => $index + 1
                ]);
            }
        }
    }
}
