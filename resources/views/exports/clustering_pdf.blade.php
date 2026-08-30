<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Segmentasi K-Means - Elmas Fresh</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #059669;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .company-address {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 3px;
        }
        .report-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 15px 0 5px 0;
            text-transform: uppercase;
            color: #0f172a;
        }
        .report-meta {
            font-size: 9pt;
            text-align: center;
            color: #475569;
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8.5pt;
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
            font-size: 7.5pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7.5pt;
        }
        .badge-c1 { background-color: #d1fae5; color: #065f46; }
        .badge-c2 { background-color: #fef3c7; color: #92400e; }
        .badge-c3 { background-color: #ffe4e6; color: #9f1239; }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 14px;
            font-size: 8.5pt;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .sign-col {
            width: 45%;
            float: left;
            text-align: center;
            font-size: 9pt;
        }
        .sign-col.right {
            float: right;
        }
        .sign-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="company-name">UMKM ELMAS FRESH</h1>
        <div class="company-address">
            Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kabupaten Sukabumi, Jawa Barat<br>
            Telp: 0812-8899-7711 | Email: info@elmasfresh.id
        </div>
    </div>

    <div class="report-title">LAPORAN HASIL SEGMENTASI PENJUALAN HARIAN PRODUK OLAHAN LEMON</div>
    <div class="report-meta">
        Metode: Algoritma K-Means Clustering | Periode: {{ $analysis->period_start->format('d/m/Y') }} s/d {{ $analysis->period_end->format('d/m/Y') }} | Tanggal Cetak: {{ date('d/m/Y H:i') }}
    </div>

    <div class="summary-box">
        <strong>Ringkasan Parameter Data Mining:</strong><br>
        &bull; Jumlah Klaster (k): <strong>{{ $analysis->k_value }}</strong> &nbsp;|&nbsp;
        &bull; Jumlah Iterasi Konvergen: <strong>{{ $analysis->iterations_count }}</strong> &nbsp;|&nbsp;
        &bull; Sum of Squared Errors (SSE): <strong>{{ $analysis->sse_inertia }}</strong> &nbsp;|&nbsp;
        &bull; Davies-Bouldin Index (DBI): <strong>{{ $analysis->davies_bouldin_index }}</strong> &nbsp;|&nbsp;
        &bull; Total Data: <strong>{{ $analysis->results->count() }} Hari</strong>
    </div>

    <div class="section-title">1. Ringkasan Karakteristik Klaster Penjualan Harian</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Klaster</th>
                <th style="width: 26%;">Klasifikasi Kategori</th>
                <th style="width: 12%; text-align: center;">Jumlah Hari</th>
                <th style="width: 17%; text-align: right;">Rata-rata X1 (Kg)</th>
                <th style="width: 17%; text-align: right;">Rata-rata X2 (Pouch)</th>
                <th style="width: 18%; text-align: right;">Rata-rata X3 (Liter)</th>
            </tr>
        </thead>
        <tbody>
            @if(is_array($analysis->cluster_summary))
                @foreach($analysis->cluster_summary as $code => $s)
                    <tr>
                        <td class="text-center text-bold">{{ $code }}</td>
                        <td>{{ $s['cluster_label'] ?? '-' }}</td>
                        <td class="text-center">{{ $s['member_count'] ?? 0 }} hari</td>
                        <td class="text-right text-bold">{{ number_format($s['avg_x1_dried_lemon_kg'] ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right text-bold">{{ number_format($s['avg_x2_manisan_lemon_pouch'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right text-bold">{{ number_format($s['avg_x3_sari_lemon_liter'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="section-title">2. Detail Hasil Segmentasi Harian & Rekomendasi Alokasi Bahan Baku</div>
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 16%;">Tanggal</th>
                <th style="width: 9%; text-align: center;">Klaster</th>
                <th style="width: 12%; text-align: right;">X1 (Kg)</th>
                <th style="width: 13%; text-align: right;">X2 (Pouch)</th>
                <th style="width: 12%; text-align: right;">X3 (Liter)</th>
                <th style="width: 34%;">Rekomendasi Manajemen Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysis->results as $idx => $r)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $r->day_name }}</td>
                    <td class="text-center">
                        <span class="badge {{ $r->cluster_code == 'C1' ? 'badge-c1' : ($r->cluster_code == 'C2' ? 'badge-c2' : 'badge-c3') }}">
                            {{ $r->cluster_code }}
                        </span>
                    </td>
                    <td class="text-right text-bold">{{ number_format($r->x1_dried_lemon_kg, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r->x2_manisan_lemon_pouch, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r->x3_sari_lemon_liter, 0, ',', '.') }}</td>
                    <td style="font-size: 7.5pt;">{{ $r->inventory_strategy }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div class="sign-col">
            <p>Mengetahui,<br><strong>Pimpinan UMKM Elmas Fresh</strong></p>
            <div class="sign-space"></div>
            <p><strong>( _______________________ )</strong></p>
        </div>
        <div class="sign-col right">
            <p>Sukabumi, {{ date('d F Y') }}<br><strong>Petugas Analis / Peneliti</strong></p>
            <div class="sign-space"></div>
            <p><strong>{{ $analysis->user ? $analysis->user->name : 'Salsabila Rifa\'i' }}</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>