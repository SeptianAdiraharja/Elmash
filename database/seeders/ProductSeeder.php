<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $olahanKulit = Category::where('slug', 'makanan-olahan-kulit-lemon')->firstOrFail();
        $sariEkstrak = Category::where('slug', 'sari-ekstrak-lemon-murni')->firstOrFail();

        // unit menentukan X1/X2/X3 di KMeansService::extractFeatures()
        Product::updateOrCreate(
            ['code' => 'DRIED-LMN-001'],
            [
                'category_id' => $olahanKulit->id,
                'name' => 'Dried Lemon',
                'slug' => Str::slug('Dried Lemon') . '-001',
                'unit' => 'Kg',
                'raw_lemon_requirement' => 4.5, // kg lemon segar per kg dried lemon (contoh)
                'cost_price' => 60000,
                'selling_price' => 95000,
                'stock' => 50,
                'min_stock_alert' => 10,
                'description' => 'Irisan lemon kering (X1: kg terkirim).',
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['code' => 'MANISAN-LMN-001'],
            [
                'category_id' => $olahanKulit->id,
                'name' => 'Manisan Lemon',
                'slug' => Str::slug('Manisan Lemon') . '-001',
                'unit' => 'Pouch',
                'raw_lemon_requirement' => 0.15, // kg lemon segar per pouch (contoh)
                'cost_price' => 8000,
                'selling_price' => 13000,
                'stock' => 500,
                'min_stock_alert' => 100,
                'description' => 'Manisan lemon kemasan pouch (X2: pouch terkirim).',
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['code' => 'SARI-LMN-001'],
            [
                'category_id' => $sariEkstrak->id,
                'name' => 'Sari Lemon',
                'slug' => Str::slug('Sari Lemon') . '-001',
                'unit' => 'Liter',
                'raw_lemon_requirement' => 1.2, // kg lemon segar per liter sari (contoh)
                'cost_price' => 18000,
                'selling_price' => 28000,
                'stock' => 300,
                'min_stock_alert' => 50,
                'description' => 'Sari/ekstrak lemon murni cair (X3: liter terkirim).',
                'is_active' => true,
            ]
        );
    }
}