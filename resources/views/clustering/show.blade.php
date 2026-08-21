@extends('layouts.app')

@section('title', 'Detail Analisis - ' . $clustering->title)
@section('page_title', 'Detail Analisis Clustering')
@section('page_subtitle', $clustering->title)

@section('content')
<div class="space-y-8">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('clustering.history') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Riwayat Analisis</span>
        </a>

        <!-- Export Buttons -->
        <div class="flex items-center gap-2">
            <a href="{{ route('clustering.export.pdf', $clustering) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm shadow-rose-600/20">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                <span>Unduh Laporan PDF</span>
            </a>
            <a href="{{ route('clustering.export.excel', $clustering) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                <span>Unduh Laporan Excel</span>
            </a>
        </div>
    </div>

    <!-- Metadata Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-2">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Analisis Tersimpan</span>
                </div>
                <h3 class="text-2xl font-black tracking-tight">{{ $clustering->title }}</h3>
                <p class="text-xs text-slate-300 mt-1">
                    Periode: <strong>{{ $clustering->period_start->format('d/m/Y') }}</strong> s/d <strong>{{ $clustering->period_end->format('d/m/Y') }}</strong> | Jumlah Klaster: <strong>k = {{ $clustering->k_value }}</strong> | Iterasi: <strong>{{ $clustering->iterations_count }} kali</strong> | SSE: <strong>{{ $clustering->sse_inertia }}</strong> | DBI: <strong>{{ $clustering->davies_bouldin_index }}</strong>
                </p>
            </div>
            <div class="text-right text-xs text-slate-400">
                <span class="block">Oleh: <strong class="text-white">{{ $clustering->user ? $clustering->user->name : 'Administrator' }}</strong></span>
                <span class="block mt-0.5">{{ $clustering->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
        </div>
    </div>

    <!-- Cluster Summary Cards -->
    @if(is_array($clustering->cluster_summary))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($clustering->cluster_summary as $code => $summ)
                @php
                    $isC1 = $code == 'C1';
                    $isC2 = $code == 'C2';
                    $cardBg = $isC1 ? 'bg-emerald-50/80 border-emerald-200' : ($isC2 ? 'bg-amber-50/80 border-amber-200' : 'bg-rose-50/80 border-rose-200');
                    $badgeStyle = $isC1 ? 'bg-emerald-600 text-white' : ($isC2 ? 'bg-amber-500 text-slate-950' : 'bg-rose-500 text-white');
                @endphp
                <div class="rounded-3xl p-6 border {{ $cardBg }} shadow-xs flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1 rounded-xl text-xs font-black {{ $badgeStyle }} shadow-2xs">{{ $code }}</span>
                            <span class="text-xs font-bold text-slate-700">{{ $summ['member_count'] ?? 0 }} Produk</span>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-900 mt-3">{{ $summ['cluster_label'] ?? 'Klaster' }}</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $summ['description'] ?? '-' }}</p>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-black/5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Total Volume:</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['total_qty'] ?? 0, 0, ',', '.') }} Unit</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Total Omset:</span>
                            <strong class="text-slate-900 font-extrabold">Rp {{ number_format($summ['total_revenue'] ?? 0, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Bahan Lemon Segar:</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['total_raw_lemon_kg'] ?? 0, 1, ',', '.') }} Kg</strong>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white/80 border border-black/5 text-[11px] text-slate-700 leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Strategi Pengelolaan Stok:</strong>
                        {{ $summ['strategy'] ?? '-' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Scatter Plot Chart Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-4">
        <div>
            <h4 class="text-base font-bold text-slate-900">Grafik Sebaran Klaster 2D</h4>
            <p class="text-xs text-slate-500">Visualisasi relasi volume penjualan vs total omset per produk</p>
        </div>
        <div class="h-80 w-full relative">
            <canvas id="savedScatterChart"></canvas>
        </div>
    </div>

    <!-- Classified Products Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-base font-bold text-slate-900">Tabel Rinci Klasifikasi Produk</h4>
                <p class="text-xs text-slate-500">Hasil segmentasi per produk olahan lemon</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">No</th>
                        <th class="py-3.5 px-4">Kode SKU</th>
                        <th class="py-3.5 px-4">Nama Produk</th>
                        <th class="py-3.5 px-4 text-center">Klaster</th>
                        <th class="py-3.5 px-4 text-right">Qty Terjual</th>
                        <th class="py-3.5 px-4 text-center">Frekuensi</th>
                        <th class="py-3.5 px-4 text-right">Total Omset</th>
                        <th class="py-3.5 px-4 text-right">Lemon Segar</th>
                        <th class="py-3.5 px-4">Rekomendasi Manajemen Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($clustering->results as $idx => $res)
                        @php
                            $badge = $res->cluster_code == 'C1' ? 'bg-emerald-100 text-emerald-800' : ($res->cluster_code == 'C2' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-400 font-semibold">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $res->product_code }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ $res->product_name }}
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $res->category_name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $badge }}">
                                    {{ $res->cluster_code }} - {{ $res->cluster_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">
                                {{ number_format($res->total_qty, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                                {{ $res->frequency }}x
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-700 whitespace-nowrap">
                                Rp {{ number_format($res->total_revenue, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-semibold text-amber-700 whitespace-nowrap">
                                {{ number_format($res->raw_lemon_kg, 1, ',', '.') }} Kg
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs text-[11px] leading-relaxed">
                                {{ $res->inventory_strategy }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const scatterCanvas = document.getElementById('savedScatterChart');
        if (scatterCanvas) {
            const results = @json($clustering->results);
            
            const datasetsByCluster = {};
            const clusterColors = {
                'C1': { bg: 'rgba(5, 150, 105, 0.8)', border: '#059669', label: 'C1 - Penjualan Tinggi' },
                'C2': { bg: 'rgba(245, 158, 11, 0.8)', border: '#d97706', label: 'C2 - Penjualan Sedang' },
                'C3': { bg: 'rgba(244, 63, 94, 0.8)', border: '#e11d48', label: 'C3 - Penjualan Rendah' },
                'C4': { bg: 'rgba(100, 116, 139, 0.8)', border: '#475569', label: 'C4 - Penjualan Sangat Rendah' },
            };

            results.forEach(r => {
                const code = r.cluster_code;
                if (!datasetsByCluster[code]) {
                    const meta = clusterColors[code] || { bg: '#64748b', border: '#334155', label: code };
                    datasetsByCluster[code] = {
                        label: meta.label,
                        data: [],
                        backgroundColor: meta.bg,
                        borderColor: meta.border,
                        pointRadius: 8,
                        pointHoverRadius: 11,
                    };
                }
                datasetsByCluster[code].data.push({
                    x: r.total_qty,
                    y: r.total_revenue,
                    name: r.product_name,
                    sku: r.product_code,
                    lemon: r.raw_lemon_kg
                });
            });

            new Chart(scatterCanvas.getContext('2d'), {
                type: 'scatter',
                data: {
                    datasets: Object.values(datasetsByCluster)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const raw = ctx.raw;
                                    return [
                                        raw.name + ' (' + raw.sku + ')',
                                        'Qty: ' + raw.x + ' unit',
                                        'Omset: Rp ' + raw.y.toLocaleString('id-ID'),
                                        'Lemon: ' + raw.lemon + ' Kg'
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Total Kuantitas Terjual (Unit)' },
                            grid: { color: '#f1f5f9' }
                        },
                        y: {
                            title: { display: true, text: 'Total Omset Penjualan (Rp)' },
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(val) {
                                    return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
