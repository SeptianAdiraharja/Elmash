<?php

namespace App\Services;

use App\Models\ClusteringAnalysis;
use App\Models\Product;
use App\Models\SalesTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    protected KMeansService $kMeansService;

    public function __construct(?KMeansService $kMeansService = null)
    {
        $this->kMeansService = $kMeansService ?? new KMeansService();
    }

    /**
     * Export Clustering Analysis to PDF - SESUAI SKRIPSI
     */
    public function exportClusteringPdf(ClusteringAnalysis $analysis)
    {
        $analysis->load(['results', 'user']);

        // Hitung atau ambil data Elbow Method untuk ditampilkan dalam laporan PDF
        $elbowData = null;
        if (!empty($analysis->raw_data_snapshot)) {
            try {
                $features = $analysis->features ?? ['x1_dried_lemon_kg', 'x2_manisan_lemon_pouch', 'x3_sari_lemon_liter'];
                $elbowData = $this->kMeansService->computeElbowMethod(
                    $analysis->raw_data_snapshot,
                    10,
                    $features,
                    'skripsi_manual'
                );
            } catch (\Exception $e) {
                // Jika gagal, gunakan data kosong
                $elbowData = null;
            }
        }

        // Siapkan data centroid awal sesuai skripsi untuk ditampilkan
        $initialCentroids = $analysis->initial_centroids ?? null;

        // Format data untuk view
        $viewData = [
            'analysis' => $analysis,
            'elbowData' => $elbowData,
            'initialCentroids' => $initialCentroids,
            'company' => [
                'name' => 'UMKM ELMAS FRESH',
                'address' => 'Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kab. Sukabumi, Jawa Barat',
                'contact' => 'Telp: 0812-8899-7711 | Email: info@elmasfresh.id',
                'doc_title' => 'LAPORAN HASIL SEGMENTASI PENJUALAN HARIAN PRODUK OLAHAN LEMON',
                'method' => 'Metode Algoritma K-Means Clustering',
            ]
        ];

        $pdf = Pdf::loadView('exports.clustering_pdf', $viewData);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 150,
        ]);

        $filename = 'Laporan-Clustering-ElmasFresh-' .
            $analysis->period_start->format('Ymd') . '-' .
            $analysis->period_end->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export Clustering Analysis to Excel (XLSX) - SESUAI SKRIPSI
     */
    public function exportClusteringExcel(ClusteringAnalysis $analysis): StreamedResponse
    {
        $analysis->load(['results']);

        $spreadsheet = new Spreadsheet();

        // ============================================================
        // SHEET 1: Hasil Segmentasi Harian
        // ============================================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Segmentasi');

        // Header Perusahaan
        $sheet->setCellValue('A1', 'UMKM ELMAS FRESH - SUKABUMI');
        $sheet->setCellValue('A2', 'LAPORAN HASIL SEGMENTASI PENJUALAN HARIAN PRODUK OLAHAN LEMON (K-MEANS CLUSTERING)');
        $sheet->setCellValue('A3', 'Periode Analisis: ' . $analysis->period_start->format('d/m/Y') . ' s/d ' . $analysis->period_end->format('d/m/Y'));
        $sheet->setCellValue('A4', 'Jumlah Klaster (k): ' . $analysis->k_value . ' | Iterasi Konvergen: ' . $analysis->iterations_count . ' | SSE: ' . number_format($analysis->sse_inertia, 5, ',', '.'));

        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

        // Table Headers
        $headers = [
            'No',
            'Tanggal Transaksi',
            'Hari',
            'X1 - Dried Lemon (Kg)',
            'X2 - Manisan Lemon (Pouch)',
            'X3 - Sari Lemon (Liter)',
            'Klaster',
            'Kategori Penjualan',
            'Rekomendasi Manajemen Stok',
        ];

        $sheet->fromArray($headers, null, 'A6');
        $headerStyle = $sheet->getStyle('A6:I6');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF059669');
        $headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 7;
        foreach ($analysis->results as $idx => $res) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValueExplicit('B' . $row, $res->transaction_date->format('d/m/Y'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $res->day_name);
            $sheet->setCellValue('D' . $row, $res->x1_dried_lemon_kg);
            $sheet->setCellValue('E' . $row, $res->x2_manisan_lemon_pouch);
            $sheet->setCellValue('F' . $row, $res->x3_sari_lemon_liter);
            $sheet->setCellValue('G' . $row, $res->cluster_code);
            $sheet->setCellValue('H' . $row, $res->cluster_label);
            $sheet->setCellValue('I' . $row, $res->inventory_strategy);
            $row++;
        }

        // Format Numbers
        $sheet->getStyle('D7:D' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E7:E' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F7:F' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Border untuk tabel
        $sheet->getStyle('A6:I' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ============================================================
        // SHEET 2: Ringkasan Klaster & Parameter
        // ============================================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Ringkasan Klaster');

        $sheet2->setCellValue('A1', 'RINGKASAN PARAMETER DAN METRIK KLASTER K-MEANS');
        $sheet2->getStyle('A1')->getFont()->setBold(true);
        $sheet2->mergeCells('A1:F1');

        // Parameter
        $sheet2->setCellValue('A3', 'Parameter');
        $sheet2->setCellValue('B3', 'Nilai');
        $sheet2->getStyle('A3:B3')->getFont()->setBold(true);

        $params = [
            ['Jumlah Klaster (k)', $analysis->k_value],
            ['Iterasi Konvergen', 'Iterasi ke-' . $analysis->iterations_count],
            ['WCSS / SSE', number_format($analysis->sse_inertia, 5, ',', '.')],
            ['Davies-Bouldin Index', number_format($analysis->davies_bouldin_index, 4, ',', '.')],
            ['Total Data', $analysis->results->count() . ' Hari'],
            ['Metode Inisialisasi', 'Skripsi Manual (Data 3, 9, 2)'],
        ];

        $row = 4;
        foreach ($params as $param) {
            $sheet2->setCellValue('A' . $row, $param[0]);
            $sheet2->setCellValue('B' . $row, $param[1]);
            $row++;
        }

        // ============================================================
        // Centroid Awal - SESUAI SKRIPSI
        // ============================================================
        $row += 2;
        $sheet2->setCellValue('A' . $row, 'CENTROID AWAL (SESUAI SKRIPSI BAB 3)');
        $sheet2->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet2->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $centroidHeaders = ['Centroid', 'Data yang Dipilih', 'X1_norm', 'X2_norm', 'X3_norm'];
        $sheet2->fromArray($centroidHeaders, null, 'A' . $row);
        $sheet2->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCD34D');
        $row++;

        // Data centroid awal sesuai skripsi
        $centroidData = [
            ['C1', 'Data ke-3', '0,1034', '0,2895', '0,2948'],
            ['C2', 'Data ke-9', '0,6552', '0,7105', '0,1022'],
            ['C3', 'Data ke-2', '0,9655', '0,2368', '0,8776'],
        ];

        foreach ($centroidData as $cd) {
            $sheet2->fromArray($cd, null, 'A' . $row);
            $row++;
        }

        // ============================================================
        // Ringkasan Cluster
        // ============================================================
        $row += 2;
        $sheet2->setCellValue('A' . $row, 'RINGKASAN HASIL CLUSTERING');
        $sheet2->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet2->mergeCells('A' . $row . ':F' . $row);
        $row++;

        $summaryHeaders = [
            'Klaster',
            'Kategori',
            'Jumlah Hari',
            'Rata-rata X1 (Kg)',
            'Rata-rata X2 (Pouch)',
            'Rata-rata X3 (Liter)',
        ];
        $sheet2->fromArray($summaryHeaders, null, 'A' . $row);
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF059669');
        $sheet2->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        if (is_array($analysis->cluster_summary)) {
            foreach ($analysis->cluster_summary as $code => $summ) {
                $sheet2->setCellValue('A' . $row, $code);
                $sheet2->setCellValue('B' . $row, $summ['cluster_label'] ?? '-');
                $sheet2->setCellValue('C' . $row, $summ['member_count'] ?? 0);
                $sheet2->setCellValue('D' . $row, $summ['avg_x1_dried_lemon_kg'] ?? 0);
                $sheet2->setCellValue('E' . $row, $summ['avg_x2_manisan_lemon_pouch'] ?? 0);
                $sheet2->setCellValue('F' . $row, $summ['avg_x3_sari_lemon_liter'] ?? 0);
                $row++;
            }
        }

        $sheet2->getStyle('D' . ($row - count($analysis->cluster_summary ?? []) + 3) . ':F' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'F') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet2->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ============================================================
        // SHEET 3: Riwayat Iterasi
        // ============================================================
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Riwayat Iterasi');

        $sheet3->setCellValue('A1', 'RIWAYAT ITERASI K-MEANS CLUSTERING');
        $sheet3->getStyle('A1')->getFont()->setBold(true);
        $sheet3->mergeCells('A1:D1');

        $iterHeaders = ['Iterasi', 'Distribusi Anggota Klaster', 'Pergeseran Maks Centroid', 'Status Konvergensi'];
        $sheet3->fromArray($iterHeaders, null, 'A3');
        $sheet3->getStyle('A3:D3')->getFont()->setBold(true);
        $sheet3->getStyle('A3:D3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFACC15');
        $row = 4;

        if (!empty($analysis->iteration_history)) {
            foreach ($analysis->iteration_history as $hist) {
                $isLast = ($hist['iteration'] == $analysis->iterations_count);
                $distribusi = '';
                foreach ($hist['cluster_counts'] as $cI => $cnt) {
                    $distribusi .= 'C' . ($cI + 1) . ': ' . $cnt . ' hari ';
                }
                $sheet3->setCellValue('A' . $row, 'Iterasi ke-' . $hist['iteration']);
                $sheet3->setCellValue('B' . $row, trim($distribusi));
                $sheet3->setCellValue('C' . $row, isset($hist['max_centroid_shift']) ? number_format($hist['max_centroid_shift'], 4, ',', '.') : ($hist['changed_count'] ?? 0));
                $sheet3->setCellValue('D' . $row, $isLast ? 'Stabil 100% (Centroid Konvergen)' : 'Centroid Bergeser');

                if ($isLast) {
                    $sheet3->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD1FAE5');
                    $sheet3->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
                }
                $row++;
            }
        }

        foreach (range('A', 'D') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        // ============================================================
        // SHEET 4: Rekomendasi Manajemen Stok
        // ============================================================
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Rekomendasi Stok');

        $sheet4->setCellValue('A1', 'REKOMENDASI MANAJEMEN STOK BERDASARKAN KLASTER');
        $sheet4->getStyle('A1')->getFont()->setBold(true);
        $sheet4->mergeCells('A1:C1');

        $rekomHeaders = ['Klaster', 'Kategori', 'Rekomendasi Manajemen Stok'];
        $sheet4->fromArray($rekomHeaders, null, 'A3');
        $sheet4->getStyle('A3:C3')->getFont()->setBold(true);
        $sheet4->getStyle('A3:C3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF059669');
        $sheet4->getStyle('A3:C3')->getFont()->getColor()->setARGB('FFFFFFFF');

        $rekomendasiData = [
            ['C1', 'Penjualan Rendah', 'Kurangi pengadaan bahan baku lemon segar untuk mencegah overstock/pembusukan.'],
            ['C2', 'Penjualan Sedang', 'Alokasikan bahan baku lemon segar sesuai estimasi kebutuhan mingguan, produksi semi-batch.'],
            ['C3', 'Penjualan Tinggi', 'Tingkatkan pengadaan bahan baku lemon segar (buffer stock) untuk mencegah stockout.'],
        ];

        $row = 4;
        foreach ($rekomendasiData as $rd) {
            $sheet4->fromArray($rd, null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'C') as $col) {
            $sheet4->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet4->getStyle('A3:C' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Set active sheet ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Laporan-Clustering-ElmasFresh-' .
            $analysis->period_start->format('Ymd') . '-' .
            $analysis->period_end->format('Ymd') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export Sales Transactions to PDF.
     */
    public function exportSalesPdf(string $startDate, string $endDate)
    {
        $transactions = SalesTransaction::with(['items.product', 'user'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $totalRevenue = $transactions->where('payment_status', '!=', 'Dibatalkan')->sum('total_amount');
        $totalItems = $transactions->where('payment_status', '!=', 'Dibatalkan')->sum(function ($tx) {
            return $tx->items->sum('quantity');
        });

        $pdf = Pdf::loadView('exports.sales_pdf', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalRevenue' => $totalRevenue,
            'totalItems' => $totalItems,
            'company' => [
                'name' => 'UMKM ELMAS FRESH',
                'address' => 'Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kab. Sukabumi',
                'doc_title' => 'REKAPITULASI TRANSAKSI PENJUALAN PRODUK OLAHAN LEMON',
            ]
        ])->setPaper('a4', 'landscape');

        $filename = 'Laporan-Penjualan-ElmasFresh-' .
            str_replace('-', '', $startDate) . '-' .
            str_replace('-', '', $endDate) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export Sales Transactions to Excel.
     */
    public function exportSalesExcel(string $startDate, string $endDate): StreamedResponse
    {
        $transactions = SalesTransaction::with(['items.product', 'user'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Penjualan');

        $sheet->setCellValue('A1', 'UMKM ELMAS FRESH - SUKABUMI');
        $sheet->setCellValue('A2', 'LAPORAN TRANSAKSI PENJUALAN PRODUK OLAHAN LEMON');
        $sheet->setCellValue('A3', 'Periode: ' . $startDate . ' s/d ' . $endDate);
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');

        $headers = [
            'No',
            'No Faktur / Invoice',
            'Tanggal',
            'Nama Pelanggan',
            'Saluran Penjualan',
            'Metode Pembayaran',
            'Status',
            'Detail Produk & Qty',
            'Total Qty',
            'Total Transaksi (Rp)',
            'Catatan'
        ];

        $sheet->fromArray($headers, null, 'A5');
        $headerStyle = $sheet->getStyle('A5:K5');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFACC15');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 6;
        foreach ($transactions as $idx => $tx) {
            $itemDetails = $tx->items->map(function ($it) {
                return $it->product_name . ' (' . $it->quantity . 'x)';
            })->implode(', ');

            $qtySum = $tx->items->sum('quantity');

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $tx->invoice_number);
            $sheet->setCellValue('C' . $row, $tx->transaction_date->format('d/m/Y'));
            $sheet->setCellValue('D' . $row, $tx->customer_name);
            $sheet->setCellValue('E' . $row, $tx->sales_channel);
            $sheet->setCellValue('F' . $row, $tx->payment_method);
            $sheet->setCellValue('G' . $row, $tx->payment_status);
            $sheet->setCellValue('H' . $row, $itemDetails);
            $sheet->setCellValue('I' . $row, $qtySum);
            $sheet->setCellValue('J' . $row, $tx->total_amount);
            $sheet->setCellValue('K' . $row, $tx->notes);
            $row++;
        }

        $sheet->getStyle('I6:I' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('J6:J' . ($row - 1))->getNumberFormat()->setFormatCode('"Rp "#,##0');

        $sheet->getStyle('A5:K' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan-Penjualan-ElmasFresh-' .
            str_replace('-', '', $startDate) . '-' .
            str_replace('-', '', $endDate) . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export Products to PDF.
     */
    public function exportProductsPdf(?string $search = null, ?string $categoryId = null, ?string $status = null)
    {
        $query = Product::with('category');

        if ($search) {
            $query->search($search);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status == '1');
        }

        $products = $query->orderBy('name', 'asc')->get();

        $categoryName = 'Semua Kategori';
        if ($categoryId) {
            $cat = \App\Models\Category::find($categoryId);
            if ($cat) {
                $categoryName = $cat->name;
            }
        }

        $statusLabel = 'Semua Status';
        if ($status === '1') {
            $statusLabel = 'Aktif Saja';
        } elseif ($status === '0') {
            $statusLabel = 'Non-Aktif Saja';
        }

        $pdf = Pdf::loadView('exports.products_pdf', [
            'products' => $products,
            'categoryName' => $categoryName,
            'statusLabel' => $statusLabel,
            'company' => [
                'name' => 'UMKM ELMAS FRESH',
                'address' => 'Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kab. Sukabumi, Jawa Barat',
                'contact' => 'Telp: 0812-8899-7711 | Email: info@elmasfresh.id',
                'doc_title' => 'KATALOG MASTER PRODUK OLAHAN LEMON',
            ]
        ])->setPaper('a4', 'portrait');

        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        $filename = 'Katalog-Produk-ElmasFresh-' . date('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export Products to Excel.
     */
    public function exportProductsExcel(?string $search = null, ?string $categoryId = null, ?string $status = null): StreamedResponse
    {
        $query = Product::with('category');

        if ($search) {
            $query->search($search);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status == '1');
        }

        $products = $query->orderBy('name', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Produk');

        $sheet->setCellValue('A1', 'UMKM ELMAS FRESH - SUKABUMI');
        $sheet->setCellValue('A2', 'KATALOG MASTER PRODUK OLAHAN LEMON');
        $sheet->setCellValue('A3', 'Tanggal Unduh: ' . date('d/m/Y H:i') . ' WIB');
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $headers = [
            'No',
            'Kode SKU',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Kebutuhan Lemon (Kg/Unit)',
            'HPP Modal (Rp)',
            'Harga Jual (Rp)',
            'Stok',
            'Status'
        ];

        $sheet->fromArray($headers, null, 'A5');
        $headerStyle = $sheet->getStyle('A5:J5');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF059669');
        $headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 6;
        foreach ($products as $idx => $prod) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValueExplicit('B' . $row, $prod->code, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $prod->name);
            $sheet->setCellValue('D' . $row, $prod->category ? $prod->category->name : '-');
            $sheet->setCellValue('E' . $row, $prod->unit);
            $sheet->setCellValue('F' . $row, $prod->raw_lemon_requirement);
            $sheet->setCellValue('G' . $row, $prod->cost_price);
            $sheet->setCellValue('H' . $row, $prod->selling_price);
            $sheet->setCellValue('I' . $row, $prod->stock);
            $sheet->setCellValue('J' . $row, $prod->is_active ? 'Aktif' : 'Non-Aktif');
            $row++;
        }

        $lastRow = max(6, $row - 1);

        // Formats
        $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B6:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E6:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I6:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('F6:F' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('G6:H' . $lastRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('I6:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('A5:J' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Katalog-Produk-ElmasFresh-' . date('Ymd-His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}