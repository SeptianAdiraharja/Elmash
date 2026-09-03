@extends('layouts.app')

@section('title', 'Eksekusi Analisis K-Means Clustering')
@section('page_title', 'Studio K-Means Clustering')
@section('page_subtitle', 'Segmentasi data penjualan harian produk olahan lemon berbasis algoritma machine learning K-Means')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'summary', saveModal: false, chartPair: 'x2x3' }">

    <!-- Parameter Setup Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="sliders" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-900">Parameter & Periode Analisis</h4>
                <p class="text-xs text-slate-500">Tentukan rentang tanggal transaksi dan jumlah klaster k</p>
            </div>
        </div>

        <form method="GET" action="{{ route('clustering.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <input type="hidden" name="run" value="1">

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Periode Mulai <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" value="{{ $startDate }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Periode Selesai <span class="text-rose-500">*</span></label>
                <input type="date" name="end_date" value="{{ $endDate }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Klaster (k) <span class="text-rose-500">*</span></label>
                <select name="k_value" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="3" {{ $kValue == 3 ? 'selected' : '' }}>k = 3 (Tinggi, Sedang, Rendah - Rekomendasi)</option>
                    <option value="2" {{ $kValue == 2 ? 'selected' : '' }}>k = 2 (Tinggi, Rendah)</option>
                    <option value="4" {{ $kValue == 4 ? 'selected' : '' }}>k = 4</option>
                    <option value="5" {{ $kValue == 5 ? 'selected' : '' }}>k = 5</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Maks. Iterasi</label>
                <input type="number" name="max_iterations" value="{{ $maxIterations }}" min="10" max="500"
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold rounded-xl shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="play" class="w-4 h-4 fill-slate-950"></i>
                    <span>Jalankan K-Means</span>
                </button>
            </div>
        </form>
    </div>

    @if($clusteringOutput)

        <!-- Algorithm Result Highlights -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-2">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    <span>Iterasi Selesai (Konvergen pada Iterasi ke-{{ $clusteringOutput['iterations_count'] }})</span>
                </div>
                <h3 class="text-2xl font-black tracking-tight">Hasil Analisis Segmentasi K-Means (k = {{ $clusteringOutput['k'] }})</h3>
                <p class="text-xs text-slate-300 mt-1">
                    Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                    | Total Data: <strong>{{ count($clusteringOutput['results']) }} Hari</strong>
                    | Nilai SSE: <strong>{{ $clusteringOutput['sse_inertia'] }}</strong>
                    | Davies-Bouldin Index: <strong>{{ $clusteringOutput['davies_bouldin_index'] }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button @click="saveModal = true" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="bookmark" class="w-4 h-4"></i>
                    <span>Simpan ke Riwayat</span>
                </button>
            </div>
        </div>

        <!-- Cluster Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($clusteringOutput['cluster_summary'] as $code => $summ)
                @php
                    $isC1 = $code == 'C1';
                    $isC2 = $code == 'C2';
                    $cardBg = $isC1 ? 'bg-emerald-50/80 border-emerald-200' : ($isC2 ? 'bg-amber-50/80 border-amber-200' : 'bg-rose-50/80 border-rose-200');
                    $badgeStyle = $isC1 ? 'bg-emerald-600 text-white' : ($isC2 ? 'bg-amber-500 text-slate-950' : 'bg-rose-500 text-white');
                @endphp
                <div class="rounded-3xl p-6 border {{ $cardBg }} shadow-sm flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1 rounded-xl text-xs font-black {{ $badgeStyle }} shadow-xs">{{ $code }}</span>
                            <span class="text-xs font-bold text-slate-700">{{ $summ['member_count'] }} Hari</span>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-900 mt-3">{{ $summ['cluster_label'] }}</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $summ['description'] }}</p>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-black/5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Rata-rata X1 (Dried Lemon):</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['avg_x1_dried_lemon_kg'], 2, ',', '.') }} Kg</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Rata-rata X2 (Manisan Lemon):</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['avg_x2_manisan_lemon_pouch'], 0, ',', '.') }} Pouch</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Rata-rata X3 (Sari Lemon):</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['avg_x3_sari_lemon_liter'], 0, ',', '.') }} Liter</strong>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white/80 border border-black/5 text-[11px] text-slate-700 leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Strategi Pengelolaan Stok:</strong>
                        {{ $summ['strategy'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Visual Chart & Detailed Tabs -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">

            <div class="flex items-center justify-between border-b border-slate-200 pb-3 flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <button @click="activeTab = 'summary'"
                            :class="activeTab === 'summary' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition cursor-pointer">
                        Tabel Klasifikasi Hari
                    </button>
                    <button @click="activeTab = 'chart'"
                            :class="activeTab === 'chart' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition cursor-pointer">
                        Grafik Scatter Plot 2D
                    </button>
                    <button @click="activeTab = 'math'"
                            :class="activeTab === 'math' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition cursor-pointer">
                        Langkah Perhitungan Matematis K-Means
                    </button>
                </div>
            </div>

            <!-- Tab 1: Daily Classification Table -->
            <div x-show="activeTab === 'summary'" class="space-y-4">
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
                                <th class="py-3.5 px-4">Rekomendasi Manajemen Persediaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($clusteringOutput['results'] as $idx => $res)
                                @php
                                    $badge = $res['cluster_code'] == 'C1' ? 'bg-emerald-100 text-emerald-800' : ($res['cluster_code'] == 'C2' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800');
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 text-slate-400 font-semibold">{{ $idx + 1 }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $res['day_name'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $badge }}">
                                            {{ $res['cluster_code'] }} - {{ $res['cluster_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">
                                        {{ number_format($res['x1_dried_lemon_kg'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-semibold text-slate-700">
                                        {{ number_format($res['x2_manisan_lemon_pouch'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-semibold text-emerald-700">
                                        {{ number_format($res['x3_sari_lemon_liter'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 max-w-xs text-[11px] leading-relaxed">
                                        {{ $res['inventory_strategy'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: 2D Scatter Plot with axis-pair toggle -->
            <div x-show="activeTab === 'chart'" class="space-y-4" style="display: none;">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h5 class="text-sm font-bold text-slate-900">Visualisasi Sebaran Klaster 2D</h5>
                        <p class="text-xs text-slate-500">Data memiliki 3 variabel (X1, X2, X3) — pilih pasangan sumbu untuk ditampilkan.</p>
                    </div>
                    <div class="inline-flex rounded-xl border border-slate-200 overflow-hidden text-xs font-bold">
                        <button type="button" @click="chartPair = 'x1x2'; renderScatterChart('x1x2')"
                                :class="chartPair === 'x1x2' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                class="px-3 py-2 transition cursor-pointer">X1 vs X2</button>
                        <button type="button" @click="chartPair = 'x1x3'; renderScatterChart('x1x3')"
                                :class="chartPair === 'x1x3' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                class="px-3 py-2 transition border-l border-slate-200 cursor-pointer">X1 vs X3</button>
                        <button type="button" @click="chartPair = 'x2x3'; renderScatterChart('x2x3')"
                                :class="chartPair === 'x2x3' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                class="px-3 py-2 transition border-l border-slate-200 cursor-pointer">X2 vs X3</button>
                    </div>
                </div>
                <div class="h-96 w-full relative">
                    <canvas id="scatterChart"></canvas>
                </div>
            </div>

            <!-- Tab 3: Step-by-Step Mathematical Computation Log -->
            <div x-show="activeTab === 'math'" class="space-y-6" style="display: none;">

                <div>
                    <h5 class="text-sm font-bold text-slate-900 mb-1">1. Normalisasi Data Min-Max [0, 1]</h5>
                    <p class="text-xs text-slate-500 mb-3">
                        Formula: $x' = \frac{x - \min(x)}{\max(x) - \min(x)}$ diterapkan pada X1 (Dried Lemon Kg), X2 (Manisan Lemon Pouch), dan X3 (Sari Lemon Liter) karena rentang nilainya jauh berbeda.
                    </p>

                    <div class="overflow-x-auto max-h-96 overflow-y-auto border border-slate-100 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="sticky top-0 bg-slate-50">
                                <tr class="text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3 text-right">X1 (Kg)</th>
                                    <th class="py-2.5 px-3 text-right">X2 (Pouch)</th>
                                    <th class="py-2.5 px-3 text-right">X3 (Liter)</th>
                                    <th class="py-2.5 px-3 text-center font-bold">Vektor Ternormalisasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                                @foreach($clusteringOutput['results'] as $r)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-2 px-3 font-sans font-semibold text-slate-800 whitespace-nowrap">{{ $r['day_name'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-600">{{ $r['x1_dried_lemon_kg'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-600">{{ $r['x2_manisan_lemon_pouch'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-600">{{ $r['x3_sari_lemon_liter'] }}</td>
                                        <td class="py-2 px-3 text-center text-emerald-700 font-bold">
                                            [{{ implode(', ', array_values($r['normalized_vector'])) }}]
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <h5 class="text-sm font-bold text-slate-900 mb-1">2. Posisi Centroid Awal vs Centroid Akhir</h5>
                    <p class="text-xs text-slate-500 mb-3">Koordinat titik pusat klaster [X1_norm, X2_norm, X3_norm] setelah konvergensi.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                            <strong class="text-slate-900 block mb-2 font-bold">Centroid Awal (K-Means++ Seed):</strong>
                            <div class="space-y-1.5 font-mono text-[11px]">
                                @foreach($clusteringOutput['initial_centroids'] as $cIdx => $cVec)
                                    <div>
                                        <span class="font-bold text-slate-700">C{{ $cIdx + 1 }}:</span>
                                        [{{ implode(', ', $cVec) }}]
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-xs">
                            <strong class="text-emerald-950 block mb-2 font-bold">Centroid Akhir (Konvergen):</strong>
                            <div class="space-y-1.5 font-mono text-[11px]">
                                @foreach($clusteringOutput['final_centroids'] as $cIdx => $cVec)
                                    <div>
                                        <span class="font-bold text-emerald-800">C{{ $cIdx + 1 }}:</span>
                                        [{{ implode(', ', $cVec) }}]
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <h5 class="text-sm font-bold text-slate-900 mb-1">3. Riwayat Pergeseran Iterasi</h5>
                    <p class="text-xs text-slate-500 mb-3">Jumlah anggota (hari) pada setiap klaster di tiap putaran iterasi.</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-mono">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                                    <th class="py-2 px-3">Iterasi ke-</th>
                                    <th class="py-2 px-3 text-center">Distribusi Anggota Tiap Klaster</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($clusteringOutput['iteration_history'] as $hist)
                                    <tr>
                                        <td class="py-2 px-3 font-bold text-slate-800">Iterasi #{{ $hist['iteration'] }}</td>
                                        <td class="py-2 px-3 text-center text-slate-700">
                                            @foreach($hist['cluster_counts'] as $cI => $cnt)
                                                <span class="px-2 py-0.5 rounded bg-slate-100 font-bold mr-2">C{{ $cI + 1 }}: {{ $cnt }} hari</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- Save Result Modal -->
        <div x-show="saveModal" x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             style="display: none;">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4" @click.outside="saveModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-base font-bold text-slate-900">Simpan Analisis ke Riwayat</h4>
                    <button @click="saveModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('clustering.save') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                    <input type="hidden" name="k_value" value="{{ $kValue }}">
                    <input type="hidden" name="max_iterations" value="{{ $maxIterations }}">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Judul Sesi Analisis <span class="text-rose-500">*</span></label>
                        <input type="text" name="title"
                               value="Analisis Segmentasi Penjualan ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})"
                               required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan / Keterangan Analisis</label>
                        <textarea name="notes" rows="3" placeholder="Tujuan pengujian atau catatan khusus evaluasi stok lemon..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="saveModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 cursor-pointer">
                            Simpan Permanen
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @else
        <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center text-slate-400">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="sparkles" class="w-8 h-8"></i>
            </div>
            <h4 class="text-base font-bold text-slate-800">Siap Menjalankan Segmentasi K-Means</h4>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Pilih parameter dan klik tombol "Jalankan K-Means" di atas untuk memproses data penjualan harian produk olahan lemon.</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
@if($clusteringOutput)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.__clusterResults = @json($clusteringOutput['results']);
        window.__scatterChartInstance = null;
        renderScatterChart('x2x3');
    });

    function renderScatterChart(pair) {
        const scatterCanvas = document.getElementById('scatterChart');
        if (!scatterCanvas) return;

        const results = window.__clusterResults || [];
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
            'C4': { bg: 'rgba(100, 116, 139, 0.8)', border: '#475569', label: 'C4' },
        };

        const datasetsByCluster = {};
        results.forEach(r => {
            const code = r.cluster_code;
            if (!datasetsByCluster[code]) {
                const meta = clusterColors[code] || { bg: '#64748b', border: '#334155', label: code };
                datasetsByCluster[code] = {
                    label: meta.label,
                    data: [],
                    backgroundColor: meta.bg,
                    borderColor: meta.border,
                    pointRadius: 6,
                    pointHoverRadius: 9,
                };
            }
            datasetsByCluster[code].data.push({
                x: r[axis.xKey],
                y: r[axis.yKey],
                day: r.day_name,
            });
        });

        if (window.__scatterChartInstance) {
            window.__scatterChartInstance.destroy();
        }

        window.__scatterChartInstance = new Chart(scatterCanvas.getContext('2d'), {
            type: 'scatter',
            data: { datasets: Object.values(datasetsByCluster) },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const raw = ctx.raw;
                                return [raw.day, axis.xLabel + ': ' + raw.x, axis.yLabel + ': ' + raw.y];
                            }
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
@endif
@endpush