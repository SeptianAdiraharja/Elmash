@extends('layouts.app')

@section('title', 'Eksekusi Analisis K-Means Clustering')
@section('page_title', 'Studio K-Means Clustering')
@section('page_subtitle', 'Segmentasi data penjualan produk olahan lemon berbasis algoritma machine learning K-Means')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'summary', saveModal: false }">

    <!-- Parameter Setup Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="sliders" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-900">Parameter & Periode Analisis</h4>
                <p class="text-xs text-slate-500">Tentukan rentang tanggal transaksi dan jumlah klaster k</p>
            </div>
        </div>

        <form method="GET" action="{{ route('clustering.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <input type="hidden" name="run" value="1">

            <!-- Start Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Periode Mulai <span class="text-rose-500">*</span></label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $startDate }}" 
                       required 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Periode Selesai <span class="text-rose-500">*</span></label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $endDate }}" 
                       required 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <!-- Cluster k -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Klaster (k) <span class="text-rose-500">*</span></label>
                <select name="k_value" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="3" {{ $kValue == 3 ? 'selected' : '' }}>k = 3 (Tinggi, Sedang, Rendah - Rekomendasi)</option>
                    <option value="2" {{ $kValue == 2 ? 'selected' : '' }}>k = 2 (Tinggi, Rendah)</option>
                    <option value="4" {{ $kValue == 4 ? 'selected' : '' }}>k = 4 (Sangat Tinggi, Tinggi, Sedang, Rendah)</option>
                    <option value="5" {{ $kValue == 5 ? 'selected' : '' }}>k = 5 (5 Tingkatan Klaster)</option>
                </select>
            </div>

            <!-- Max Iterations -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Maks. Iterasi</label>
                <input type="number" 
                       name="max_iterations" 
                       value="{{ $maxIterations }}" 
                       min="10" 
                       max="500" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold rounded-xl shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-2">
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
                    Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong> | Total Data: <strong>{{ count($clusteringOutput['results']) }} Produk</strong> | Nilai SSE: <strong>{{ $clusteringOutput['sse_inertia'] }}</strong> | Davies-Bouldin Index: <strong>{{ $clusteringOutput['davies_bouldin_index'] }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button @click="saveModal = true" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center gap-2">
                    <i data-lucide="bookmark" class="w-4 h-4"></i>
                    <span>Simpan ke Riwayat</span>
                </button>
            </div>
        </div>

        <!-- 3 Cluster Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($clusteringOutput['cluster_summary'] as $code => $summ)
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
                            <span class="text-xs font-bold text-slate-700">{{ $summ['member_count'] }} Varian Produk</span>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-900 mt-3">{{ $summ['cluster_label'] }}</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $summ['description'] }}</p>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-black/5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Total Volume Terjual:</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['total_qty'], 0, ',', '.') }} Unit</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Total Omset Penjualan:</span>
                            <strong class="text-slate-900 font-extrabold">Rp {{ number_format($summ['total_revenue'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Kebutuhan Lemon Segar:</span>
                            <strong class="text-slate-900 font-extrabold">{{ number_format($summ['total_raw_lemon_kg'], 1, ',', '.') }} Kg</strong>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white/80 border border-black/5 text-[11px] text-slate-700 leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Strategi Pengelolaan Stok:</strong>
                        {{ $summ['strategy'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Visual Scatter Chart & Detailed Tabs -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            
            <!-- Tab Navigation -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-3 flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <button @click="activeTab = 'summary'" 
                            :class="activeTab === 'summary' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition">
                        Tabel Klasifikasi Produk
                    </button>
                    <button @click="activeTab = 'chart'" 
                            :class="activeTab === 'chart' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition">
                        Grafik Scatter Plot 2D
                    </button>
                    <button @click="activeTab = 'math'" 
                            :class="activeTab === 'math' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition">
                        Langkah Perhitungan Matematis K-Means
                    </button>
                </div>
            </div>

            <!-- Tab 1: Product Classification Table -->
            <div x-show="activeTab === 'summary'" class="space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">No</th>
                                <th class="py-3.5 px-4">Kode SKU</th>
                                <th class="py-3.5 px-4">Nama Produk Olahan</th>
                                <th class="py-3.5 px-4 text-center">Klaster</th>
                                <th class="py-3.5 px-4 text-right">Total Qty</th>
                                <th class="py-3.5 px-4 text-center">Frekuensi</th>
                                <th class="py-3.5 px-4 text-right">Total Omset</th>
                                <th class="py-3.5 px-4 text-right">Lemon Segar (Kg)</th>
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
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $res['product_code'] }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-900">
                                        <a href="{{ route('products.show', $res['product_id']) }}" class="hover:text-emerald-600 transition">
                                            {{ $res['product_name'] }}
                                        </a>
                                        <span class="block text-[10px] text-slate-400 font-normal">{{ $res['category_name'] }} &bull; {{ $res['unit'] }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $badge }}">
                                            {{ $res['cluster_code'] }} - {{ $res['cluster_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">
                                        {{ number_format($res['total_qty'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                                        {{ $res['frequency'] }}x
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-emerald-700 whitespace-nowrap">
                                        Rp {{ number_format($res['total_revenue'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-semibold text-amber-700 whitespace-nowrap">
                                        {{ number_format($res['raw_lemon_kg'], 1, ',', '.') }} Kg
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

            <!-- Tab 2: 2D Scatter Plot -->
            <div x-show="activeTab === 'chart'" class="space-y-4" style="display: none;">
                <div>
                    <h5 class="text-sm font-bold text-slate-900">Visualisasi Sebaran Klaster 2D</h5>
                    <p class="text-xs text-slate-500">Sumbu X: Total Kuantitas Terjual (Unit) vs Sumbu Y: Total Omset Penjualan (Rp)</p>
                </div>
                <div class="h-96 w-full relative">
                    <canvas id="scatterChart"></canvas>
                </div>
            </div>

            <!-- Tab 3: Step-by-Step Mathematical Computation Log -->
            <div x-show="activeTab === 'math'" class="space-y-6" style="display: none;">
                
                <!-- Math Sub-Section: Min-Max Normalization Table -->
                <div>
                    <h5 class="text-sm font-bold text-slate-900 mb-1">1. Normalisasi Data Min-Max [0, 1]</h5>
                    <p class="text-xs text-slate-500 mb-3">Formula: $x' = \frac{x - \min(x)}{\max(x) - \min(x)}$ untuk menyeimbangkan rentang kuantitas, omset, dan kebutuhan lemon segar.</p>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Produk</th>
                                    <th class="py-2.5 px-3 text-right">X1 (Qty)</th>
                                    <th class="py-2.5 px-3 text-right">X2 (Freq)</th>
                                    <th class="py-2.5 px-3 text-right">X3 (Omset)</th>
                                    <th class="py-2.5 px-3 text-right">X4 (Lemon Kg)</th>
                                    <th class="py-2.5 px-3 text-center font-bold">Vektor Ternormalisasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                                @foreach($clusteringOutput['results'] as $r)
                                    <tr>
                                        <td class="py-2 px-3 font-sans font-semibold text-slate-800">{{ $r['product_name'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-600">{{ $r['total_qty'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-600">{{ $r['frequency'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-600">{{ number_format($r['total_revenue'], 0, ',', '.') }}</td>
                                        <td class="py-2 px-3 text-right text-slate-600">{{ $r['raw_lemon_kg'] }}</td>
                                        <td class="py-2 px-3 text-center text-emerald-700 font-bold">
                                            [{{ implode(', ', array_values($r['normalized_vector'])) }}]
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Math Sub-Section: Centroid Table -->
                <div class="pt-4 border-t border-slate-200">
                    <h5 class="text-sm font-bold text-slate-900 mb-1">2. Posisi Centroid Awal vs Centroid Akhir</h5>
                    <p class="text-xs text-slate-500 mb-3">Koordinat titik pusat klaster setelah konvergensi perhitungan Euclidean Distance.</p>
                    
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

                <!-- Math Sub-Section: Iteration History -->
                <div class="pt-4 border-t border-slate-200">
                    <h5 class="text-sm font-bold text-slate-900 mb-1">3. Riwayat Pergeseran Iterasi</h5>
                    <p class="text-xs text-slate-500 mb-3">Jumlah anggota pada setiap klaster di tiap putaran iterasi.</p>
                    
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
                                                <span class="px-2 py-0.5 rounded bg-slate-100 font-bold mr-2">C{{ $cI + 1 }}: {{ $cnt }} item</span>
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
        <div x-show="saveModal" 
             x-transition 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
             style="display: none;">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4" @click.outside="saveModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-base font-bold text-slate-900">Simpan Analisis ke Riwayat</h4>
                    <button @click="saveModal = false" class="text-slate-400 hover:text-slate-600">
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
                        <input type="text" 
                               name="title" 
                               value="Analisis Segmentasi Penjualan ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})" 
                               required 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan / Keterangan Analisis</label>
                        <textarea name="notes" rows="3" placeholder="Tujuan pengujian atau catatan khusus evaluasi stok lemon..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="saveModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20">
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
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Pilih parameter dan klik tombol "Jalankan K-Means" di atas untuk memproses data penjualan produk olahan lemon.</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
@if($clusteringOutput)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const scatterCanvas = document.getElementById('scatterChart');
        if (scatterCanvas) {
            const results = @json($clusteringOutput['results']);
            
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
@endif
@endpush
