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

    th,
    td {
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

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .text-bold {
        font-weight: bold;
    }

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
</style>
```

</head>

<body>

```
{{-- =========================================================
     HEADER
========================================================== --}}
<div class="header">
    <h1 class="company-name">UMKM ELMAS FRESH</h1>

    <div class="company-address">
        Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang,
        Kabupaten Sukabumi, Jawa Barat<br>
        Telp: 0812-8899-7711 |
        Email: info@elmasfresh.id
    </div>
</div>

<div class="report-title">
    LAPORAN HASIL SEGMENTASI PENJUALAN HARIAN PRODUK OLAHAN LEMON
</div>

<div class="report-meta">
    Metode: Algoritma K-Means Clustering |
    Periode:
    {{ $analysis->period_start->format('d/m/Y') }}
    s/d
    {{ $analysis->period_end->format('d/m/Y') }}
    |
    Tanggal Cetak:
    {{ date('d/m/Y H:i') }}
</div>


{{-- =========================================================
     RINGKASAN PARAMETER
========================================================== --}}
<div class="summary-box">

    <strong>Ringkasan Parameter Data Mining:</strong><br>

    &bull;
    Jumlah Klaster (k):
    <strong>{{ $analysis->k_value }}</strong>

    &nbsp;|&nbsp;

    &bull;
    Iterasi sampai Konvergen:
    <strong>{{ $analysis->iterations_count }}</strong>

    &nbsp;|&nbsp;

    &bull;
    WCSS / SSE:
    <strong>{{ number_format($analysis->sse_inertia, 5, ',', '.') }}</strong>

    &nbsp;|&nbsp;

    &bull;
    Davies-Bouldin Index:
    <strong>{{ number_format($analysis->davies_bouldin_index, 4, ',', '.') }}</strong>

    &nbsp;|&nbsp;

    &bull;
    Total Data:
    <strong>{{ $analysis->results->count() }} Hari</strong>

</div>


{{-- =========================================================
     1. DASAR PERHITUNGAN K-MEANS
========================================================== --}}
<div class="section-title">
    1. Dasar Perhitungan K-Means Clustering
</div>

<div class="note-box">
    Proses segmentasi dilakukan menggunakan algoritma K-Means Clustering
    dengan tiga variabel penjualan, yaitu X1 (Dried Lemon), X2 (Manisan Lemon),
    dan X3 (Sari Lemon). Sebelum proses clustering dilakukan, data
    dinormalisasi menggunakan metode Min-Max agar setiap variabel berada
    pada rentang nilai 0 sampai 1.
</div>


<div class="sub-title">
    1.1 Normalisasi Min-Max
</div>

<div class="formula-box">

    <div>
        Normalisasi dilakukan menggunakan rumus:
    </div>

    <div class="formula">
        X<sub>norm</sub> =
        (X - X<sub>min</sub>) /
        (X<sub>max</sub> - X<sub>min</sub>)
    </div>

    <div>
        Keterangan:
        X = nilai aktual,
        X<sub>min</sub> = nilai minimum,
        dan X<sub>max</sub> = nilai maksimum.
    </div>

</div>


<div class="sub-title">
    1.2 Penentuan Jumlah Klaster dengan Elbow Method
</div>

<div class="formula-box">

    <div>
        Penentuan jumlah klaster dilakukan menggunakan metode Elbow
        dengan menghitung nilai Within Cluster Sum of Squares (WCSS)
        untuk beberapa nilai k.
    </div>

    <div class="formula">
        WCSS =
        &Sigma;<sub>k=1</sub><sup>K</sup>
        &Sigma;<sub>x&isin;C<sub>k</sub></sub>
        ||x - &mu;<sub>k</sub>||<sup>2</sup>
    </div>

    <div>
        Nilai WCSS menunjukkan jumlah kuadrat jarak setiap data terhadap
        centroid pada cluster masing-masing. Jumlah cluster dipilih pada
        titik ketika penurunan WCSS mulai melandai (elbow).
    </div>

</div>


<div class="result-box">

    <strong>Hasil Penentuan Klaster:</strong><br>

    Berdasarkan metode Elbow yang digunakan dalam penelitian,
    jumlah klaster yang digunakan adalah:

    <strong>k = {{ $analysis->k_value }}</strong>.

    Nilai tersebut selanjutnya digunakan dalam proses K-Means Clustering
    sampai diperoleh kondisi konvergen.

</div>


<div class="sub-title">
    1.3 Perhitungan Jarak Euclidean
</div>

<div class="formula-box">

    <div>
        Jarak setiap data terhadap centroid dihitung menggunakan
        jarak Euclidean:
    </div>

    <div class="formula">
        d(x,&mu;) =
        &radic;(
        (x<sub>1</sub>-&mu;<sub>1</sub>)<sup>2</sup> +
        (x<sub>2</sub>-&mu;<sub>2</sub>)<sup>2</sup> +
        (x<sub>3</sub>-&mu;<sub>3</sub>)<sup>2</sup>
        )
    </div>

    <div>
        Setiap data dimasukkan ke cluster dengan nilai jarak
        Euclidean terkecil terhadap centroid.
    </div>

</div>


<div class="sub-title">
    1.4 Perhitungan Centroid
</div>

<div class="formula-box">

    <div>
        Setelah setiap data memperoleh cluster, centroid baru dihitung
        berdasarkan rata-rata nilai seluruh anggota cluster:
    </div>

    <div class="formula">
        &mu;<sub>k</sub> =
        (1 / n<sub>k</sub>)
        &Sigma;<sub>x&isin;C<sub>k</sub></sub> x
    </div>

    <div>
        Proses penghitungan jarak dan pembentukan centroid dilakukan
        secara berulang sampai anggota cluster tidak mengalami perubahan
        atau kondisi konvergensi tercapai.
    </div>

</div>


{{-- =========================================================
     2. HASIL PROSES CLUSTERING
========================================================== --}}
<div class="section-title">
    2. Hasil Proses Clustering
</div>

<table>
    <thead>
        <tr>
            <th style="width: 10%;">Klaster</th>
            <th style="width: 26%;">Klasifikasi Kategori</th>
            <th style="width: 12%; text-align: center;">
                Jumlah Hari
            </th>
            <th style="width: 17%; text-align: right;">
                Rata-rata X1 (Kg)
            </th>
            <th style="width: 17%; text-align: right;">
                Rata-rata X2 (Pouch)
            </th>
            <th style="width: 18%; text-align: right;">
                Rata-rata X3 (Liter)
            </th>
        </tr>
    </thead>

    <tbody>

        @if(is_array($analysis->cluster_summary))

            @foreach($analysis->cluster_summary as $code => $s)

                <tr>

                    <td class="text-center text-bold">
                        {{ $code }}
                    </td>

                    <td>
                        {{ $s['cluster_label'] ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $s['member_count'] ?? 0 }} hari
                    </td>

                    <td class="text-right text-bold">
                        {{ number_format(
                            $s['avg_x1_dried_lemon_kg'] ?? 0,
                            2,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-right text-bold">
                        {{ number_format(
                            $s['avg_x2_manisan_lemon_pouch'] ?? 0,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-right text-bold">
                        {{ number_format(
                            $s['avg_x3_sari_lemon_liter'] ?? 0,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                </tr>

            @endforeach

        @endif

    </tbody>
</table>


{{-- =========================================================
     3. HASIL EVALUASI CLUSTERING
========================================================== --}}
<div class="section-title">
    3. Evaluasi Hasil Clustering
</div>


<div class="sub-title">
    3.1 Within Cluster Sum of Squares (WCSS)
</div>

<div class="formula-box">

    <div>
        Nilai WCSS digunakan untuk mengukur tingkat kekompakan
        data dalam masing-masing cluster.
    </div>

    <div class="formula">
        WCSS =
        &Sigma;
        ||x - &mu;||<sup>2</sup>
    </div>

    <div>
        Semakin kecil nilai WCSS, semakin dekat posisi data terhadap
        centroid cluster sehingga menunjukkan cluster yang semakin
        kompak.
    </div>

</div>

<div class="result-box">

    <strong>Nilai WCSS / SSE hasil clustering:</strong>

    <strong>
        {{ number_format($analysis->sse_inertia, 5, ',', '.') }}
    </strong>

    <br><br>

    Nilai tersebut merupakan jumlah kuadrat jarak seluruh data
    terhadap centroid cluster masing-masing pada hasil akhir
    proses K-Means.

</div>


<div class="sub-title">
    3.2 Davies-Bouldin Index (DBI)
</div>

<div class="formula-box">

    <div>
        Evaluasi kualitas cluster juga dilakukan menggunakan
        Davies-Bouldin Index (DBI).
    </div>

    <div class="formula">
        DBI =
        (1 / k)
        &Sigma;<sub>i=1</sub><sup>k</sup>
        max<sub>j &ne; i</sub>
        {
        (S<sub>i</sub> + S<sub>j</sub>) /
        M<sub>ij</sub>
        }
    </div>

    <div>
        Nilai DBI yang lebih kecil menunjukkan cluster yang semakin
        kompak di dalam cluster dan semakin terpisah antar-cluster.
    </div>

</div>

<div class="result-box">

    <strong>
        Davies-Bouldin Index (DBI):
    </strong>

    <strong>
        {{ number_format(
            $analysis->davies_bouldin_index,
            4,
            ',',
            '.'
        ) }}
    </strong>

</div>


<div class="sub-title">
    3.3 Kondisi Konvergensi
</div>

<div class="note-box">

    Proses iterasi K-Means dilakukan secara berulang dengan tahapan
    penentuan centroid, penghitungan jarak Euclidean, penentuan
    anggota cluster, dan pembaruan centroid.

    Proses dihentikan setelah diperoleh kondisi konvergen pada:

    <strong>
        iterasi ke-{{ $analysis->iterations_count }}
    </strong>.

    Hasil akhir tersebut kemudian digunakan sebagai dasar segmentasi
    penjualan harian.

</div>


{{-- =========================================================
     4. DETAIL SEGMENTASI HARIAN
========================================================== --}}
<div class="section-title">
    4. Detail Hasil Segmentasi Harian & Rekomendasi Alokasi Bahan Baku
</div>

<table>

    <thead>

        <tr>

            <th style="width: 4%;">
                No
            </th>

            <th style="width: 16%;">
                Tanggal
            </th>

            <th style="width: 9%; text-align: center;">
                Klaster
            </th>

            <th style="width: 12%; text-align: right;">
                X1 (Kg)
            </th>

            <th style="width: 13%; text-align: right;">
                X2 (Pouch)
            </th>

            <th style="width: 12%; text-align: right;">
                X3 (Liter)
            </th>

            <th style="width: 34%;">
                Rekomendasi Manajemen Stok
            </th>

        </tr>

    </thead>

    <tbody>

        @foreach($analysis->results as $idx => $r)

            <tr>

                <td class="text-center">
                    {{ $idx + 1 }}
                </td>

                <td class="text-bold">
                    {{ $r->day_name }}
                </td>

                <td class="text-center">

                    <span class="badge
                        {{
                            $r->cluster_code == 'C1'
                            ? 'badge-c1'
                            : (
                                $r->cluster_code == 'C2'
                                ? 'badge-c2'
                                : 'badge-c3'
                            )
                        }}">

                        {{ $r->cluster_code }}

                    </span>

                </td>

                <td class="text-right text-bold">
                    {{ number_format(
                        $r->x1_dried_lemon_kg,
                        0,
                        ',',
                        '.'
                    ) }}
                </td>

                <td class="text-right">
                    {{ number_format(
                        $r->x2_manisan_lemon_pouch,
                        0,
                        ',',
                        '.'
                    ) }}
                </td>

                <td class="text-right">
                    {{ number_format(
                        $r->x3_sari_lemon_liter,
                        0,
                        ',',
                        '.'
                    ) }}
                </td>

                <td style="font-size: 7.5pt;">
                    {{ $r->inventory_strategy }}
                </td>

            </tr>

        @endforeach

    </tbody>

</table>


{{-- =========================================================
     5. KESIMPULAN HASIL SEGMENTASI
========================================================== --}}
<div class="section-title">
    5. Kesimpulan Hasil Segmentasi
</div>

<div class="note-box">

    Berdasarkan proses K-Means Clustering dengan jumlah cluster
    sebanyak <strong>{{ $analysis->k_value }}</strong>, diperoleh
    segmentasi penjualan harian berdasarkan tiga variabel, yaitu
    Dried Lemon (X1), Manisan Lemon (X2), dan Sari Lemon (X3).

    Hasil akhir menunjukkan bahwa proses clustering konvergen pada
    iterasi ke-<strong>{{ $analysis->iterations_count }}</strong>
    dengan nilai WCSS/SSE sebesar
    <strong>{{ number_format(
        $analysis->sse_inertia,
        5,
        ',',
        '.'
    ) }}</strong>
    dan nilai Davies-Bouldin Index sebesar
    <strong>{{ number_format(
        $analysis->davies_bouldin_index,
        4,
        ',',
        '.'
    ) }}</strong>.

    Hasil segmentasi kemudian digunakan sebagai dasar dalam menentukan
    strategi pengelolaan persediaan berdasarkan karakteristik tingkat
    penjualan masing-masing cluster.

</div>


{{-- =========================================================
     TANDA TANGAN
========================================================== --}}
<div class="signatures">

    <div class="sign-col">

        <p>
            Mengetahui,<br>
            <strong>Pimpinan UMKM Elmas Fresh</strong>
        </p>

        <div class="sign-space"></div>

        <p>
            <strong>
                ( ______________________________ )
            </strong>
        </p>

    </div>


    <div class="sign-col right">

        <p>
            Sukabumi, {{ date('d F Y') }}<br>
            <strong>Petugas Analis / Peneliti</strong>
        </p>

        <div class="sign-space"></div>

        <p>
            <strong>
                {{ $analysis->user
                    ? $analysis->user->name
                    : "Salsabila Rifa'i"
                }}
            </strong>
        </p>

    </div>

    <div style="clear: both;"></div>

</div>
</body>
</html>
