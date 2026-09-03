<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClusteringAnalysis;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\User;
use App\Services\KMeansService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ElmasFreshSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@elmasfresh.id')->first();
    }

    public function test_login_screen_renders_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('UMKM ELMAS FRESH');
        $response->assertSee('admin@elmasfresh.id');
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'admin@elmasfresh.id',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sistem Segmentasi Penjualan Produk Olahan Lemon');
        $response->assertSee('Total Omset Penjualan');
        $response->assertSee('Lemon Segar Terserap');
    }

    public function test_admin_can_view_and_update_profile()
    {
        $response = $this->actingAs($this->admin)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee($this->admin->email);

        $updateResponse = $this->actingAs($this->admin)->put('/profile', [
            'name' => 'Salsabila Rifa\'i, S.Kom.',
            'email' => 'salsabila@elmasfresh.id',
            'phone' => '0812-3344-5566',
        ]);

        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'email' => 'salsabila@elmasfresh.id',
        ]);
    }

    public function test_admin_can_create_new_lemon_product()
    {
        $category = Category::first();

        $response = $this->actingAs($this->admin)->post('/products', [
            'category_id' => $category->id,
            'code' => 'ELM-NEW-TEST',
            'name' => 'Sirup Lemon Serai Premium 500ml',
            'unit' => 'Botol 500ml',
            'raw_lemon_requirement' => 1.8,
            'cost_price' => 25000,
            'selling_price' => 45000,
            'stock' => 50,
            'min_stock_alert' => 10,
            'description' => 'Produk inovasi baru sirup lemon dengan serai wangi.',
            'is_active' => true,
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'code' => 'ELM-NEW-TEST',
            'name' => 'Sirup Lemon Serai Premium 500ml',
        ]);
    }

    public function test_admin_can_create_sales_transaction()
    {
        $product = Product::first();

        $response = $this->actingAs($this->admin)->post('/transactions', [
            'invoice_number' => 'INV-TEST-001',
            'transaction_date' => '2026-05-15',
            'customer_name' => 'Kafe Lemonade Cikole',
            'customer_phone' => '0899-1234-5678',
            'sales_channel' => 'Konsinyasi Kafe',
            'payment_method' => 'QRIS',
            'payment_status' => 'Lunas',
            'discount' => 5000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => $product->selling_price,
                ]
            ]
        ]);

        $this->assertDatabaseHas('sales_transactions', [
            'invoice_number' => 'INV-TEST-001',
            'customer_name' => 'Kafe Lemonade Cikole',
        ]);
    }

    public function test_kmeans_service_execution_and_accuracy()
    {
        $kMeansService = new KMeansService();
        $dataset = $kMeansService->extractFeatures('2025-01-01', '2025-10-30');

        $this->assertNotEmpty($dataset);
        $output = $kMeansService->runClustering($dataset, 3, 100);

        $this->assertEquals(3, $output['k']);
        $this->assertTrue($output['converged']);
        $this->assertArrayHasKey('C1', $output['cluster_summary']);
        $this->assertArrayHasKey('C2', $output['cluster_summary']);
        $this->assertArrayHasKey('C3', $output['cluster_summary']);

        // C3 = Penjualan Tinggi, C1 = Penjualan Rendah
        $this->assertGreaterThanOrEqual(
            $output['cluster_summary']['C1']['centroid_score'],
            $output['cluster_summary']['C3']['centroid_score']
        );

        // Elbow data computed
        $this->assertNotNull($output['elbow_data']);
        $this->assertEquals(3, $output['elbow_data']['optimal_k']);

        // Test arbitrary sample size feature (misal 10 sampel data)
        $dataset10 = $kMeansService->extractFeatures('2025-01-01', '2025-10-30', 10);
        $this->assertCount(10, $dataset10);
        $output10 = $kMeansService->runClustering($dataset10, 3, 100);
        $this->assertCount(10, $output10['results']);
        $this->assertTrue($output10['converged']);
    }

    public function test_clustering_export_pdf_and_excel()
    {
        $kMeansService = new KMeansService();
        $dataset = $kMeansService->extractFeatures('2025-01-01', '2025-04-30');
        $output = $kMeansService->runClustering($dataset, 3, 100);

        $analysis = ClusteringAnalysis::create([
            'title' => 'Analisis Uji Coba Export',
            'period_start' => '2025-01-01',
            'period_end' => '2025-04-30',
            'k_value' => 3,
            'max_iterations' => 100,
            'iterations_count' => $output['iterations_count'],
            'is_converged' => $output['converged'],
            'sse_inertia' => $output['sse_inertia'],
            'davies_bouldin_index' => $output['davies_bouldin_index'],
            'features' => $output['features'],
            'initial_centroids' => $output['initial_centroids'],
            'final_centroids' => $output['final_centroids'],
            'cluster_summary' => $output['cluster_summary'],
            'raw_data_snapshot' => $output['raw_data'],
            'iteration_history' => $output['iteration_history'],
            'created_by' => $this->admin->id,
        ]);

        $pdfResponse = $this->actingAs($this->admin)->get("/clustering/{$analysis->id}/export/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));

        $excelResponse = $this->actingAs($this->admin)->get("/clustering/{$analysis->id}/export/excel");
        $excelResponse->assertStatus(200);
    }

    public function test_multi_period_comparison_view()
    {
        $kMeansService = new KMeansService();
        $datasetA = $kMeansService->extractFeatures('2025-01-01', '2025-03-31');
        $outputA = $kMeansService->runClustering($datasetA, 3, 100);
        $analysisA = ClusteringAnalysis::create([
            'title' => 'Sesi Periode A',
            'period_start' => '2025-01-01',
            'period_end' => '2025-03-31',
            'k_value' => 3,
            'max_iterations' => 100,
            'iterations_count' => $outputA['iterations_count'],
            'is_converged' => true,
            'cluster_summary' => $outputA['cluster_summary'],
            'created_by' => $this->admin->id,
        ]);

        $datasetB = $kMeansService->extractFeatures('2025-04-01', '2025-06-30');
        $outputB = $kMeansService->runClustering($datasetB, 3, 100);
        $analysisB = ClusteringAnalysis::create([
            'title' => 'Sesi Periode B',
            'period_start' => '2025-04-01',
            'period_end' => '2025-06-30',
            'k_value' => 3,
            'max_iterations' => 100,
            'iterations_count' => $outputB['iterations_count'],
            'is_converged' => true,
            'cluster_summary' => $outputB['cluster_summary'],
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/clustering/compare?analysis_a=' . $analysisA->id . '&analysis_b=' . $analysisB->id);
        $response->assertStatus(200);
        $response->assertSee('Tabel Perbandingan Klaster', false);
    }
}
