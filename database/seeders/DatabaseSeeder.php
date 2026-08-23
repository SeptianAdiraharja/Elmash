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

        // // 2. Create Categories
        // $catMinuman = Category::create([
        //     'name' => 'Sari & Ekstrak Lemon Murni',
        //     'slug' => 'sari-ekstrak-lemon',
        //     'description' => 'Produk perasan sari lemon murni kualitas grade-A tanpa bahan pengawet.',
        // ]);

        // $catRTD = Category::create([
        //     'name' => 'Minuman Olahan Segar & RTD',
        //     'slug' => 'minuman-olahan-segar',
        //     'description' => 'Minuman siap saji, sirup lemon madu, dan teh herbal lemon.',
        // ]);

        // $catMakanan = Category::create([
        //     'name' => 'Makanan & Olahan Kulit Lemon',
        //     'slug' => 'makanan-olahan-lemon',
        //     'description' => 'Selai lemon premium, permen lemon herbal, dan irisan lemon kering (dried lemon).',
        // ]);

        // $catPerawatan = Category::create([
        //     'name' => 'Perawatan & Kebersihan Herbal',
        //     'slug' => 'perawatan-kebersihan-herbal',
        //     'description' => 'Sabun herbal lemon, essential oil, dan produk pembersih berbasis citrus alami.',
        // ]);

        // // 3. Create Products with realistic requirements and pricing
        // $productsData = [
        //     // Top High Performers (C1 Candidates)
        //     [
        //         'category_id' => $catMinuman->id,
        //         'code' => 'ELM-SR250',
        //         'name' => 'Sari Lemon Murni Elmas 250ml',
        //         'unit' => 'Botol 250ml',
        //         'raw_lemon_requirement' => 1.250,
        //         'cost_price' => 18000,
        //         'selling_price' => 30000,
        //         'stock' => 150,
        //         'min_stock_alert' => 25,
        //         'description' => '100% perasan lemon segar murni tanpa pemanis buatan dalam kemasan botol higienis 250ml.',
        //         'sales_weight' => 95, // high frequency
        //     ],
        //     [
        //         'category_id' => $catMinuman->id,
        //         'code' => 'ELM-SR500',
        //         'name' => 'Sari Lemon Murni Elmas 500ml',
        //         'unit' => 'Botol 500ml',
        //         'raw_lemon_requirement' => 2.500,
        //         'cost_price' => 32000,
        //         'selling_price' => 55000,
        //         'stock' => 110,
        //         'min_stock_alert' => 20,
        //         'description' => 'Perasan lemon murni ukuran keluarga 500ml, kaya vitamin C alami untuk imunitas tubuh.',
        //         'sales_weight' => 88,
        //     ],
        //     [
        //         'category_id' => $catRTD->id,
        //         'code' => 'ELM-RTD330',
        //         'name' => 'Lemonade Ready-to-Drink Segar 330ml',
        //         'unit' => 'Botol 330ml',
        //         'raw_lemon_requirement' => 0.500,
        //         'cost_price' => 6000,
        //         'selling_price' => 12000,
        //         'stock' => 240,
        //         'min_stock_alert' => 40,
        //         'description' => 'Minuman lemonade dingin menyegarkan dengan paduan sedikit madu murni siap minum.',
        //         'sales_weight' => 92,
        //     ],
        //     [
        //         'category_id' => $catMakanan->id,
        //         'code' => 'ELM-DRY100',
        //         'name' => 'Dried Lemon Slices (Lemon Kering 100g)',
        //         'unit' => 'Pouch 100g',
        //         'raw_lemon_requirement' => 2.200,
        //         'cost_price' => 24000,
        //         'selling_price' => 45000,
        //         'stock' => 85,
        //         'min_stock_alert' => 15,
        //         'description' => 'Irisan buah lemon segar yang dikeringkan dengan dehidrator suhu rendah untuk infused water dan seduhan teh.',
        //         'sales_weight' => 70,
        //     ],

        //     // Medium Performers (C2 Candidates)
        //     [
        //         'category_id' => $catRTD->id,
        //         'code' => 'ELM-MADU350',
        //         'name' => 'Sirup Lemon Madu Herbal 350ml',
        //         'unit' => 'Botol 350ml',
        //         'raw_lemon_requirement' => 1.000,
        //         'cost_price' => 22000,
        //         'selling_price' => 38000,
        //         'stock' => 75,
        //         'min_stock_alert' => 15,
        //         'description' => 'Konsentrat sari lemon dipadukan dengan madu randu asli dan rempah sereh alami.',
        //         'sales_weight' => 60,
        //     ],
        //     [
        //         'category_id' => $catRTD->id,
        //         'code' => 'ELM-TEA20',
        //         'name' => 'Teh Celup Lemon Herbal (20 Kantong)',
        //         'unit' => 'Box 20 pcs',
        //         'raw_lemon_requirement' => 0.800,
        //         'cost_price' => 12000,
        //         'selling_price' => 25000,
        //         'stock' => 90,
        //         'min_stock_alert' => 20,
        //         'description' => 'Kombinasi teh hijau pilihan dengan kulit lemon kering wangi yang menyegarkan.',
        //         'sales_weight' => 55,
        //     ],
        //     [
        //         'category_id' => $catPerawatan->id,
        //         'code' => 'ELM-SOAP80',
        //         'name' => 'Sabun Batang Aromaterapi Lemon 80g',
        //         'unit' => 'Bar 80g',
        //         'raw_lemon_requirement' => 0.400,
        //         'cost_price' => 8000,
        //         'selling_price' => 18000,
        //         'stock' => 120,
        //         'min_stock_alert' => 25,
        //         'description' => 'Sabun mandi herbal minyak kelapa dengan ekstrak lemon kaya antioksidan dan mencerahkan kulit.',
        //         'sales_weight' => 50,
        //     ],
        //     [
        //         'category_id' => $catPerawatan->id,
        //         'code' => 'ELM-DISH500',
        //         'name' => 'Sabun Cuci Piring Ekstrak Lemon 500ml',
        //         'unit' => 'Pouch 500ml',
        //         'raw_lemon_requirement' => 0.600,
        //         'cost_price' => 9000,
        //         'selling_price' => 16000,
        //         'stock' => 130,
        //         'min_stock_alert' => 25,
        //         'description' => 'Cairan pembersih lemak ampuh dengan minyak atsiri lemon alami antibakteri.',
        //         'sales_weight' => 52,
        //     ],
        //     [
        //         'category_id' => $catPerawatan->id,
        //         'code' => 'ELM-HS100',
        //         'name' => 'Hand Sanitizer Spray Lemon Oil 100ml',
        //         'unit' => 'Botol Spray 100ml',
        //         'raw_lemon_requirement' => 0.300,
        //         'cost_price' => 7000,
        //         'selling_price' => 15000,
        //         'stock' => 95,
        //         'min_stock_alert' => 20,
        //         'description' => 'Pembersih tangan antiseptik 70% alkohol dengan aroma kesegaran lemon alami.',
        //         'sales_weight' => 45,
        //     ],

        //     // Low Performers / Niche (C3 Candidates)
        //     [
        //         'category_id' => $catMinuman->id,
        //         'code' => 'ELM-SR1000',
        //         'name' => 'Sari Lemon Murni Jerigen 1 Liter',
        //         'unit' => 'Jerigen 1L',
        //         'raw_lemon_requirement' => 5.000,
        //         'cost_price' => 60000,
        //         'selling_price' => 100000,
        //         'stock' => 30,
        //         'min_stock_alert' => 10,
        //         'description' => 'Ukuran jumbo hemat 1 Liter cocok untuk kebutuhan kedai minuman dan konsumsi rutin.',
        //         'sales_weight' => 28,
        //     ],
        //     [
        //         'category_id' => $catMakanan->id,
        //         'code' => 'ELM-CURD220',
        //         'name' => 'Selai Lemon Curd Premium 220g',
        //         'unit' => 'Jar Kaca 220g',
        //         'raw_lemon_requirement' => 1.500,
        //         'cost_price' => 19000,
        //         'selling_price' => 35000,
        //         'stock' => 45,
        //         'min_stock_alert' => 10,
        //         'description' => 'Selai olesan roti lembut manis asam segar terbuat dari jus lemon segar dan mentega nabati.',
        //         'sales_weight' => 22,
        //     ],
        //     [
        //         'category_id' => $catPerawatan->id,
        //         'code' => 'ELM-EO10',
        //         'name' => 'Essential Oil Lemon Sukabumi 10ml',
        //         'unit' => 'Botol Pipet 10ml',
        //         'raw_lemon_requirement' => 3.500,
        //         'cost_price' => 35000,
        //         'selling_price' => 65000,
        //         'stock' => 40,
        //         'min_stock_alert' => 10,
        //         'description' => 'Minyak atsiri murni hasil distilasi uap kulit lemon segar untuk diffuser dan relaksasi.',
        //         'sales_weight' => 18,
        //     ],
        //     [
        //         'category_id' => $catPerawatan->id,
        //         'code' => 'ELM-MASK50',
        //         'name' => 'Masker Organik Bubuk Lemon Peel 50g',
        //         'unit' => 'Sachet 50g',
        //         'raw_lemon_requirement' => 0.900,
        //         'cost_price' => 10000,
        //         'selling_price' => 22000,
        //         'stock' => 35,
        //         'min_stock_alert' => 10,
        //         'description' => 'Masker kecantikan wajah alami bubuk bulir lemon untuk eksfoliasi sel kulit mati.',
        //         'sales_weight' => 15,
        //     ],
        //     [
        //         'category_id' => $catMakanan->id,
        //         'code' => 'ELM-PAST75',
        //         'name' => 'Permen Lemon Pastilles Herbal 75g',
        //         'unit' => 'Pouch 75g',
        //         'raw_lemon_requirement' => 0.500,
        //         'cost_price' => 8500,
        //         'selling_price' => 17500,
        //         'stock' => 60,
        //         'min_stock_alert' => 15,
        //         'description' => 'Permen hisap pelega tenggorokan dengan ekstrak lemon asli dan mint.',
        //         'sales_weight' => 20,
        //     ],
        //     [
        //         'category_id' => $catMinuman->id,
        //         'code' => 'ELM-BULK5L',
        //         'name' => 'Konsentrat Lemon Curah Industri 5 Liter',
        //         'unit' => 'Jerigen 5L',
        //         'raw_lemon_requirement' => 22.000,
        //         'cost_price' => 280000,
        //         'selling_price' => 450000,
        //         'stock' => 12,
        //         'min_stock_alert' => 5,
        //         'description' => 'Konsentrat perasan murni untuk pasokan kafe mitra, horeka, dan pabrik minuman.',
        //         'sales_weight' => 8,
        //     ],
        // ];

        // $createdProducts = [];
        // foreach ($productsData as $pData) {
        //     $weight = $pData['sales_weight'];
        //     unset($pData['sales_weight']);
        //     $product = Product::create($pData);
        //     $product->sales_weight = $weight;
        //     $createdProducts[] = $product;
        // }

        // // 4. Seed Raw Lemon Stock History (Table 1.1 from Skripsi Bab 1)
        // $rawLemonHistory = [
        //     ['period_month' => '2025-01', 'status' => 'Kelebihan', 'quantity_kg' => 3000, 'notes' => 'Panen raya awal tahun, pengadaan melebihi serapan produksi olahan'],
        //     ['period_month' => '2025-02', 'status' => 'Kelebihan', 'quantity_kg' => 3200, 'notes' => 'Pasokan petani melimpah, kapasitas produksi belum maksimal'],
        //     ['period_month' => '2025-03', 'status' => 'Kelebihan', 'quantity_kg' => 3300, 'notes' => 'Stok menumpuk di gudang penyimpanan'],
        //     ['period_month' => '2025-04', 'status' => 'Kelebihan', 'quantity_kg' => 3500, 'notes' => 'Overstock tinggi menjelang musim kemarau'],
        //     ['period_month' => '2025-05', 'status' => 'Kekurangan', 'quantity_kg' => 1000, 'notes' => 'Penurunan panen kebun lokal, terjadi stockout produksi sari lemon'],
        //     ['period_month' => '2025-06', 'status' => 'Kekurangan', 'quantity_kg' => 900, 'notes' => 'Permintaan lemonade tinggi namun bahan baku terbatas'],
        //     ['period_month' => '2025-07', 'status' => 'Kekurangan', 'quantity_kg' => 800, 'notes' => 'Kekurangan stok lemon segar berkualitas'],
        //     ['period_month' => '2025-08', 'status' => 'Seimbang', 'quantity_kg' => 0, 'notes' => 'Pasokan dan permintaan berimbang'],
        //     ['period_month' => '2025-09', 'status' => 'Kekurangan', 'quantity_kg' => 700, 'notes' => 'Defisit stok akibat lonjakan pesanan reseller'],
        //     ['period_month' => '2025-10', 'status' => 'Seimbang', 'quantity_kg' => 0, 'notes' => 'Kondisi stabil'],
        //     ['period_month' => '2025-11', 'status' => 'Kelebihan', 'quantity_kg' => 1000, 'notes' => 'Mulai panen baru dari mitra perkebunan'],
        //     ['period_month' => '2025-12', 'status' => 'Kelebihan', 'quantity_kg' => 1200, 'notes' => 'Kelebihan pasokan akhir tahun'],
        //     ['period_month' => '2026-01', 'status' => 'Kelebihan', 'quantity_kg' => 3800, 'notes' => 'Panen awal tahun melonjak tinggi'],
        //     ['period_month' => '2026-02', 'status' => 'Kelebihan', 'quantity_kg' => 4000, 'notes' => 'Overstock berulang tanpa segmentasi presisi'],
        //     ['period_month' => '2026-03', 'status' => 'Kelebihan', 'quantity_kg' => 4200, 'notes' => 'Akumulasi bahan baku belum teralokasi optimal'],
        //     ['period_month' => '2026-04', 'status' => 'Kelebihan', 'quantity_kg' => 4500, 'notes' => 'Puncak overstock 4.500 Kg lemon segar'],
        // ];

        // foreach ($rawLemonHistory as $raw) {
        //     RawLemonStock::create($raw);
        // }

        // // 5. Seed Realistic Sales Transactions from Jan 2025 to May 2026
        // $customerNames = [
        //     'Toko Berkah Herbal Sukabumi',
        //     'Kedai Kopi & Teh Selabintana',
        //     'Ibu Hj. Siti Nurjanah (Reseller Bogor)',
        //     'Bapak Hendra Gunawan (Bandung)',
        //     'Apotek & Toko Sehat Al-Falah',
        //     'Kafe Citrus Corner Cikole',
        //     'Supermarket Lokal Selamat Sukabumi',
        //     'Ibu Maya Kartika (WhatsApp Order)',
        //     'Bapak Rian Hidayat (Cianjur Reseller)',
        //     'Pelanggan Langsung Outlet Sukalarang',
        //     'Kantin Sehat RSUD Sekarwangi',
        //     'Toko Organik Bumi Sehat',
        //     'Marketplace Shopee - Elmas Fresh Official',
        //     'Marketplace Tokopedia - Elmas Sukabumi',
        //     'Distributor Herbal Jabodetabek',
        //     'Kedai Minuman Segar Alun-Alun',
        //     'Ibu Ratna Dewi (Komunitas Senam)',
        //     'Bapak Dedi Mulyadi (PO Reseller)',
        // ];

        // $channels = ['Toko Offline', 'WhatsApp Order', 'Reseller / Distributor', 'Konsinyasi Kafe', 'Shopee / Marketplace'];
        // $paymentMethods = ['Cash / Tunai', 'Transfer BCA', 'Transfer BRI', 'QRIS'];

        // $startDate = Carbon::create(2025, 1, 1);
        // $endDate = Carbon::create(2026, 5, 20);

        // $invoiceCounter = 1;
        // $currDate = clone $startDate;

        // while ($currDate <= $endDate) {
        //     // Generate 1 to 4 transactions per day
        //     $txCount = rand(1, 4);

        //     for ($t = 0; $t < $txCount; $t++) {
        //         $invoiceNum = 'INV-' . $currDate->format('Ymd') . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT);
        //         $customer = $customerNames[array_rand($customerNames)];
        //         $channel = $channels[array_rand($channels)];
        //         $payMethod = $paymentMethods[array_rand($paymentMethods)];

        //         // Pick 1 to 4 distinct products
        //         $numItems = rand(1, 4);
        //         $selectedProductKeys = (array) array_rand($createdProducts, $numItems);

        //         $txSubtotal = 0;
        //         $txItemsData = [];

        //         foreach ($selectedProductKeys as $pKey) {
        //             $prod = $createdProducts[$pKey];
        //             // Quantity influenced by product weight
        //             $weight = $prod->sales_weight;
        //             if ($weight > 80) {
        //                 $qty = rand(4, 25);
        //             } elseif ($weight > 40) {
        //                 $qty = rand(2, 12);
        //             } else {
        //                 $qty = rand(1, 4);
        //             }

        //             $itemSubtotal = $qty * $prod->selling_price;
        //             $rawLemonUsed = $qty * $prod->raw_lemon_requirement;

        //             $txSubtotal += $itemSubtotal;

        //             $txItemsData[] = [
        //                 'product_id' => $prod->id,
        //                 'product_name' => $prod->name,
        //                 'product_code' => $prod->code,
        //                 'quantity' => $qty,
        //                 'unit_price' => $prod->selling_price,
        //                 'cost_price' => $prod->cost_price,
        //                 'subtotal' => $itemSubtotal,
        //                 'raw_lemon_used' => $rawLemonUsed,
        //             ];
        //         }

        //         $discount = (rand(1, 10) == 1) ? round($txSubtotal * 0.05, -2) : 0;
        //         $totalAmount = $txSubtotal - $discount;

        //         $transaction = SalesTransaction::create([
        //             'invoice_number' => $invoiceNum,
        //             'transaction_date' => $currDate->format('Y-m-d'),
        //             'customer_name' => $customer,
        //             'customer_phone' => '08' . rand(1111111111, 9999999999),
        //             'sales_channel' => $channel,
        //             'payment_method' => $payMethod,
        //             'payment_status' => 'Lunas',
        //             'subtotal' => $txSubtotal,
        //             'discount' => $discount,
        //             'tax' => 0,
        //             'total_amount' => $totalAmount,
        //             'notes' => 'Pesanan rutin penjualan produk olahan lemon.',
        //             'created_by' => $admin->id,
        //         ]);

        //         foreach ($txItemsData as $itData) {
        //             $itData['sales_transaction_id'] = $transaction->id;
        //             SalesTransactionItem::create($itData);
        //         }
        //     }

        //     // advance date by 1 to 2 days
        //     $currDate->addDays(rand(1, 2));
        // }

        // // 6. Run Initial Clustering Analyses so the system has instant historical data ready
        // $kMeansService = new KMeansService();

        // // Run Analysis 1: Periode 2025 Penuh
        // $dataset2025 = $kMeansService->extractFeatures('2025-01-01', '2025-12-31');
        // $clusterOutput2025 = $kMeansService->runClustering($dataset2025, 3, 100);

        // $analysis2025 = ClusteringAnalysis::create([
        //     'title' => 'Analisis Segmentasi Penjualan Tahunan 2025',
        //     'period_start' => '2025-01-01',
        //     'period_end' => '2025-12-31',
        //     'k_value' => 3,
        //     'max_iterations' => 100,
        //     'iterations_count' => $clusterOutput2025['iterations_count'],
        //     'is_converged' => $clusterOutput2025['converged'],
        //     'sse_inertia' => $clusterOutput2025['sse_inertia'],
        //     'davies_bouldin_index' => $clusterOutput2025['davies_bouldin_index'],
        //     'features' => $clusterOutput2025['features'],
        //     'initial_centroids' => $clusterOutput2025['initial_centroids'],
        //     'final_centroids' => $clusterOutput2025['final_centroids'],
        //     'cluster_summary' => $clusterOutput2025['cluster_summary'],
        //     'raw_data_snapshot' => $clusterOutput2025['raw_data'],
        //     'iteration_history' => $clusterOutput2025['iteration_history'],
        //     'notes' => 'Segmentasi data penjualan produk olahan lemon UMKM Elmas Fresh periode tahun penuh 2025.',
        //     'created_by' => $admin->id,
        // ]);

        // foreach ($clusterOutput2025['results'] as $res) {
        //     ClusteringResult::create([
        //         'clustering_analysis_id' => $analysis2025->id,
        //         'product_id' => $res['product_id'],
        //         'product_name' => $res['product_name'],
        //         'product_code' => $res['product_code'],
        //         'category_name' => $res['category_name'],
        //         'total_qty' => $res['total_qty'],
        //         'frequency' => $res['frequency'],
        //         'total_revenue' => $res['total_revenue'],
        //         'raw_lemon_kg' => $res['raw_lemon_kg'],
        //         'normalized_vector' => $res['normalized_vector'],
        //         'cluster_index' => $res['cluster_index'],
        //         'cluster_code' => $res['cluster_code'],
        //         'cluster_label' => $res['cluster_label'],
        //         'distance_to_centroid' => $res['distance_to_centroid'],
        //         'inventory_strategy' => $res['inventory_strategy'],
        //     ]);
        // }

        // // Run Analysis 2: Periode Q1-Q2 2026 (Recent Period)
        // $dataset2026 = $kMeansService->extractFeatures('2026-01-01', '2026-04-30');
        // $clusterOutput2026 = $kMeansService->runClustering($dataset2026, 3, 100);

        // $analysis2026 = ClusteringAnalysis::create([
        //     'title' => 'Analisis Segmentasi Penjualan Caturwulan I 2026 (Jan - Apr 2026)',
        //     'period_start' => '2026-01-01',
        //     'period_end' => '2026-04-30',
        //     'k_value' => 3,
        //     'max_iterations' => 100,
        //     'iterations_count' => $clusterOutput2026['iterations_count'],
        //     'is_converged' => $clusterOutput2026['converged'],
        //     'sse_inertia' => $clusterOutput2026['sse_inertia'],
        //     'davies_bouldin_index' => $clusterOutput2026['davies_bouldin_index'],
        //     'features' => $clusterOutput2026['features'],
        //     'initial_centroids' => $clusterOutput2026['initial_centroids'],
        //     'final_centroids' => $clusterOutput2026['final_centroids'],
        //     'cluster_summary' => $clusterOutput2026['cluster_summary'],
        //     'raw_data_snapshot' => $clusterOutput2026['raw_data'],
        //     'iteration_history' => $clusterOutput2026['iteration_history'],
        //     'notes' => 'Evaluasi data penjualan terkini untuk penyelarasan stok bahan baku lemon segar 4.500 Kg.',
        //     'created_by' => $admin->id,
        // ]);

        // foreach ($clusterOutput2026['results'] as $res) {
        //     ClusteringResult::create([
        //         'clustering_analysis_id' => $analysis2026->id,
        //         'product_id' => $res['product_id'],
        //         'product_name' => $res['product_name'],
        //         'product_code' => $res['product_code'],
        //         'category_name' => $res['category_name'],
        //         'total_qty' => $res['total_qty'],
        //         'frequency' => $res['frequency'],
        //         'total_revenue' => $res['total_revenue'],
        //         'raw_lemon_kg' => $res['raw_lemon_kg'],
        //         'normalized_vector' => $res['normalized_vector'],
        //         'cluster_index' => $res['cluster_index'],
        //         'cluster_code' => $res['cluster_code'],
        //         'cluster_label' => $res['cluster_label'],
        //         'distance_to_centroid' => $res['distance_to_centroid'],
        //         'inventory_strategy' => $res['inventory_strategy'],
        //     ]);
        // }
    }
}
