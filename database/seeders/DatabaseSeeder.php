<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClusteringAnalysis;
use App\Models\ClusteringResult;
use App\Models\Product;
use App\Models\RawLemonStock;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use App\Models\User;
use App\Services\KMeansService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@elmasfresh.id'],
            [
                'name' => 'Salsabila Rifa\'i',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '0812-8899-7711',
            ]
        );

        // Additional Operator user
        User::firstOrCreate(
            ['email' => 'alif@elmasfresh.id'],
            [
                'name' => 'Alif Syada Mukti',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '0857-2233-4455',
            ]
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            // SalesTransactionSeeder::class,
        ]);
    }
}
