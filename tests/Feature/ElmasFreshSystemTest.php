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
        $dataset = $kMeansService->extractFeatures('2025-01-01', '2026-04-30');

        $this->assertNotEmpty($dataset);
        $output = $kMeansService->runClustering($dataset, 3, 100);

        $this->assertEquals(3, $output['k']);
        $this->assertTrue($output['converged']);
        $this->assertArrayHasKey('C1', $output['cluster_summary']);
        $this->assertArrayHasKey('C2', $output['cluster_summary']);
        $this->assertArrayHasKey('C3', $output['cluster_summary']);

        // Check C1 is high performance
        $this->assertGreaterThanOrEqual(
            $output['cluster_summary']['C2']['avg_qty'],
            $output['cluster_summary']['C1']['avg_qty']
        );
    }

    public function test_clustering_export_pdf_and_excel()
    {
        $analysis = ClusteringAnalysis::first();

        $pdfResponse = $this->actingAs($this->admin)->get("/clustering/{$analysis->id}/export/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));

        $excelResponse = $this->actingAs($this->admin)->get("/clustering/{$analysis->id}/export/excel");
        $excelResponse->assertStatus(200);
    }

    public function test_multi_period_comparison_view()
    {
        $analyses = ClusteringAnalysis::all();
        $this->assertGreaterThanOrEqual(2, $analyses->count());

        $response = $this->actingAs($this->admin)->get('/clustering/compare?analysis_a=' . $analyses[0]->id . '&analysis_b=' . $analyses[1]->id);
        $response->assertStatus(200);
        $response->assertSee('Tabel Perbandingan Klaster', false);
    }
}
