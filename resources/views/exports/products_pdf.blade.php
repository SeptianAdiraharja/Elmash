<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Master Produk - Elmas Fresh</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.4;
            padding: 15px;
            background: #ffffff;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #059669;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .company-address {
            font-size: 8pt;
            color: #64748b;
            margin-top: 3px;
        }

        .report-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            color: #0f172a;
            margin: 10px 0 4px 0;
        }

        .report-meta {
            font-size: 8pt;
            text-align: center;
            color: #64748b;
            margin-bottom: 14px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary-table td {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 8px;
            text-align: center;
            width: 25%;
        }

        .summary-table .label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
        }

        .summary-table .value {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
            display: block;
        }

        .summary-table .value.green { color: #059669; }
        .summary-table .value.amber { color: #d97706; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 7px;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #059669;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.3px;
            text-align: center;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-mono { font-family: Courier, monospace; }

        .badge-active {
            color: #065f46;
            background-color: #d1fae5;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-inactive {
            color: #475569;
            background-color: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
        }

        .badge-warning {
            color: #991b1b;
            background-color: #fee2e2;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sign-table td {
            border: none;
            width: 50%;
            text-align: center;
            font-size: 8pt;
        }

        .sign-space {
            height: 45px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div class="company-address">{{ $company['address'] }} | {{ $company['contact'] }}</div>
    </div>

    <div class="report-title">{{ $company['doc_title'] }}</div>
    <div class="report-meta">
        Dicetak pada: {{ date('d/m/Y H:i') }} WIB &bull; 
        Filter Kategori: {{ $categoryName }} &bull; 
        Status: {{ $statusLabel }}
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <span class="label">Total Produk</span>
                <span class="value">{{ $products->count() }} Item</span>
            </td>
            <td>
                <span class="label">Produk Aktif</span>
                <span class="value green">{{ $products->where('is_active', true)->count() }} Item</span>
            </td>
            <td>
                <span class="label">Total Stok Fisik</span>
                <span class="value">{{ number_format($products->sum('stock'), 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Perlu Restock</span>
                <span class="value amber">{{ $products->filter(fn($p) => $p->stock <= $p->min_stock_alert)->count() }} Item</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Kode SKU</th>
                <th style="width: 20%;">Nama Produk</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 7%;">Satuan</th>
                <th style="width: 11%;">Kebutuhan Lemon</th>
                <th style="width: 11%;">HPP (Modal)</th>
                <th style="width: 11%;">Harga Jual</th>
                <th style="width: 6%;">Stok</th>
                <th style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $idx => $prod)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-mono text-center">{{ $prod->code }}</td>
                    <td><strong>{{ $prod->name }}</strong></td>
                    <td>{{ $prod->category ? $prod->category->name : '-' }}</td>
                    <td class="text-center">{{ $prod->unit }}</td>
                    <td class="text-right">{{ number_format($prod->raw_lemon_requirement, 3, ',', '.') }} kg</td>
                    <td class="text-right">Rp {{ number_format($prod->cost_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($prod->stock <= $prod->min_stock_alert)
                            <span class="badge-warning">{{ $prod->stock }}</span>
                        @else
                            {{ $prod->stock }}
                        @endif
                    </td>
                    <td class="text-center">
                        @if($prod->is_active)
                            <span class="badge-active">Aktif</span>
                        @else
                            <span class="badge-inactive">Non-Aktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">Tidak ada data produk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table class="sign-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    <strong>Pimpinan UMKM Elmas Fresh</strong>
                    <div class="sign-space"></div>
                    <strong>( ______________________ )</strong>
                </td>
                <td>
                    Sukabumi, {{ date('d F Y') }}<br>
                    <strong>Bagian Operasional & Gudang</strong>
                    <div class="sign-space"></div>
                    <strong>( ______________________ )</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>