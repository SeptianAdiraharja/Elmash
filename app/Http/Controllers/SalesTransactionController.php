<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SalesTransactionController extends Controller
{
    protected ExportService $exportService;

    /**
     * Konfigurasi produk yang akan diimport
     */
    private const PRODUCT_CONFIG = [
        'dried_lemon' => [
            'code' => 'ELM-DRL-KG',
            'name' => 'Dried Lemon (Curah KG)',
            'unit' => 'Kg',
            'unit_label' => 'kg',
            'category_slug' => 'makanan-olahan-lemon',
            'category_name' => 'Makanan & Olahan Kulit Lemon',
            'qty_col' => 'B',
            'total_col' => 'E',
        ],
        'manisan_lemon' => [
            'code' => 'ELM-MSN',
            'name' => 'Manisan Lemon',
            'unit' => 'Pouch',
            'unit_label' => 'pouch',
            'category_slug' => 'makanan-olahan-lemon',
            'category_name' => 'Makanan & Olahan Kulit Lemon',
            'qty_col' => 'C',
            'total_col' => 'F',
        ],
        'sari_lemon' => [
            'code' => 'ELM-SRL-LTR',
            'name' => 'Sari Lemon (Curah Liter)',
            'unit' => 'Liter',
            'unit_label' => 'L',
            'category_slug' => 'sari-ekstrak-lemon',
            'category_name' => 'Sari & Ekstrak Lemon Murni',
            'qty_col' => 'D',
            'total_col' => 'G',
        ],
    ];

    private const IMPORT_USER_ID = 1;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    // ============================================================
    // CRUD METHODS
    // ============================================================

    public function index(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $channel = $request->get('channel');
        $status = $request->get('status');

        $query = SalesTransaction::with(['items.product', 'user']);

        if ($search) {
            $query->search($search);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        if ($channel) {
            $query->where('sales_channel', $channel);
        }

        if ($status) {
            $query->where('payment_status', $status);
        }

        $metricQuery = clone $query;
        $totalOmset = (float) $metricQuery->where('payment_status', '!=', 'Dibatalkan')->sum('total_amount');
        $totalCount = $metricQuery->count();

        $transactions = $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $channels = ['Toko Offline', 'WhatsApp Order', 'Reseller / Distributor', 'Konsinyasi Kafe', 'Shopee / Marketplace'];
        $statuses = ['Lunas', 'Menunggu Pembayaran', 'Dibatalkan'];

        return view('transactions.index', compact(
            'transactions',
            'search',
            'startDate',
            'endDate',
            'channel',
            'status',
            'channels',
            'statuses',
            'totalOmset',
            'totalCount'
        ));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(SalesTransaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        return view('transactions.create', compact('products', 'invoiceNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'sales_channel' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'payment_status' => ['required', 'string'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $itemsData = $request->input('items', []);
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($itemsData as $row) {
                $product = Product::find($row['product_id']);
                if (!$product) continue;

                $qty = (int) $row['quantity'];
                $price = (float) $row['unit_price'];
                $rowSubtotal = $qty * $price;
                $rawLemon = $qty * (float) $product->raw_lemon_requirement;

                $subtotal += $rowSubtotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'cost_price' => (float) $product->cost_price,
                    'subtotal' => $rowSubtotal,
                    'raw_lemon_used' => $rawLemon,
                ];

                if ($request->input('payment_status') !== 'Dibatalkan') {
                    $product->decrement('stock', $qty);
                }
            }

            $discount = (float) $request->input('discount', 0);
            $totalAmount = max(0, $subtotal - $discount);

            $invoiceNumber = $request->input('invoice_number') ?: 'INV-' . date('Ymd') . '-' . str_pad(SalesTransaction::count() + 1, 4, '0', STR_PAD_LEFT);

            $transaction = SalesTransaction::create([
                'invoice_number' => $invoiceNumber,
                'transaction_date' => $request->input('transaction_date'),
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'sales_channel' => $request->input('sales_channel'),
                'payment_method' => $request->input('payment_method'),
                'payment_status' => $request->input('payment_status'),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total_amount' => $totalAmount,
                'notes' => $request->input('notes'),
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsToCreate as $it) {
                $it['sales_transaction_id'] = $transaction->id;
                SalesTransactionItem::create($it);
            }

            DB::commit();
            return redirect()->route('transactions.show', $transaction)->with('success', "Transaksi {$transaction->invoice_number} berhasil dicatat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function show(SalesTransaction $transaction)
    {
        $transaction->load(['items.product.category', 'user']);
        return view('transactions.show', compact('transaction'));
    }

    public function edit(SalesTransaction $transaction)
    {
        $transaction->load('items.product');
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('transactions.edit', compact('transaction', 'products'));
    }

    public function update(Request $request, SalesTransaction $transaction)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'sales_channel' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'payment_status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.show', $transaction)->with('success', "Informasi transaksi {$transaction->invoice_number} berhasil diperbarui.");
    }

    public function destroy(SalesTransaction $transaction)
    {
        $invoice = $transaction->invoice_number;
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', "Transaksi {$invoice} berhasil dihapus.");
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        return $this->exportService->exportSalesPdf($startDate, $endDate);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        return $this->exportService->exportSalesExcel($startDate, $endDate);
    }

    // ============================================================
    // IMPORT METHODS
    // ============================================================

    /**
     * Tampilkan form upload file Excel.
     */
    public function importForm()
    {
        return view('transactions.import');
    }

    /**
     * Proses import file Excel dengan harga.
     *
     * Format file yang didukung:
     * | Tanggal | Dried Lemon (KG) | Manisan Lemon (Pouch) | Sari lemon (Liter) | Total Dried Lemon (Rp) | Total Manisan Lemon (Rp) | Total Sari Lemon (Rp) | Total Harus Dibayar (Rp) |
     */
    public function importStore(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ], [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
        ]);

        try {
            // Load file Excel
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            if (empty($rows)) {
                return back()->with('error', 'File Excel kosong atau tidak terbaca.');
            }

            // 1. Cari dan validasi header
            $headerResult = $this->findAndValidateHeader($rows);
            if (!$headerResult['valid']) {
                return back()->with('error', $headerResult['message']);
            }

            $colMap = $headerResult['col_map'];
            $headerRowIndex = $headerResult['header_row_index'];

            // 2. Pastikan produk ada di database
            $products = $this->ensureProductsExist();

            // 3. Parse data dari Excel
            $dailyData = $this->parseExcelData($rows, $headerRowIndex, $colMap);

            if (empty($dailyData)) {
                return back()->with('error', 'Tidak ada data valid yang ditemukan di file Excel.');
            }

            // 4. Import data ke database
            $result = $this->importDataToDatabase($dailyData, $products);

            // 5. Tampilkan ringkasan hasil
            $message = $this->buildSuccessMessage($result);

            return redirect()->route('transactions.index')->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Cari dan validasi header di file Excel
     */
    private function findAndValidateHeader(array $rows): array
    {
        $keywords = [
            'tanggal' => 'date',
            'dried lemon (kg)' => 'qty_dried',
            'total dried lemon' => 'total_dried',
            'manisan lemon (pouch)' => 'qty_manisan',
            'total manisan lemon' => 'total_manisan',
            'sari lemon (liter)' => 'qty_sari',
            'total sari lemon' => 'total_sari',
            'total harus dibayar' => 'total_all',
        ];

        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) continue;

            $rowText = strtolower(implode(' ', array_filter($row)));
            $matchedColumns = [];

            foreach ($keywords as $keyword => $colKey) {
                if (str_contains($rowText, $keyword)) {
                    $matchedColumns[] = $colKey;
                }
            }

            // Jika ditemukan minimal 5 kolom, anggap ini header
            if (count($matchedColumns) >= 5) {
                $colMap = $this->buildColumnMap($row, $keywords);
                return [
                    'valid' => true,
                    'col_map' => $colMap,
                    'header_row_index' => $index,
                    'message' => 'Header ditemukan.',
                ];
            }
        }

        return [
            'valid' => false,
            'col_map' => [],
            'header_row_index' => null,
            'message' => 'Tidak dapat menemukan header yang valid. Pastikan file memiliki kolom: Tanggal, Dried Lemon (KG), Manisan Lemon (Pouch), Sari lemon (Liter), Total Dried Lemon (Rp), Total Manisan Lemon (Rp), Total Sari Lemon (Rp), Total Harus Dibayar (Rp).',
        ];
    }

    /**
     * Bangun mapping kolom dari header
     */
    private function buildColumnMap(array $header, array $keywords): array
    {
        $colMap = [];

        foreach ($header as $index => $col) {
            $colLower = strtolower(trim((string) $col));

            foreach ($keywords as $keyword => $colKey) {
                if (str_contains($colLower, $keyword)) {
                    $colMap[$colKey] = $index;
                    break;
                }
            }
        }

        return $colMap;
    }

    /**
     * Parse data dari Excel
     */
    private function parseExcelData(array $rows, int $headerRowIndex, array $colMap): array
    {
        $dailyData = [];

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip baris kosong
            if (empty(array_filter($row))) continue;

            // Skip baris total / jumlah
            $firstCell = trim((string) ($row[0] ?? ''));
            if (stripos($firstCell, 'Jumlah') !== false || stripos($firstCell, 'Total') !== false) {
                continue;
            }

            // Parse tanggal
            $date = $this->parseDate($row[$colMap['date']] ?? '');
            if (!$date) continue;

            $dateKey = $date->format('Y-m-d');

            // Baca data per produk
            $productData = $this->extractProductData($row, $colMap);

            if (!empty($productData)) {
                $dailyData[$dateKey] = $productData;
            }
        }

        ksort($dailyData);
        return $dailyData;
    }

    /**
     * Ekstrak data produk dari satu baris
     */
    private function extractProductData(array $row, array $colMap): array
    {
        $productData = [];

        $productConfigs = [
            'dried_lemon' => ['code' => 'ELM-DRL-KG', 'qty_col' => 'qty_dried', 'total_col' => 'total_dried'],
            'manisan_lemon' => ['code' => 'ELM-MSN', 'qty_col' => 'qty_manisan', 'total_col' => 'total_manisan'],
            'sari_lemon' => ['code' => 'ELM-SRL-LTR', 'qty_col' => 'qty_sari', 'total_col' => 'total_sari'],
        ];

        foreach ($productConfigs as $config) {
            $qtyIdx = $colMap[$config['qty_col']] ?? null;
            $totalIdx = $colMap[$config['total_col']] ?? null;

            if ($qtyIdx === null || $totalIdx === null) continue;

            $qty = (int) ($row[$qtyIdx] ?? 0);
            $total = (float) ($row[$totalIdx] ?? 0);

            if ($qty > 0) {
                $productData[$config['code']] = [
                    'qty' => $qty,
                    'total' => $total,
                    'unit_price' => $qty > 0 ? round($total / $qty, 2) : 0,
                ];
            }
        }

        return $productData;
    }

    /**
     * Import data ke database
     */
    private function importDataToDatabase(array $dailyData, array $products): array
    {
        $imported = 0;
        $skipped = 0;
        $updatedPrices = [];

        DB::transaction(function () use ($dailyData, $products, &$imported, &$skipped, &$updatedPrices) {
            foreach ($dailyData as $dateKey => $productData) {
                // Skip jika tidak ada produk
                if (empty($productData)) {
                    $skipped++;
                    continue;
                }

                $invoiceNumber = $this->generateInvoiceNumber($dateKey);

                // Skip jika sudah ada
                if (SalesTransaction::where('invoice_number', $invoiceNumber)->exists()) {
                    $skipped++;
                    continue;
                }

                // Buat transaksi
                $result = $this->createTransactionFromData($dateKey, $productData, $products, $invoiceNumber);

                if ($result['success']) {
                    $imported++;
                    $updatedPrices = array_merge($updatedPrices, $result['updated_prices']);
                } else {
                    $skipped++;
                }
            }
        });

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'updated_prices' => $updatedPrices,
            'total_omset' => SalesTransaction::where('created_by', self::IMPORT_USER_ID)
                ->where('notes', 'LIKE', 'IMPORT EXCEL%')
                ->sum('total_amount'),
        ];
    }

    /**
     * Buat transaksi dari data
     */
    private function createTransactionFromData(string $dateKey, array $productData, array $products, string $invoiceNumber): array
    {
        $itemsToCreate = [];
        $notesParts = [];
        $subtotal = 0;
        $updatedPrices = [];

        foreach ($productData as $code => $data) {
            $product = $products[$code] ?? null;
            if (!$product) continue;

            $qty = (int) $data['qty'];
            $unitPrice = (float) $data['unit_price'];
            $rowSubtotal = $qty * $unitPrice;
            $rawLemon = $qty * (float) $product->raw_lemon_requirement;

            $subtotal += $rowSubtotal;

            $itemsToCreate[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'cost_price' => (float) $product->cost_price,
                'subtotal' => $rowSubtotal,
                'raw_lemon_used' => $rawLemon,
            ];

            // Update harga jual jika lebih tinggi
            if ($unitPrice > $product->selling_price) {
                $product->update(['selling_price' => $unitPrice]);
                $updatedPrices[$product->name] = $unitPrice;
            }

            $unitLabel = $this->getUnitLabel($code);
            $notesParts[] = "{$product->name}: {$qty} {$unitLabel} x Rp " . number_format($unitPrice, 0, ',', '.');
        }

        if (empty($itemsToCreate)) {
            return ['success' => false, 'updated_prices' => []];
        }

        // Buat transaksi
        $transaction = SalesTransaction::create([
            'invoice_number' => $invoiceNumber,
            'transaction_date' => $dateKey,
            'customer_name' => 'Data Historis (Import Excel)',
            'customer_phone' => null,
            'sales_channel' => 'Import Data Historis',
            'payment_method' => 'Tidak Diketahui (Import)',
            'payment_status' => 'Lunas',
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax' => 0,
            'total_amount' => $subtotal,
            'notes' => 'IMPORT EXCEL — ' . implode('; ', $notesParts) . ' | Total: Rp ' . number_format($subtotal, 0, ',', '.'),
            'created_by' => self::IMPORT_USER_ID,
        ]);

        foreach ($itemsToCreate as $item) {
            $item['sales_transaction_id'] = $transaction->id;
            SalesTransactionItem::create($item);
        }

        return ['success' => true, 'updated_prices' => $updatedPrices];
    }

    /**
     * Generate nomor invoice
     */
    private function generateInvoiceNumber(string $dateKey): string
    {
        $date = Carbon::parse($dateKey);
        $prefix = 'INV-IMP-' . $date->format('Ymd');
        $count = SalesTransaction::where('invoice_number', 'LIKE', $prefix . '%')->count() + 1;

        return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Dapatkan label unit dari kode produk
     */
    private function getUnitLabel(string $code): string
    {
        $map = [
            'ELM-DRL-KG' => 'kg',
            'ELM-MSN' => 'pouch',
            'ELM-SRL-LTR' => 'L',
        ];

        return $map[$code] ?? '';
    }

    /**
     * Pastikan produk ada di database
     */
    private function ensureProductsExist(): array
    {
        $result = [];

        foreach (self::PRODUCT_CONFIG as $config) {
            $category = Category::firstOrCreate(
                ['slug' => $config['category_slug']],
                ['name' => $config['category_name'], 'description' => null]
            );

            $product = Product::firstOrCreate(
                ['code' => $config['code']],
                [
                    'category_id' => $category->id,
                    'name' => $config['name'],
                    'slug' => \Illuminate\Support\Str::slug($config['name']),
                    'unit' => $config['unit'],
                    'raw_lemon_requirement' => 0,
                    'cost_price' => 0,
                    'selling_price' => 0,
                    'stock' => 0,
                    'min_stock_alert' => 10,
                    'description' => 'Dibuat otomatis dari import data penjualan Excel.',
                    'is_active' => true,
                ]
            );

            $result[$config['code']] = $product;
        }

        return $result;
    }

    /**
     * Parse tanggal dari berbagai format
     */
    private function parseDate(string $raw): ?Carbon
    {
        $raw = trim($raw);
        if (empty($raw)) return null;

        // Hapus nama hari jika ada: "SENIN, 01-09-2025" -> "01-09-2025"
        if (str_contains($raw, ',')) {
            $parts = explode(',', $raw);
            $raw = trim(end($parts));
        }

        try {
            // Coba format d-m-Y
            return Carbon::createFromFormat('d-m-Y', $raw)->startOfDay();
        } catch (\Exception $e) {
            try {
                // Coba format d/m/Y
                return Carbon::createFromFormat('d/m/Y', $raw)->startOfDay();
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    /**
     * Buat pesan sukses
     */
    private function buildSuccessMessage(array $result): string
    {
        $message = "✅ Import selesai! {$result['imported']} transaksi harian berhasil dibuat";

        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} tanggal dilewati (sudah ada)";
        }

        $message .= ".";

        if ($result['total_omset'] > 0) {
            $message .= "\n💰 Total omset terimport: Rp " . number_format($result['total_omset'], 0, ',', '.');
        }

        if (!empty($result['updated_prices'])) {
            $message .= "\n📊 Harga jual produk diperbarui:";
            foreach ($result['updated_prices'] as $name => $price) {
                $message .= "\n   - {$name}: Rp " . number_format($price, 0, ',', '.');
            }
        }

        return $message;
    }
}