@extends('layouts.app')

@section('title', 'Dashboard Ringkasan & Monitoring')
@section('page_title', 'Dashboard Monitoring')
@section('page_subtitle', 'Ringkasan performa penjualan produk olahan lemon dan status segmentasi K-Means')

@section('content')
<div class="space-y-8">

    <!-- Hero Banner with Case Study Context -->
    <div class="bg-gradient-to-r from-emerald-800 via-teal-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-3">
                    <span>🍋</span>
                    <span>UMKM Elmas Fresh - Sukabumi</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Sistem Segmentasi Penjualan Produk Olahan Lemon</h3>
                <p class="text-emerald-100/80 text-sm mt-2 leading-relaxed">
                    Pengambilan keputusan berbasis data menggunakan metode data mining <strong>K-Means Clustering</strong> untuk mengoptimalkan manajemen persediaan bahan baku lemon segar dan mencegah <em>overstock</em> maupun <em>stockout</em>.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('clustering.index') }}" class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-sm rounded-xl transition shadow-lg shadow-amber-400/20 flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-slate-950"></i>
                    <span>Eksekusi Clustering</span>
                </a>
                <a href="{{ route('transactions.index') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-sm rounded-xl transition border border-white/15 flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4"></i>
                    <span>Data Transaksi</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: Total Omset -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Omset Penjualan</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="text-2xl font-black text-slate-900 tracking-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    <span class="text-emerald-600 font-bold">Akumulasi</span> dari seluruh transaksi tercatat
                </p>
            </div>
        </div>

        <!-- Card 2: Total Units Sold -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Volume Produk Terjual</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="package-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalUnitsSold, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">Unit</span></h4>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    Dari <strong>{{ $totalProducts }}</strong> varian produk olahan aktif
                </p>
            </div>
        </div>

        <!-- Card 3: Total Lemon Raw Material Used -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Lemon Segar Terserap</span>
                <div class="w-10 h-10 rounded-2xl bg-yellow-50 text-yellow-600 flex items-center justify-center">
                    <i data-lucide="scale" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalRawLemonKg, 1, ',', '.') }} <span class="text-sm font-semibold text-slate-400">Kg</span></h4>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    Kebutuhan bahan baku buah lemon segar
                </p>
            </div>
        </div>

        <!-- Card 4: Total Transactions -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Jumlah Transaksi</span>
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">Nota/PO</span></h4>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    Transaksi lunas dan terverifikasi
                </p>
            </div>
        </div>

    </div>

    <!-- 2 Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Sales Trend Line/Area Chart (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h4 class="text-base font-bold text-slate-900">Tren Pendapatan & Penjualan Bulanan</h4>
                    <p class="text-xs text-slate-500">Pergerakan omset penjualan produk olahan lemon per bulan</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>Pendapatan (Rp)</span>
                </div>
            </div>

            <div class="h-72 w-full relative">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Right: Sales Channel Distribution Donut (1 Col) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs flex flex-col justify-between">
            <div>
                <h4 class="text-base font-bold text-slate-900">Distribusi Saluran Penjualan</h4>
                <p class="text-xs text-slate-500 mb-4">Kontribusi omset berdasarkan saluran distribusi</p>
                
                <div class="h-56 w-full relative flex items-center justify-center">
                    <canvas id="channelChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                @foreach($channelDistribution as $ch)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium truncate">{{ $ch->sales_channel }}</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($ch->total_omset, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Active Clustering Highlight & Raw Lemon Balance Problem Tracker -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Clustering Latest Result Widget (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">K-Means Aktif</span>
                        <h4 class="text-base font-bold text-slate-900">{{ $latestAnalysis ? $latestAnalysis->title : 'Belum Ada Analisis Tersimpan' }}</h4>
                    </div>
                    @if($latestAnalysis)
                        <p class="text-xs text-slate-500 mt-1">
                            Periode Data: <strong>{{ $latestAnalysis->period_start->format('d/m/Y') }}</strong> s/d <strong>{{ $latestAnalysis->period_end->format('d/m/Y') }}</strong> | Jumlah Klaster: <strong>k = {{ $latestAnalysis->k_value }}</strong>
                        </p>
                    @endif
                </div>

                @if($latestAnalysis)
                    <div class="flex items-center gap-2">
                        <a href="{{ route('clustering.show', $latestAnalysis) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            <span>Detail Analisis</span>
                        </a>
                        <a href="{{ route('clustering.export.pdf', $latestAnalysis) }}" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                            <i data-lucide="file-down" class="w-3.5 h-3.5"></i>
                            <span>Export PDF</span>
                        </a>
                    </div>
                @endif
            </div>

            @if($latestAnalysis && is_array($latestAnalysis->cluster_summary))
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    @foreach($latestAnalysis->cluster_summary as $cCode => $cData)
                        @php
                            $bgClass = $cCode == 'C1' ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : ($cCode == 'C2' ? 'bg-amber-50 border-amber-200 text-amber-950' : 'bg-rose-50 border-rose-200 text-rose-950');
                            $badgeClass = $cCode == 'C1' ? 'bg-emerald-600 text-white' : ($cCode == 'C2' ? 'bg-amber-500 text-slate-950' : 'bg-rose-500 text-white');
                        @endphp
                        <div class="p-4 rounded-2xl border {{ $bgClass }} flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-black {{ $badgeClass }}">{{ $cCode }}</span>
                                    <span class="text-xs font-bold">{{ $cData['member_count'] ?? 0 }} Produk</span>
                                </div>
                                <h5 class="text-sm font-bold mt-2.5 leading-snug">{{ $cData['cluster_label'] ?? 'Klaster' }}</h5>
                            </div>
                            <div class="mt-4 pt-3 border-t border-black/5 text-xs space-y-1">
                                <div class="flex justify-between text-slate-600">
                                    <span>Total Qty:</span>
                                    <strong class="text-slate-900">{{ number_format($cData['total_qty'] ?? 0, 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Total Omset:</span>
                                    <strong class="text-slate-900">Rp {{ number_format($cData['total_revenue'] ?? 0, 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Lemon Segar:</span>
                                    <strong class="text-slate-900">{{ number_format($cData['total_raw_lemon_kg'] ?? 0, 1, ',', '.') }} Kg</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Strategic Managerial Recommendation Highlight -->
                <div class="p-4 rounded-2xl bg-slate-900 text-slate-200 text-xs leading-relaxed flex items-start gap-3">
                    <div class="w-7 h-7 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center shrink-0 font-bold">
                        💡
                    </div>
                    <div>
                        <strong class="text-white block mb-0.5">Rekomendasi Manajerial Persediaan Bahan Baku:</strong>
                        Fokuskan 70% kuota pembelian lemon segar pada produk <strong>Klaster C1 (Tinggi)</strong> untuk menghindari <em>stockout</em>. Untuk <strong>Klaster C3 (Rendah)</strong>, batasi produksi dan berlakukan sistem <em>Make-To-Order</em> guna meminimalkan risiko pembusukan buah akibat <em>overstock</em>.
                    </div>
                </div>
            @else
                <div class="text-center py-10 text-slate-400">
                    <i data-lucide="sparkles" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <p class="text-sm">Belum ada hasil segmentasi K-Means yang tersimpan.</p>
                    <a href="{{ route('clustering.index') }}" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-semibold">
                        Jalankan Clustering Sekarang
                    </a>
                </div>
            @endif
        </div>

        <!-- Thesis Case Problem Monitor: Stock Balance Table (1 Col) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-base font-bold text-slate-900">Kondisi Stok Lemon</h4>
                    <p class="text-xs text-slate-500">Identifikasi masalah bahan baku aktual</p>
                </div>
                <a href="{{ route('lemon-stocks.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">
                    Semua
                </a>
            </div>

            <div class="space-y-2.5 overflow-y-auto max-h-[340px]">
                @foreach($lemonStocks as $st)
                    @php
                        $badge = $st->status == 'Kelebihan' ? 'bg-rose-100 text-rose-700' : ($st->status == 'Kekurangan' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                    @endphp
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-800">{{ \Carbon\Carbon::createFromFormat('Y-m', $st->period_month)->translatedFormat('F Y') }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $badge }}">
                                {{ $st->status }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-sm text-slate-900">{{ number_format($st->quantity_kg, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-500 block">Kg</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Bottom Tables: Top Products & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Top 5 Products -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h4 class="text-base font-bold text-slate-900">Top 5 Produk Terlaris</h4>
                    <p class="text-xs text-slate-500">Berdasarkan akumulasi kuantitas penjualan</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">Lihat Semua Produk &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase tracking-wider">
                            <th class="pb-3">Produk</th>
                            <th class="pb-3 text-right">Terjual</th>
                            <th class="pb-3 text-right">Total Omset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topProducts as $idx => $tp)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-slate-100 font-bold text-slate-700 flex items-center justify-center text-[10px]">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <a href="{{ route('products.show', $tp->product_id) }}" class="font-bold text-slate-800 hover:text-emerald-600 truncate block max-w-xs">
                                            {{ $tp->product_name }}
                                        </a>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $tp->product_code }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-right font-extrabold text-slate-900">
                                    {{ number_format($tp->total_sold, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-right font-bold text-emerald-600">
                                    Rp {{ number_format($tp->total_omset, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h4 class="text-base font-bold text-slate-900">Transaksi Penjualan Terbaru</h4>
                    <p class="text-xs text-slate-500">Catatan faktur / PO harian terbaru</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">Lihat Semua Transaksi &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase tracking-wider">
                            <th class="pb-3">Faktur & Tanggal</th>
                            <th class="pb-3">Pelanggan</th>
                            <th class="pb-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentTransactions as $rt)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3">
                                    <a href="{{ route('transactions.show', $rt) }}" class="font-mono font-bold text-emerald-700 hover:underline block">
                                        {{ $rt->invoice_number }}
                                    </a>
                                    <span class="text-[10px] text-slate-400">{{ $rt->transaction_date->format('d/m/Y') }}</span>
                                </td>
                                <td class="py-3 font-medium text-slate-700 truncate max-w-[140px]">
                                    {{ $rt->customer_name }}
                                    <span class="block text-[10px] text-slate-400">{{ $rt->sales_channel }}</span>
                                </td>
                                <td class="py-3 text-right font-extrabold text-slate-900">
                                    Rp {{ number_format($rt->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Sales Trend Chart
        const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
        if (trendCtx) {
            const months = @json($chartMonths);
            const revenues = @json($chartRevenues);

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Pendapatan Penjualan (Rp)',
                        data: revenues,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                },
                                font: { size: 10 }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        }

        // 2. Channel Donut Chart
        const channelCtx = document.getElementById('channelChart')?.getContext('2d');
        if (channelCtx) {
            const channels = @json($channelDistribution->pluck('sales_channel'));
            const amounts = @json($channelDistribution->pluck('total_omset'));

            new Chart(channelCtx, {
                type: 'doughnut',
                data: {
                    labels: channels,
                    datasets: [{
                        data: amounts,
                        backgroundColor: [
                            '#059669',
                            '#10B981',
                            '#F59E0B',
                            '#3B82F6',
                            '#8B5CF6'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, font: { size: 10 } }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endpush
