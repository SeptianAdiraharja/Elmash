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
     * Konfigurasi pemetaan sheet Excel -> produk yang akan dibuat/dipakai.
     * Sesuaikan di sini kalau nama sheet, kode produk, atau kategori berubah.
     */
    private const SHEET_MAP = [
        'Dried Lemoen' => [
            'product_code'   => 'ELM-DRL-KG',
            'product_name'   => 'Dried Lemoen (Curah KG)',
            'unit'           => 'KG',
            'unit_label'     => 'kg',
            'category_slug'  => 'makanan-olahan-lemon',
            'category_name'  => 'Makanan & Olahan Kulit Lemon',
        ],
        'Manisan Lemon' => [
            'product_code'   => 'ELM-MSN',
            'product_name'   => 'Manisan Lemon',
            'unit'           => 'Pouch',
            'unit_label'     => 'pouch',
            'category_slug'  => 'makanan-olahan-lemon',
            'category_name'  => 'Makanan & Olahan Kulit Lemon',
        ],
        'Sari Lemon' => [
            'product_code'   => 'ELM-SRL-LTR',
            'product_name'   => 'Sari Lemon (Curah Liter)',
            'unit'           => 'Liter',
            'unit_label'     => 'L',
            'category_slug'  => 'sari-ekstrak-lemon',
            'category_name'  => 'Sari & Ekstrak Lemon Murni',
        ],
    ];

    /** User ID yang dicatat sebagai pembuat transaksi hasil import. */
    private const IMPORT_USER_ID = 1;

    /**
     * @return array<string, string> product_code => unit_label (untuk teks notes)
     */
    private function unitLabelsByCode(): array
    {
        $labels = [];
        foreach (self::SHEET_MAP as $config) {
            $labels[$config['product_code']] = $config['unit_label'];
        }
        return $labels;
    }

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

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

        // Clone query for metrics
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
        ], [
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'items.required' => 'Minimal harus menambahkan 1 item produk.',
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

                // Decrement stock if active & paid
                if ($request->input('payment_status') !== 'Dibatalkan') {
                    $product->decrement('stock', $qty);
                }
            }

            $discount = (float) $request->input('discount', 0);
            $totalAmount = max(0, $subtotal - $discount);

            $invoiceNumber = $request->input('invoice_number');
            if (!$invoiceNumber) {
                $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(SalesTransaction::count() + 1, 4, '0', STR_PAD_LEFT);
            }

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

    /*
    |--------------------------------------------------------------------------
    | IMPORT DATA PENJUALAN DARI EXCEL (Dried Lemoen / Manisan Lemon / Sari Lemon)
    |--------------------------------------------------------------------------
    */

    /**
     * Tampilkan form upload file Excel.
     */
    public function importForm()
    {
        return view('transactions.import');
    }

    /**
     * Proses file Excel: baca 3 sheet, gabungkan per tanggal jadi 1 SalesTransaction
     * berisi 3 item produk, lalu update stok produk dari kolom SISA terakhir.
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
            $products = $this->ensureImportProducts();

            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());

            // dailyData[Y-m-d][product_code] = ['po' => x, 'kirim' => y, 'sisa' => z]
            $dailyData = [];

            foreach (self::SHEET_MAP as $sheetKey => $config) {
                $sheet = $this->findSheetByName($spreadsheet, $sheetKey);
                if (!$sheet) {
                    continue; // sheet tidak ditemukan di file ini, lewati
                }

                $rows = $sheet->toArray(null, true, true, false);

                foreach ($rows as $i => $row) {
                    if ($i === 0) continue; // baris header

                    $rawDate = trim((string) ($row[0] ?? ''));
                    $date = $this->parseIndonesianDate($rawDate);
                    if (!$date) continue; // gagal parse (baris kosong / "TOTAL (DUMMY)")

                    $dateKey = $date->format('Y-m-d');
                    $po = (float) ($row[1] ?? 0);
                    $kirim = (float) ($row[2] ?? 0);
                    $sisa = (float) ($row[3] ?? 0);

                    $dailyData[$dateKey][$config['product_code']] = [
                        'po' => $po,
                        'kirim' => $kirim,
                        'sisa' => $sisa,
                    ];
                }
            }

            if (empty($dailyData)) {
                return back()->with('error', 'Tidak ada data valid yang ditemukan di file Excel. Pastikan nama sheet sesuai: '
                    . implode(', ', array_keys(self::SHEET_MAP)));
            }

            ksort($dailyData); // urutkan tanggal ascending

            $imported = 0;
            $skipped = 0;
            $lastSisa = []; // product_code => sisa terakhir yang ditemukan

            $unitLabels = $this->unitLabelsByCode();

            DB::transaction(function () use ($dailyData, $products, $unitLabels, &$imported, &$skipped, &$lastSisa) {
                foreach ($dailyData as $dateKey => $productsOnThisDate) {
                    $invoiceNumber = 'INV-IMPORT-' . str_replace('-', '', $dateKey);

                    if (SalesTransaction::where('invoice_number', $invoiceNumber)->exists()) {
                        $skipped++;
                        continue;
                    }

                    $subtotal = 0;
                    $itemsToCreate = [];
                    $notesParts = [];

                    foreach ($productsOnThisDate as $code => $data) {
                        $product = $products[$code] ?? null;
                        if (!$product) continue;

                        $qty = (int) round($data['kirim']);
                        $price = (float) $product->selling_price; // masih 0 sampai diisi manual
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

                        $unitLabel = $unitLabels[$code] ?? '';
                        $poFormatted = rtrim(rtrim(number_format($data['po'], 2, ',', '.'), '0'), ',');

                        $notesParts[] = "{$product->name}: {$poFormatted} {$unitLabel}";

                        // Simpan sisa terakhir per produk (data terurut tanggal ascending
                        // jadi nilai ini otomatis akan jadi nilai TERAKHIR setelah loop selesai)
                        $lastSisa[$code] = $data['sisa'];
                    }

                    if (empty($itemsToCreate)) continue;

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
                        'notes' => 'MASUK P.O — ' . implode(', ', $notesParts),
                        'created_by' => self::IMPORT_USER_ID,
                    ]);

                    foreach ($itemsToCreate as $it) {
                        $it['sales_transaction_id'] = $transaction->id;
                        SalesTransactionItem::create($it);
                    }

                    $imported++;
                }

                // Update stok produk ke nilai SISA terakhir yang ditemukan di data
                foreach ($lastSisa as $code => $sisa) {
                    if (isset($products[$code])) {
                        $products[$code]->update(['stock' => (int) round($sisa)]);
                    }
                }
            });

            return redirect()->route('transactions.index')->with('success',
                "Import selesai: {$imported} transaksi harian berhasil dibuat, {$skipped} tanggal dilewati (sudah pernah diimpor sebelumnya). "
                . "Harga jual produk hasil import masih Rp 0 — silakan update manual di halaman Produk."
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Pastikan kategori & 3 produk baru (Dried Lemoen, Manisan Lemon, Sari Lemon) ada di database.
     * Aman dipanggil berulang kali (idempoten) — tidak akan membuat duplikat.
     *
     * @return array<string, Product> product_code => Product
     */
    private function ensureImportProducts(): array
    {
        $result = [];

        foreach (self::SHEET_MAP as $config) {
            $category = Category::firstOrCreate(
                ['slug' => $config['category_slug']],
                ['name' => $config['category_name'], 'description' => null]
            );

            $product = Product::firstOrCreate(
                ['code' => $config['product_code']],
                [
                    'category_id' => $category->id,
                    'name' => $config['product_name'],
                    'unit' => $config['unit'],
                    'raw_lemon_requirement' => 0,
                    'cost_price' => 0,
                    'selling_price' => 0,
                    'stock' => 0,
                    'min_stock_alert' => 10,
                    'description' => 'Dibuat otomatis dari import data penjualan Excel. Harga perlu diisi manual.',
                    'is_active' => true,
                ]
            );

            $result[$config['product_code']] = $product;
        }

        return $result;
    }

    /**
     * Cari sheet berdasarkan nama, toleran terhadap spasi ekstra di nama sheet
     * (mis. "Dried Lemoen " dengan spasi di belakang).
     */
    private function findSheetByName($spreadsheet, string $name)
    {
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (trim($sheetName) === trim($name)) {
                return $spreadsheet->getSheetByName($sheetName);
            }
        }
        return null;
    }

    /**
     * Parse tanggal format "KAMIS,02-01-2025" -> Carbon. Return null untuk baris
     * yang bukan data tanggal (header, "TOTAL (DUMMY)", baris kosong).
     */
    private function parseIndonesianDate(string $raw): ?Carbon
    {
        if ($raw === '' || stripos($raw, 'TOTAL') !== false || stripos($raw, 'TANGGAL') !== false) {
            return null;
        }

        $parts = explode(',', $raw);
        $datePart = trim(end($parts));

        try {
            return Carbon::createFromFormat('d-m-Y', $datePart)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
}