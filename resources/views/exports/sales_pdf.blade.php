<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Penjualan - Elmas Fresh</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 15pt;
            font-weight: bold;
            color: #059669;
            margin: 0;
        }
        .company-address {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
        }
        .report-title {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0 4px 0;
            text-transform: uppercase;
            color: #0f172a;
        }
        .report-meta {
            font-size: 8.5pt;
            text-align: center;
            color: #475569;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8pt;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            vertical-align: middle;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 7pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .summary-cards {
            width: 100%;
            margin-bottom: 12px;
        }
        .summary-card {
            width: 48%;
            float: left;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            font-size: 8.5pt;
        }
        .summary-card.right {
            float: right;
        }
        .signatures {
            margin-top: 25px;
            width: 100%;
        }
        .sign-col {
            width: 45%;
            float: left;
            text-align: center;
            font-size: 8.5pt;
        }
        .sign-col.right {
            float: right;
        }
        .sign-space {
            height: 50px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="company-name">UMKM ELMAS FRESH</h1>
        <div class="company-address">
            Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kabupaten Sukabumi, Jawa Barat | Telp: 0812-8899-7711
        </div>
    </div>

    <div class="report-title">REKAPITULASI TRANSAKSI PENJUALAN PRODUK OLAHAN LEMON</div>
    <div class="report-meta">
        Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} | Tanggal Cetak: {{ date('d/m/Y H:i') }}
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            Total Transaksi: <strong>{{ count($transactions) }} Nota</strong><br>
            Total Item Produk Terjual: <strong>{{ number_format($totalItems, 0, ',', '.') }} Unit</strong>
        </div>
        <div class="summary-card right">
            Total Akumulasi Omset: <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong><br>
            Rata-rata Penjualan per Nota: <strong>Rp {{ number_format(count($transactions) > 0 ? $totalRevenue / count($transactions) : 0, 0, ',', '.') }}</strong>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 13%;">No Faktur</th>
                <th style="width: 9%;">Tanggal</th>
                <th style="width: 16%;">Nama Pelanggan</th>
                <th style="width: 12%;">Saluran Penjualan</th>
                <th style="width: 26%;">Detail Produk</th>
                <th style="width: 8%; text-align: center;">Status</th>
                <th style="width: 12%; text-align: right;">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $idx => $t)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $t->invoice_number }}</td>
                    <td class="text-center">{{ $t->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $t->customer_name }}</td>
                    <td>{{ $t->sales_channel }}</td>
                    <td style="font-size: 7.5pt;">
                        @foreach($t->items as $it)
                            &bull; {{ $it->product_name }} ({{ $it->quantity }}x)<br>
                        @endforeach
                    </td>
                    <td class="text-center">{{ $t->payment_status }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
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
            <p>Sukabumi, {{ date('d F Y') }}<br><strong>Bagian Administrasi & Keuangan</strong></p>
            <div class="sign-space"></div>
            <p><strong>( _______________________ )</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
