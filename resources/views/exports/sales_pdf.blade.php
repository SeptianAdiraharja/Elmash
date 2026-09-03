<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Penjualan & Segmentasi - Elmas Fresh</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 15px;
            background: #ffffff;
        }

        /* ============================================
           HEADER
        ============================================ */
        .header {
            text-align: center;
            border-bottom: 3px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #059669;
            letter-spacing: 1px;
            margin: 0;
        }

        .company-address {
            font-size: 8pt;
            color: #64748b;
            margin-top: 3px;
        }

        .company-phone {
            font-size: 8pt;
            color: #64748b;
        }

        /* ============================================
           JUDUL LAPORAN
        ============================================ */
        .report-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            color: #0f172a;
            margin: 12px 0 5px 0;
        }

        .report-subtitle {
            font-size: 9pt;
            text-align: center;
            color: #475569;
            margin-bottom: 4px;
        }

        .report-meta {
            font-size: 8.5pt;
            text-align: center;
            color: #64748b;
            margin-bottom: 16px;
        }

        /* ============================================
           SUMMARY CARDS
        ============================================ */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }

        .summary-card .label {
            font-size: 7.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card .value {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        .summary-card.green .value { color: #059669; }
        .summary-card.amber .value { color: #d97706; }
        .summary-card.rose .value { color: #e11d48; }
        .summary-card.blue .value { color: #2563eb; }

        /* ============================================
           TABEL
        ============================================ */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 7.5pt;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: middle;
        }

        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 6.5pt;
            letter-spacing: 0.3px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }

        /* ============================================
           BADGE CLUSTER
        ============================================ */
        .badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 6.5pt;
        }

        .badge-c1 {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-c2 {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-c3 {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* ============================================
           CLUSTER INSIGHT SECTION
        ============================================ */
        .insight-section {
            margin-top: 18px;
            padding: 14px 16px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
        }

        .insight-title {
            font-size: 10pt;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 8px;
        }

        .insight-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .insight-item {
            background: white;
            border-radius: 6px;
            padding: 10px 12px;
            border: 1px solid #d1fae5;
        }

        .insight-item .cluster-label {
            font-weight: bold;
            font-size: 9pt;
        }

        .insight-item .cluster-desc {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }

        .insight-item .cluster-stats {
            font-size: 7.5pt;
            color: #334155;
            margin-top: 4px;
        }

        .insight-item .strategy {
            font-size: 7pt;
            color: #065f46;
            margin-top: 4px;
            padding: 4px 8px;
            background: #ecfdf5;
            border-radius: 4px;
        }

        /* ============================================
           FOOTER / TANDA TANGAN
        ============================================ */
        .signatures {
            margin-top: 30px;
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

        .sign-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .sign-col {
            text-align: center;
            font-size: 8.5pt;
        }

        .sign-space {
            height: 50px;
        }

        .sign-line {
            border-top: 1px solid #94a3b8;
            width: 200px;
            margin: 0 auto;
        }

        /* ============================================
           PRINT OPTIMIZATION
        ============================================ */
        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
            .summary-card { background: #f8fafc !important; }
            table { font-size: 7pt; }
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .insight-grid {
                grid-template-columns: 1fr;
            }
            .sign-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================
         HEADER
    ============================================ -->
    <div class="header">
        <h1 class="company-name">🌿 UMKM ELMAS FRESH</h1>
        <div class="company-address">
            Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kabupaten Sukabumi, Jawa Barat
        </div>
        <div class="company-phone">
            📞 0812-8899-7711 &nbsp;|&nbsp; ✉️ info@elmasfresh.id
        </div>
    </div>

    <!-- ============================================
         JUDUL LAPORAN
    ============================================ -->
    <div class="report-title">
        LAPORAN TRANSAKSI PENJUALAN &amp; SEGMENTASI PRODUK
    </div>
    <div class="report-subtitle">
        Produk Olahan Lemon (Dried Lemon, Manisan Lemon, Sari Lemon)
    </div>
    <div class="report-meta">
        Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        &nbsp;|&nbsp; Tanggal Cetak: {{ date('d/m/Y H:i') }}
        &nbsp;|&nbsp; Total Data: {{ count($transactions) }} Transaksi
    </div>

    <!-- ============================================
         SUMMARY CARDS
    ============================================ -->
    <div class="summary-grid">
        <div class="summary-card green">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ count($transactions) }}</div>
        </div>
        <div class="summary-card blue">
            <div class="label">Total Item Terjual</div>
            <div class="value">{{ number_format($totalItems, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card amber">
            <div class="label">Total Omset</div>
            <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card rose">
            <div class="label">Rata-rata per Nota</div>
            <div class="value">Rp {{ number_format(count($transactions) > 0 ? $totalRevenue / count($transactions) : 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- ============================================
         HASIL CLUSTERING (Jika Tersedia)
    ============================================ -->
    @if(isset($clusteringOutput) && $clusteringOutput)
    <div class="insight-section">
        <div class="insight-title">
            📊 Hasil Segmentasi Penjualan (K-Means Clustering)
        </div>
        <div class="insight-grid">
            @foreach($clusteringOutput['cluster_summary'] as $code => $summ)
                @php
                    $colors = [
                        'C1' => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Rendah'],
                        'C2' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Sedang'],
                        'C3' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Tinggi'],
                    ];
                    $style = $colors[$code] ?? $colors['C1'];
                @endphp
                <div class="insight-item" style="border-left: 4px solid {{ $style['text'] }};">
                    <div class="cluster-label" style="color: {{ $style['text'] }};">
                        {{ $code }} - Penjualan {{ $style['label'] }}
                    </div>
                    <div class="cluster-desc">
                        {{ $summ['member_count'] }} hari transaksi
                    </div>
                    <div class="cluster-stats">
                        X1: {{ number_format($summ['avg_x1_dried_lemon_kg'] ?? 0, 2, ',', '.') }} Kg &nbsp;|&nbsp;
                        X2: {{ number_format($summ['avg_x2_manisan_lemon_pouch'] ?? 0, 0, ',', '.') }} Pouch &nbsp;|&nbsp;
                        X3: {{ number_format($summ['avg_x3_sari_lemon_liter'] ?? 0, 0, ',', '.') }} Liter
                    </div>
                    <div class="strategy">
                        💡 {{ $summ['strategy'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Parameter Clustering -->
        <div style="margin-top: 10px; font-size: 7.5pt; color: #475569; text-align: center; border-top: 1px solid #bbf7d0; padding-top: 10px;">
            <strong>Parameter Clustering:</strong>
            k = {{ $clusteringOutput['k'] }} &nbsp;|&nbsp;
            Iterasi Konvergen: ke-{{ $clusteringOutput['iterations_count'] }} &nbsp;|&nbsp;
            WCSS/SSE: {{ number_format($clusteringOutput['sse_inertia'], 5, ',', '.') }} &nbsp;|&nbsp;
            DBI: {{ number_format($clusteringOutput['davies_bouldin_index'], 4, ',', '.') }}
        </div>
    </div>
    @endif

    <!-- ============================================
         TABEL TRANSAKSI
    ============================================ -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">No Faktur</th>
                <th style="width: 9%;">Tanggal</th>
                <th style="width: 15%;">Pelanggan</th>
                <th style="width: 10%;">Channel</th>
                <th style="width: 18%;">Detail Produk</th>
                @if(isset($clusteringOutput) && $clusteringOutput)
                <th style="width: 7%; text-align: center;">Klaster</th>
                @endif
                <th style="width: 8%; text-align: center;">Status</th>
                <th style="width: 12%; text-align: right;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $idx => $t)
                @php
                    // Cari cluster untuk tanggal ini jika ada
                    $clusterInfo = null;
                    if (isset($clusteringOutput) && $clusteringOutput) {
                        $dateStr = $t->transaction_date->toDateString();
                        foreach ($clusteringOutput['results'] as $res) {
                            if ($res['transaction_date'] == $dateStr) {
                                $clusterInfo = $res;
                                break;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $t->invoice_number }}</td>
                    <td class="text-center">{{ $t->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $t->customer_name }}</td>
                    <td>{{ $t->sales_channel }}</td>
                    <td style="font-size: 7pt;">
                        @foreach($t->items as $it)
                            &bull; {{ $it->product_name }}
                            <span style="color: #64748b;">({{ $it->quantity }}×)</span>
                            @if(!$loop->last)<br>@endif
                        @endforeach
                    </td>
                    @if(isset($clusteringOutput) && $clusteringOutput)
                    <td class="text-center">
                        @if($clusterInfo)
                            <span class="badge badge-{{ strtolower($clusterInfo['cluster_code']) }}">
                                {{ $clusterInfo['cluster_code'] }}
                            </span>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    @endif
                    <td class="text-center">
                        @if($t->payment_status == 'Lunas')
                            <span style="color: #059669;">✓ Lunas</span>
                        @elseif($t->payment_status == 'Dibatalkan')
                            <span style="color: #e11d48;">✗ Batal</span>
                        @else
                            <span style="color: #d97706;">⏳ {{ $t->payment_status }}</span>
                        @endif
                    </td>
                    <td class="text-right text-bold">
                        {{ number_format($t->total_amount, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="{{ isset($clusteringOutput) ? 8 : 7 }}" class="text-right">
                    Total Keseluruhan
                </td>
                <td class="text-right">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- ============================================
         REKOMENDASI BERDASARKAN CLUSTERING
    ============================================ -->
    @if(isset($clusteringOutput) && $clusteringOutput)
    <div style="margin-top: 16px; padding: 12px 16px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px;">
        <div style="font-size: 8.5pt; font-weight: bold; color: #92400e; margin-bottom: 6px;">
            📋 Rekomendasi Pengelolaan Stok Berdasarkan Hasil Segmentasi:
        </div>
        <div style="font-size: 7.5pt; color: #78350f; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
            @foreach($clusteringOutput['cluster_summary'] as $code => $summ)
                <div>
                    <strong>{{ $code }} ({{ $summ['cluster_label'] }}):</strong>
                    {{ $summ['strategy'] }}
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ============================================
         TANDA TANGAN
    ============================================ -->
    <div class="signatures">
        <div class="sign-row">
            <div class="sign-col">
                <p>
                    Mengetahui,<br>
                    <strong>Pimpinan UMKM Elmas Fresh</strong>
                </p>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <p style="font-size: 7.5pt; color: #64748b; margin-top: 4px;">
                    ( _______________________ )
                </p>
            </div>
            <div class="sign-col">
                <p>
                    Sukabumi, {{ date('d F Y') }}<br>
                    <strong>Petugas Analis / Peneliti</strong>
                </p>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <p style="font-size: 7.5pt; color: #64748b; margin-top: 4px;">
                    Salsabila Rifa'i
                </p>
            </div>
        </div>
    </div>

</body>
</html>