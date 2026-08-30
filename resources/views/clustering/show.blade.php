@extends('layouts.app')

@section('title', 'Detail Analisis - ' . $clustering->title)
@section('page_title', 'Detail Analisis Clustering')
@section('page_subtitle', $clustering->title)

@section('content')
<div class="space-y-8">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('clustering.history') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Riwayat Analisis</span>
        </a>

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

    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-2">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Analisis Tersimpan</span>
                </div>
                <h3 class="text-2xl font-black tracking-tight">{{ $clustering->title }}</h3>
                <p class="text-xs text-slate-300 mt-1">
                    Periode: <strong>{{ $clustering->period_start->format('d/m/Y') }}</strong> s/d <strong>{{ $clustering->period_end->format('d/m/Y') }}</strong>
                    | Jumlah Klaster: <strong>k = {{ $clustering->k_value }}</strong>
                    | Iterasi: <strong>{{ $clustering->iterations_count }} kali</strong>
                    | SSE: <strong>{{ $clustering->sse_inertia }}</strong>
                    | DBI: <strong>{{ $clustering->davies_bouldin_index }}</strong>
                </p>
            </div>
            <div class="text-right text-xs text-slate-400">
                <span class="block">Oleh: <strong class="text-white">{{ $clustering->user ? $clustering->user->name : 'Administrator' }}</strong></span>
                <span class="block mt-0.5">{{ $clustering->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
        </div>
    </div>

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
                            <span class="text-xs font-bold text-slate-700">{{ $summ['member_count'] ?? 0 }} Hari</span>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-900 mt-3">{{ $summ['cluster_label'] ?? 'Klaster' }}</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $summ['description'] ?? '-' }}</p>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-black/5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Rata-rata X1 (Dried Lemon):</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['avg_x1_dried_lemon_kg'] ?? 0, 2, ',', '.') }} Kg</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Rata-rata X2 (Manisan Lemon):</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['avg_x2_manisan_lemon_pouch'] ?? 0, 0, ',', '.') }} Pouch</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Rata-rata X3 (Sari Lemon):</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['avg_x3_sari_lemon_liter'] ?? 0, 0, ',', '.') }} Liter</strong>
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

    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-4" x-data="{ chartPair: 'x2x3' }">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h4 class="text-base font-bold text-slate-900">Grafik Sebaran Klaster 2D</h4>
                <p class="text-xs text-slate-500">Pilih pasangan sumbu X1/X2/X3 yang ingin ditampilkan.</p>
            </div>
            <div class="inline-flex rounded-xl border border-slate-200 overflow-hidden text-xs font-bold">
                <button type="button" @click="chartPair = 'x1x2'; renderSavedScatterChart('x1x2')"
                        :class="chartPair === 'x1x2' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                        class="px-3 py-2 transition">X1 vs X2</button>
                <button type="button" @click="chartPair = 'x1x3'; renderSavedScatterChart('x1x3')"
                        :class="chartPair === 'x1x3' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                        class="px-3 py-2 transition border-l border-slate-200">X1 vs X3</button>
                <button type="button" @click="chartPair = 'x2x3'; renderSavedScatterChart('x2x3')"
                        :class="chartPair === 'x2x3' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                        class="px-3 py-2 transition border-l border-slate-200">X2 vs X3</button>
            </div>
        </div>
        <div class="h-80 w-full relative">
            <canvas id="savedScatterChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-base font-bold text-slate-900">Tabel Rinci Klasifikasi Hari</h4>
                <p class="text-xs text-slate-500">Hasil segmentasi harian penjualan produk olahan lemon</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">No</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4 text-center">Klaster</th>
                        <th class="py-3.5 px-4 text-right">X1 Dried Lemon (Kg)</th>
                        <th class="py-3.5 px-4 text-right">X2 Manisan Lemon (Pouch)</th>
                        <th class="py-3.5 px-4 text-right">X3 Sari Lemon (Liter)</th>
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
                            <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $res->day_name }}</td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $badge }}">
                                    {{ $res->cluster_code }} - {{ $res->cluster_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">
                                {{ number_format($res->x1_dried_lemon_kg, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-semibold text-slate-700">
                                {{ number_format($res->x2_manisan_lemon_pouch, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-semibold text-emerald-700">
                                {{ number_format($res->x3_sari_lemon_liter, 0, ',', '.') }}
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
        window.__savedClusterResults = @json($clustering->results);
        window.__savedScatterChartInstance = null;
        renderSavedScatterChart('x2x3');
    });

    function renderSavedScatterChart(pair) {
        const scatterCanvas = document.getElementById('savedScatterChart');
        if (!scatterCanvas) return;

        const results = window.__savedClusterResults || [];
        const axisMap = {
            'x1x2': { xKey: 'x1_dried_lemon_kg', yKey: 'x2_manisan_lemon_pouch', xLabel: 'X1 - Dried Lemon (Kg)', yLabel: 'X2 - Manisan Lemon (Pouch)' },
            'x1x3': { xKey: 'x1_dried_lemon_kg', yKey: 'x3_sari_lemon_liter', xLabel: 'X1 - Dried Lemon (Kg)', yLabel: 'X3 - Sari Lemon (Liter)' },
            'x2x3': { xKey: 'x2_manisan_lemon_pouch', yKey: 'x3_sari_lemon_liter', xLabel: 'X2 - Manisan Lemon (Pouch)', yLabel: 'X3 - Sari Lemon (Liter)' },
        };
        const axis = axisMap[pair] || axisMap['x2x3'];

        const clusterColors = {
            'C1': { bg: 'rgba(5, 150, 105, 0.8)', border: '#059669', label: 'C1 - Penjualan Tinggi' },
            'C2': { bg: 'rgba(245, 158, 11, 0.8)', border: '#d97706', label: 'C2 - Penjualan Sedang' },
            'C3': { bg: 'rgba(244, 63, 94, 0.8)', border: '#e11d48', label: 'C3 - Penjualan Rendah' },
        };

        const datasetsByCluster = {};
        results.forEach(r => {
            const code = r.cluster_code;
            if (!datasetsByCluster[code]) {
                const meta = clusterColors[code] || { bg: '#64748b', border: '#334155', label: code };
                datasetsByCluster[code] = {
                    label: meta.label, data: [], backgroundColor: meta.bg, borderColor: meta.border,
                    pointRadius: 6, pointHoverRadius: 9,
                };
            }
            datasetsByCluster[code].data.push({ x: r[axis.xKey], y: r[axis.yKey], day: r.day_name });
        });

        if (window.__savedScatterChartInstance) {
            window.__savedScatterChartInstance.destroy();
        }

        window.__savedScatterChartInstance = new Chart(scatterCanvas.getContext('2d'), {
            type: 'scatter',
            data: { datasets: Object.values(datasetsByCluster) },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => [ctx.raw.day, axis.xLabel + ': ' + ctx.raw.x, axis.yLabel + ': ' + ctx.raw.y]
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: axis.xLabel }, grid: { color: '#f1f5f9' } },
                    y: { title: { display: true, text: axis.yLabel }, grid: { color: '#f1f5f9' } }
                }
            }
        });
    }
</script>
@endpush