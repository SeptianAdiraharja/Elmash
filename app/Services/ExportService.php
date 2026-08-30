<?php

namespace App\Services;

use App\Models\ClusteringAnalysis;
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
    /**
     * Export Clustering Analysis to PDF.
     */
    public function exportClusteringPdf(ClusteringAnalysis $analysis)
    {
        $analysis->load(['results', 'user']);

        $pdf = Pdf::loadView('exports.clustering_pdf', [
            'analysis' => $analysis,
            'company' => [
                'name' => 'UMKM ELMAS FRESH',
                'address' => 'Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kab. Sukabumi, Jawa Barat',
                'contact' => 'Telp: 0812-8899-7711 | Email: info@elmasfresh.id',
                'doc_title' => 'LAPORAN HASIL SEGMENTASI PENJUALAN HARIAN PRODUK OLAHAN LEMON',
                'method' => 'Metode Algoritma K-Means Clustering',
            ]
        ])->setPaper('a4', 'portrait');

        $filename = 'Laporan-Clustering-ElmasFresh-' . $analysis->period_start->format('Ymd') . '-' . $analysis->period_end->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Clustering Analysis to Excel (XLSX).
     */
    public function exportClusteringExcel(ClusteringAnalysis $analysis): StreamedResponse
    {
        $analysis->load(['results']); // product/category relation dihapus

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Hasil Segmentasi
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Segmentasi');

        $sheet->setCellValue('A1', 'UMKM ELMAS FRESH - SUKABUMI');
        $sheet->setCellValue('A2', 'LAPORAN HASIL SEGMENTASI PENJUALAN HARIAN PRODUK OLAHAN LEMON (K-MEANS CLUSTERING)');
        $sheet->setCellValue('A3', 'Periode Analisis: ' . $analysis->period_start->format('d/m/Y') . ' s/d ' . $analysis->period_end->format('d/m/Y') . ' | Jumlah Klaster (k): ' . $analysis->k_value);
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        // Table Headers
        $headers = [
            'No',
            'Tanggal Transaksi',
            'Hari',
            'X1 - Dried Lemon Terkirim (Kg)',
            'X2 - Manisan Lemon Terkirim (Pouch)',
            'X3 - Sari Lemon Terkirim (Liter)',
            'Kode Klaster',
            'Label Kategori Penjualan',
            'Rekomendasi Manajemen Stok & Produksi',
        ];

        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:I5')->getFont()->setBold(true);
        $sheet->getStyle('A5:I5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFACC15');

        $row = 6;
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
        $sheet->getStyle('D6:D' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E6:E' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F6:F' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sheet 2: Ringkasan Klaster & Parameter
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Ringkasan Klaster');
        $sheet2->setCellValue('A1', 'RINGKASAN METRIK KLASTER K-MEANS');
        $sheet2->getStyle('A1')->getFont()->setBold(true);

        $summaryHeaders = [
            'Klaster',
            'Kategori',
            'Jumlah Hari',
            'Rata-rata X1 Dried Lemon (Kg)',
            'Rata-rata X2 Manisan Lemon (Pouch)',
            'Rata-rata X3 Sari Lemon (Liter)',
        ];
        $sheet2->fromArray($summaryHeaders, null, 'A3');
        $sheet2->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet2->getStyle('A3:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF059669');
        $sheet2->getStyle('A3:F3')->getFont()->getColor()->setARGB('FFFFFFFF');

        $sRow = 4;
        if (is_array($analysis->cluster_summary)) {
            foreach ($analysis->cluster_summary as $code => $summ) {
                $sheet2->setCellValue('A' . $sRow, $code);
                $sheet2->setCellValue('B' . $sRow, $summ['cluster_label'] ?? '-');
                $sheet2->setCellValue('C' . $sRow, $summ['member_count'] ?? 0);
                $sheet2->setCellValue('D' . $sRow, $summ['avg_x1_dried_lemon_kg'] ?? 0);
                $sheet2->setCellValue('E' . $sRow, $summ['avg_x2_manisan_lemon_pouch'] ?? 0);
                $sheet2->setCellValue('F' . $sRow, $summ['avg_x3_sari_lemon_liter'] ?? 0);
                $sRow++;
            }
        }

        $sheet2->getStyle('D4:F' . ($sRow - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'F') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Laporan-Clustering-ElmasFresh-' . $analysis->period_start->format('Ymd') . '.xlsx';
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

        $filename = 'Laporan-Penjualan-ElmasFresh-' . str_replace('-', '', $startDate) . '-' . str_replace('-', '', $endDate) . '.pdf';
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
        $sheet->getStyle('A5:K5')->getFont()->setBold(true);
        $sheet->getStyle('A5:K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFACC15');

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

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan-Penjualan-ElmasFresh-' . str_replace('-', '', $startDate) . '-' . str_replace('-', '', $endDate) . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
