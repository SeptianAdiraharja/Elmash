<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::updateOrCreate(
            ['slug' => 'makanan-olahan-kulit-lemon'],
            [
                'name' => 'Makanan & Olahan Kulit Lemon',
                'description' => 'Produk olahan padat berbahan dasar kulit/daging lemon, seperti Dried Lemon dan Manisan Lemon.',
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'sari-ekstrak-lemon-murni'],
            [
                'name' => 'Sari & Ekstrak Lemon Murni',
                'description' => 'Produk cair hasil ekstraksi sari lemon murni.',
            ]
        );
    }
}