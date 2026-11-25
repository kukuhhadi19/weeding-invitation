<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed default Indonesian wedding invitation template categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Klasik',
                'slug' => 'klasik',
                'description' => 'Desain undangan klasik elegan dengan tipografi serif formal dan ornamen abadi.',
                'icon' => 'crown',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Modern',
                'slug' => 'modern',
                'description' => 'Tata letak kontemporer yang bersih, ringkas, dan menonjolkan foto pasangan.',
                'icon' => 'sparkles',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Floral',
                'slug' => 'floral',
                'description' => 'Hiasan ilustrasi bunga lembut dan warna pastel romantis untuk akad dan resepsi.',
                'icon' => 'flower',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Minimalis',
                'slug' => 'minimalis',
                'description' => 'Konsep monokrom dan whitespace lega agar informasi acara mudah dibaca tamu.',
                'icon' => 'layout',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Adat Tradisional',
                'slug' => 'adat-tradisional',
                'description' => 'Sentuhan budaya Nusantara seperti Jawa, Sunda, Minang, dan Bali dengan aksen emas.',
                'icon' => 'landmark',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
