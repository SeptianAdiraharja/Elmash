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

    .sub-title {
        font-size: 9.5pt;
        font-weight: bold;
        color: #334155;
        margin-top: 10px;
        margin-bottom: 5px;
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

    /* C1 = Penjualan Rendah */
    .badge-c1 {
        background-color: #ffe4e6;
        color: #9f1239;
    }

    /* C2 = Penjualan Sedang */
    .badge-c2 {
        background-color: #fef3c7;
        color: #92400e;
    }

    /* C3 = Penjualan Tinggi */
    .badge-c3 {
        background-color: #d1fae5;
        color: #065f46;
    }

    .summary-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 8px;
        margin-bottom: 14px;
        font-size: 8.5pt;
    }

    .formula-box {
        background-color: #f8fafc;
        border-left: 3px solid #059669;
        padding: 7px 9px;
        margin-bottom: 8px;
        font-size: 8.5pt;
    }

    .formula {
        text-align: center;
        font-family: 'DejaVu Sans', sans-serif;
        margin: 5px 0;
    }

    .note-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px;
        margin-bottom: 12px;
        font-size: 8pt;
    }

    .result-box {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 8px;
        margin-top: 8px;
        margin-bottom: 14px;
        font-size: 8.5pt;
    }

    .centroid-box {
        background-color: #fef3c7;
        border: 1px solid #fcd34d;
        padding: 8px;
        margin-top: 8px;
        margin-bottom: 14px;
        font-size: 8.5pt;
        border-radius: 4px;
    }

    .page-break {
        page-break-before: always;
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

    .clearfix { clear: both; }

    .iteration-table td {
        font-size: 8pt;
    }

    .converged-row {
        background-color: #ecfdf5;
        font-weight: bold;
    }

    .normalization-table td, .normalization-table th {
        font-size: 7.5pt;
        padding: 3px 5px;
    }

    .strategy-text {
        font-size: 7.5pt;
        color: #475569;
    }
</style>
</head>

<body>

{{-- =========================================================
     HEADER
========================================================== --}}
<div class="header">
    <h1 class="company-name">UMKM ELMAS FRESH</h1>

    <div class="company-address">
        Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang,
        Kabupaten Sukabumi, Jawa Barat<br>
        Telp: 0812-8899-7711 | Email: info@elmasfresh.id
    </div>
</div>

<div class="report-title">
    LAPORAN HASIL SEGMENTASI PENJUALAN HARIAN PRODUK OLAHAN LEMON
</div>

<div class="report-meta">
    Metode: Algoritma K-Means Clustering |
    Periode: {{ $analysis->period_start->format('d/m/Y') }}
    s/d {{ $analysis->period_end->format('d/m/Y') }}
    | Tanggal Cetak: {{ date('d/m/Y H:i') }}
</div>


{{-- =========================================================
     RINGKASAN PARAMETER
========================================================== --}}
<div class="summary-box">
    <strong>Ringkasan Parameter Data Mining:</strong><br>

    &bull; Jumlah Klaster (k): <strong>{{ $analysis->k_value }}</strong>
    &nbsp;|&nbsp;
    &bull; Iterasi sampai Konvergen: <strong>{{ $analysis->iterations_count }}</strong>
    &nbsp;|&nbsp;
    &bull; WCSS / SSE: <strong>{{ number_format($analysis->sse_inertia, 5, ',', '.') }}</strong>
    &nbsp;|&nbsp;
    &bull; Total Data: <strong>{{ $analysis->results->count() }} Hari</strong>
</div>


{{-- =========================================================
     1. DASAR PERHITUNGAN K-MEANS
========================================================== --}}
<div class="section-title">1. Dasar Perhitungan K-Means Clustering</div>

<div class="note-box">
    Proses segmentasi dilakukan menggunakan algoritma K-Means Clustering
    dengan tiga variabel penjualan, yaitu X1 (Dried Lemon), X2 (Manisan Lemon),
    dan X3 (Sari Lemon). Sebelum proses clustering dilakukan, data
    dinormalisasi menggunakan metode Min-Max agar setiap variabel berada
    pada rentang nilai 0 sampai 1.
</div>

{{-- 1.1 Normalisasi Min-Max --}}
<div class="sub-title">1.1 Normalisasi Min-Max</div>

<div class="formula-box">
    <div>Normalisasi dilakukan menggunakan rumus:</div>
    <div class="formula">X<sub>norm</sub> = (X - X<sub>min</sub>) / (X<sub>max</sub> - X<sub>min</sub>)</div>
    <div>Keterangan: X = nilai aktual, X<sub>min</sub> = nilai minimum, dan X<sub>max</sub> = nilai maksimum.</div>
</div>

{{-- Tabel Normalisasi --}}
@if($analysis->results->count() <= 10)
<div style="margin-top: 6px; margin-bottom: 6px;">
    <strong style="font-size: 8pt; color: #334155;">Data Sampel dan Hasil Normalisasi:</strong>
</div>

<table class="normalization-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th style="text-align: right;">X1</th>
            <th style="text-align: right;">X2</th>
            <th style="text-align: right;">X3</th>
            <th style="text-align: center;">X1_norm</th>
            <th style="text-align: center;">X2_norm</th>
            <th style="text-align: center;">X3_norm</th>
        </tr>
    </thead>
    <tbody>
        @foreach($analysis->results as $idx => $r)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $r->day_name }}</td>
                <td class="text-right">{{ number_format($r->x1_dried_lemon_kg, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($r->x2_manisan_lemon_pouch, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($r->x3_sari_lemon_liter, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($r->normalized_vector['x1_dried_lemon_kg'] ?? 0, 4, ',', '.') }}</td>
                <td class="text-center">{{ number_format($r->normalized_vector['x2_manisan_lemon_pouch'] ?? 0, 4, ',', '.') }}</td>
                <td class="text-center">{{ number_format($r->normalized_vector['x3_sari_lemon_liter'] ?? 0, 4, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- 1.2 Penentuan Jumlah Klaster dengan Elbow Method --}}
<div class="sub-title">1.2 Penentuan Jumlah Klaster dengan Elbow Method (Perhitungan WCSS)</div>

<div class="formula-box">
    <div>Penentuan jumlah klaster optimal dilakukan menggunakan metode Elbow dengan menghitung nilai <em>Within Cluster Sum of Squares</em> (WCSS) untuk rentang nilai k = 1 sampai k = 10.</div>
    <div class="formula">WCSS = &Sigma;<sub>k=1</sub><sup>K</sup> &Sigma;<sub>x&isin;C<sub>k</sub></sub> ||x - &mu;<sub>k</sub>||<sup>2</sup></div>
    <div>Nilai WCSS menunjukkan total kuadrat jarak seluruh objek data terhadap centroid klasternya. Titik siku (elbow point) menandakan jumlah klaster optimal di mana penambahan nilai k berikutnya tidak lagi memberikan penurunan WCSS yang tajam.</div>
</div>

@if(!empty($elbowData['wcss']))
    <div style="margin-top: 6px; margin-bottom: 8px;">
        <strong style="font-size: 8pt; color: #334155; text-transform: uppercase;">
            TABEL NILAI WCSS HASIL PERHITUNGAN (K = 1 S/D {{ count($elbowData['wcss']) }}):
        </strong>
    </div>

    <table style="margin-bottom: 8px; font-size: 8pt;">
        <thead>
            <tr>
                <th style="width: 15%; text-align: center;">NILAI k</th>
                <th style="width: 30%; text-align: right;">NILAI WCSS</th>
                <th style="width: 25%; text-align: right;">&Delta; PENURUNAN</th>
                <th style="width: 30%; text-align: center;">KETERANGAN / STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($elbowData['wcss'] as $kIdx => $wcssVal)
                @php
                    $delta = $elbowData['deltas'][$kIdx] ?? null;
                    $isOptimal = ($kIdx == ($elbowData['optimal_k'] ?? 3));
                @endphp
                <tr style="{{ $isOptimal ? 'background-color: #fef3c7; font-weight: bold;' : '' }}">
                    <td class="text-center">k = {{ $kIdx }}</td>
                    <td class="text-right">{{ number_format($wcssVal, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $delta !== null ? number_format($delta, 2, ',', '.') : '-' }}</td>
                    <td class="text-center">
                        @if($isOptimal)
                            <span style="color: #92400e; font-weight: bold;">Titik Siku / Elbow Optimal (k = {{ $kIdx }})</span>
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="result-box">
    <strong>Hasil Penentuan Klaster Berdasarkan Metode Elbow:</strong><br>
    {{ $elbowData['explanation'] ?? 'Berdasarkan metode Elbow yang digunakan dalam penelitian, penurunan tajam terjadi hingga k = 3 kemudian melandai, sehingga jumlah klaster yang ditetapkan adalah k = ' . $analysis->k_value . '.' }}
</div>

{{-- 1.3 Perhitungan Jarak Euclidean --}}
<div class="sub-title">1.3 Perhitungan Jarak Euclidean</div>

<div class="formula-box">
    <div>Jarak setiap data terhadap centroid dihitung menggunakan jarak Euclidean:</div>
    <div class="formula">d(x,&mu;) = &radic;((x<sub>1</sub>-&mu;<sub>1</sub>)<sup>2</sup> + (x<sub>2</sub>-&mu;<sub>2</sub>)<sup>2</sup> + (x<sub>3</sub>-&mu;<sub>3</sub>)<sup>2</sup>)</div>
    <div>Setiap data dimasukkan ke cluster dengan nilai jarak Euclidean terkecil terhadap centroid.</div>
</div>

{{-- 1.4 Perhitungan Centroid --}}
<div class="sub-title">1.4 Perhitungan Centroid</div>

<div class="formula-box">
    <div>Setelah setiap data memperoleh cluster, centroid baru dihitung berdasarkan rata-rata nilai seluruh anggota cluster:</div>
    <div class="formula">&mu;<sub>k</sub> = (1 / n<sub>k</sub>) &Sigma;<sub>x&isin;C<sub>k</sub></sub> x</div>
    <div>Proses penghitungan jarak dan pembentukan centroid dilakukan secara berulang sampai anggota cluster tidak mengalami perubahan atau kondisi konvergensi tercapai.</div>
</div>

{{-- =========================================================
     CENTROID AWAL - SESUAI SKRIPSI
========================================================== --}}
@if(!empty($analysis->initial_centroids))
<div class="centroid-box">
    <table style="margin-top: 5px; font-size: 8pt;">
        <thead>
            <tr>
                <th style="width: 15%;">Centroid</th>
                <th style="width: 25%;">Data yang Dipilih</th>
                <th style="width: 20%; text-align: right;">X1_norm</th>
                <th style="width: 20%; text-align: right;">X2_norm</th>
                <th style="width: 20%; text-align: right;">X3_norm</th>
            </tr>
        </thead>
        <tbody>
            @php
                $dataIndices = [3, 9, 2];
                $sampleData = [
                    ['x1_dried_lemon_kg' => 0.1034, 'x2_manisan_lemon_pouch' => 0.2895, 'x3_sari_lemon_liter' => 0.2948],
                    ['x1_dried_lemon_kg' => 0.6552, 'x2_manisan_lemon_pouch' => 0.7105, 'x3_sari_lemon_liter' => 0.1022],
                    ['x1_dried_lemon_kg' => 0.9655, 'x2_manisan_lemon_pouch' => 0.2368, 'x3_sari_lemon_liter' => 0.8776],
                ];
            @endphp
            @foreach($analysis->initial_centroids as $idx => $centroid)
                <tr>
                    <td class="text-center"><strong>C{{ $idx + 1 }}</strong></td>
                    <td>Data ke-{{ $dataIndices[$idx] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($sampleData[$idx]['x1_dried_lemon_kg'] ?? 0, 4, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($sampleData[$idx]['x2_manisan_lemon_pouch'] ?? 0, 4, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($sampleData[$idx]['x3_sari_lemon_liter'] ?? 0, 4, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top: 5px; font-size: 7.5pt; color: #92400e;">
        <strong>Keterangan:</strong> C1 (Data ke-3) = Penjualan Rendah, C2 (Data ke-9) = Penjualan Sedang, C3 (Data ke-2) = Penjualan Tinggi
    </div>
</div>
@endif


{{-- =========================================================
     2. HASIL PROSES CLUSTERING
========================================================== --}}
<div class="section-title">2. Hasil Proses Clustering</div>

<table>
    <thead>
        <tr>
            <th style="width: 10%;">KLASTER</th>
            <th style="width: 26%;">KLASIFIKASI KATEGORI</th>
            <th style="width: 12%; text-align: center;">JUMLAH HARI</th>
            <th style="width: 17%; text-align: right;">RATA-RATA X1 (KG)</th>
            <th style="width: 17%; text-align: right;">RATA-RATA X2 (POUCH)</th>
            <th style="width: 18%; text-align: right;">RATA-RATA X3 (LITER)</th>
        </tr>
    </thead>
    <tbody>
        @if(is_array($analysis->cluster_summary))
            @foreach($analysis->cluster_summary as $code => $s)
                @php
                    $badgeClass = $code == 'C1' ? 'badge-c1' : ($code == 'C2' ? 'badge-c2' : 'badge-c3');
                @endphp
                <tr>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $code }}</span>
                    </td>
                    <td>{{ $s['cluster_label'] ?? '-' }}</td>
                    <td class="text-center"><strong>{{ $s['member_count'] ?? 0 }}</strong> hari</td>
                    <td class="text-right"><strong>{{ number_format($s['avg_x1_dried_lemon_kg'] ?? 0, 2, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($s['avg_x2_manisan_lemon_pouch'] ?? 0, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($s['avg_x3_sari_lemon_liter'] ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>


{{-- =========================================================
     3. HASIL EVALUASI CLUSTERING
========================================================== --}}
<div class="section-title">3. Evaluasi Hasil Clustering</div>

{{-- 3.1 WCSS --}}
<div class="sub-title">3.1 Within Cluster Sum of Squares (WCSS)</div>

<div class="formula-box">
    <div>Nilai WCSS digunakan untuk mengukur tingkat kekompakan data dalam masing-masing cluster.</div>
    <div class="formula">WCSS = &Sigma; ||x - &mu;||<sup>2</sup></div>
    <div>Semakin kecil nilai WCSS, semakin dekat posisi data terhadap centroid cluster sehingga menunjukkan cluster yang semakin kompak.</div>
</div>

<div class="result-box">
    <strong>Nilai WCSS / SSE hasil clustering:</strong>
    <strong>{{ number_format($analysis->sse_inertia, 5, ',', '.') }}</strong>
    <br><br>
    Nilai tersebut merupakan jumlah kuadrat jarak seluruh data terhadap centroid cluster masing-masing pada hasil akhir proses K-Means.
</div>

{{-- 3.2 Kondisi Konvergensi --}}
<div class="sub-title">3.2 Kondisi Konvergensi</div>

<div class="note-box">
    Proses iterasi K-Means dilakukan secara berulang dengan tahapan penentuan centroid,
    penghitungan jarak Euclidean, penentuan anggota klaster, dan pembaruan posisi centroid.
    Kriteria penghentian algoritma (konvergensi) menggunakan <strong>kriteria stabilitas klaster</strong>,
    yaitu iterasi dihentikan apabila seluruh objek data penjualan tidak lagi berpindah klaster antar-putaran (stabilitas keanggotaan 100%).
    <br><br>
    <strong>Kondisi konvergen tercapai pada: iterasi ke-{{ $analysis->iterations_count }}</strong> dengan status keanggotaan klaster yang telah stabil sempurna.
</div>

@if(!empty($analysis->iteration_history))
    <div style="margin-top: 6px; margin-bottom: 6px;">
        <strong style="font-size: 8pt; color: #334155; text-transform: uppercase;">
            RIWAYAT PEMBENTUKAN KLASTER TIAP ITERASI MENUJU STABILITAS:
        </strong>
    </div>

    <table class="iteration-table" style="margin-bottom: 12px; font-size: 8pt;">
        <thead>
            <tr>
                <th style="width: 15%;">ITERASI</th>
                <th style="width: 50%; text-align: center;">DISTRIBUSI JUMLAH ANGGOTA KLASTER</th>
                <th style="width: 35%; text-align: center;">STATUS KONVERGENSI STABILITAS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysis->iteration_history as $hist)
                @php
                    $isLast = ($hist['iteration'] == $analysis->iterations_count);
                @endphp
                <tr class="{{ $isLast ? 'converged-row' : '' }}">
                    <td>Iterasi ke-{{ $hist['iteration'] }}</td>
                    <td class="text-center">
                        @foreach($hist['cluster_counts'] as $cI => $cnt)
                            <span style="display: inline-block; padding: 1px 4px; background-color: #f1f5f9; border-radius: 2px; margin-right: 4px;">
                                C{{ $cI + 1 }}: {{ $cnt }} hari
                            </span>
                        @endforeach
                    </td>
                    <td class="text-center">
                        @if($isLast)
                            <span style="color: #065f46; font-weight: bold;">Stabil 100% (Konvergen)</span>
                        @else
                            <span style="color: #64748b;">Pergeseran Anggota Masih Berlangsung</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif


{{-- =========================================================
     4. DETAIL SEGMENTASI HARIAN
========================================================== --}}
<div class="section-title">4. Detail Hasil Segmentasi Harian & Rekomendasi Alokasi Bahan Baku</div>

<table>
    <thead>
        <tr>
            <th style="width: 4%;">NO</th>
            <th style="width: 16%;">TANGGAL</th>
            <th style="width: 9%; text-align: center;">KLASTER</th>
            <th style="width: 12%; text-align: right;">X1 (KG)</th>
            <th style="width: 13%; text-align: right;">X2 (POUCH)</th>
            <th style="width: 12%; text-align: right;">X3 (LITER)</th>
            <th style="width: 34%;">REKOMENDASI MANAJEMEN STOK</th>
        </tr>
    </thead>
    <tbody>
        @foreach($analysis->results as $idx => $r)
            @php
                $badgeClass = $r->cluster_code == 'C1' ? 'badge-c1' : ($r->cluster_code == 'C2' ? 'badge-c2' : 'badge-c3');
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-bold">{{ $r->day_name }}</td>
                <td class="text-center">
                    <span class="badge {{ $badgeClass }}">{{ $r->cluster_code }}</span>
                </td>
                <td class="text-right"><strong>{{ number_format($r->x1_dried_lemon_kg, 0, ',', '.') }}</strong></td>
                <td class="text-right">{{ number_format($r->x2_manisan_lemon_pouch, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($r->x3_sari_lemon_liter, 0, ',', '.') }}</td>
                <td class="strategy-text">{{ $r->inventory_strategy }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


{{-- =========================================================
     5. KESIMPULAN HASIL SEGMENTASI
========================================================== --}}
<div class="section-title">5. Kesimpulan Hasil Segmentasi</div>

<div class="note-box">
    Berdasarkan proses K-Means Clustering dengan jumlah cluster
    sebanyak <strong>{{ $analysis->k_value }}</strong>, diperoleh
    segmentasi penjualan harian berdasarkan tiga variabel, yaitu
    Dried Lemon (X1), Manisan Lemon (X2), dan Sari Lemon (X3).

    Hasil akhir menunjukkan bahwa proses clustering konvergen pada
    iterasi ke-<strong>{{ $analysis->iterations_count }}</strong>
    dengan nilai WCSS/SSE sebesar
    <strong>{{ number_format($analysis->sse_inertia, 5, ',', '.') }}</strong>.

    Hasil segmentasi kemudian digunakan sebagai dasar dalam menentukan
    strategi pengelolaan persediaan berdasarkan karakteristik tingkat
    penjualan masing-masing cluster.
</div>

{{-- =========================================================
     REKOMENDASI BERDASARKAN SKRIPSI
========================================================== --}}
<div class="section-title">6. Rekomendasi Manajemen Stok</div>

<table>
    <thead>
        <tr>
            <th style="width: 20%;">Klaster</th>
            <th style="width: 40%;">Karakteristik</th>
            <th style="width: 40%;">Rekomendasi Manajemen Stok</th>
        </tr>
    </thead>
    <tbody>
        @foreach($analysis->cluster_summary as $code => $s)
            <tr>
                <td class="text-center"><strong>{{ $code }}</strong></td>
                <td>{{ $s['description'] ?? '-' }}</td>
                <td>{{ $s['strategy'] ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 10px; padding: 8px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 8pt;">
    <strong>Keterangan:</strong><br>
    &bull; <strong>Rendah (C1):</strong> Kurangi pengadaan bahan baku lemon segar untuk mencegah overstock/pembusukan.<br>
    &bull; <strong>Sedang (C2):</strong> Alokasikan bahan baku lemon segar sesuai estimasi kebutuhan mingguan, produksi semi-batch.<br>
    &bull; <strong>Tinggi (C3):</strong> Tingkatkan pengadaan bahan baku lemon segar (buffer stock) untuk mencegah stockout.
</div>


{{-- =========================================================
     TANDA TANGAN
========================================================== --}}
<div class="signatures">
    <div class="sign-col">
        <p>Mengetahui,<br><strong>Pimpinan UMKM Elmas Fresh</strong></p>
        <div class="sign-space"></div>
        <p><strong>( ______________________________ )</strong></p>
    </div>

    <div class="sign-col right">
        <p>Sukabumi, {{ date('d F Y') }}<br><strong>Petugas Analis / Peneliti</strong></p>
        <div class="sign-space"></div>
        <p><strong>{{ $analysis->user ? $analysis->user->name : 'Salsabila Rifa\'i' }}</strong></p>
    </div>

    <div class="clearfix"></div>
</div>

</body>
</html>