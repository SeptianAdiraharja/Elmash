@extends('layouts.app')

@section('title', 'Komparasi Analisis Antarperiode')
@section('page_title', 'Komparasi Antarperiode')
@section('page_subtitle', 'Bandingkan pergeseran klaster produk olahan lemon dan pergerakan penjualan antar rentang waktu')

@section('content')
<div class="space-y-8">

    <!-- Comparison Selection Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="git-compare" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-900">Pilih 2 Sesi Analisis untuk Dibandingkan</h4>
                <p class="text-xs text-slate-500">Pilih Periode Dasar (A) dan Periode Pembanding (B)</p>
            </div>
        </div>

        <form method="GET" action="{{ route('clustering.compare') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
            
            <!-- Analysis A -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Periode A (Basis / Masa Lalu) <span class="text-rose-500">*</span></label>
                <select name="analysis_a" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Sesi A --</option>
                    @foreach($allAnalyses as $an)
                        <option value="{{ $an->id }}" {{ $analysisIdA == $an->id ? 'selected' : '' }}>
                            {{ $an->title }} ({{ $an->period_start->format('d/m/Y') }} - {{ $an->period_end->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Analysis B -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Periode B (Pembanding / Terbaru) <span class="text-rose-500">*</span></label>
                <select name="analysis_b" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Sesi B --</option>
                    @foreach($allAnalyses as $an)
                        <option value="{{ $an->id }}" {{ $analysisIdB == $an->id ? 'selected' : '' }}>
                            {{ $an->title }} ({{ $an->period_start->format('d/m/Y') }} - {{ $an->period_end->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Compare Submit -->
            <div>
                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Bandingkan</span>
                </button>
            </div>

        </form>
    </div>

    @if($analysisA && $analysisB)
        
        <!-- Comparison Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @php
                $naikCount = collect($comparison)->where('trend', 'naik')->count();
                $turunCount = collect($comparison)->where('trend', 'turun')->count();
                $tetapCount = collect($comparison)->where('trend', 'tetap')->count();
            @endphp

            <div class="bg-white rounded-3xl p-6 border border-emerald-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Performa Meningkat 🚀</span>
                    <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ $naikCount }} <span class="text-xs font-semibold text-slate-500">Produk</span></h4>
                    <p class="text-[11px] text-slate-500 mt-1">Naik ke klaster yang lebih tinggi</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Posisi Stabil ⏸</span>
                    <h4 class="text-2xl font-black text-slate-800 mt-1">{{ $tetapCount }} <span class="text-xs font-semibold text-slate-500">Produk</span></h4>
                    <p class="text-[11px] text-slate-500 mt-1">Klaster tetap tidak berubah</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center">
                    <i data-lucide="minus" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-rose-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Performa Menurun 🔻</span>
                    <h4 class="text-2xl font-black text-rose-700 mt-1">{{ $turunCount }} <span class="text-xs font-semibold text-slate-500">Produk</span></h4>
                    <p class="text-[11px] text-slate-500 mt-1">Turun ke klaster lebih rendah</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="trending-down" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Comparison Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h4 class="text-base font-bold text-slate-900">Tabel Perbandingan Klaster & Pergeseran Penjualan</h4>
                    <p class="text-xs text-slate-500">
                        Membandingkan <strong>{{ $analysisA->title }}</strong> vs <strong>{{ $analysisB->title }}</strong>
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">Produk Olahan Lemon</th>
                            <th class="py-3.5 px-4 text-center">Klaster (A)</th>
                            <th class="py-3.5 px-4 text-center">Klaster (B)</th>
                            <th class="py-3.5 px-4 text-center">Status Trajektori</th>
                            <th class="py-3.5 px-4 text-right">Qty (A)</th>
                            <th class="py-3.5 px-4 text-right">Qty (B)</th>
                            <th class="py-3.5 px-4 text-right">Selisih Qty</th>
                            <th class="py-3.5 px-4 text-right">Selisih Omset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($comparison as $row)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $row['product_name'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $row['product_code'] }}</span>
                                </td>
                                
                                <!-- Cluster A -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $row['cluster_a'] == 'C1' ? 'bg-emerald-100 text-emerald-800' : ($row['cluster_a'] == 'C2' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $row['cluster_a'] }}
                                    </span>
                                </td>

                                <!-- Cluster B -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $row['cluster_b'] == 'C1' ? 'bg-emerald-100 text-emerald-800' : ($row['cluster_b'] == 'C2' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $row['cluster_b'] }}
                                    </span>
                                </td>

                                <!-- Trend -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($row['trend'] == 'naik')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                            <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                            Naik Kelas
                                        </span>
                                    @elseif($row['trend'] == 'turun')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">
                                            <i data-lucide="arrow-down-right" class="w-3 h-3"></i>
                                            Turun Kelas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">
                                            <i data-lucide="minus" class="w-3 h-3"></i>
                                            Stabil
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-right font-medium text-slate-600">
                                    {{ number_format($row['qty_a'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-medium text-slate-900">
                                    {{ number_format($row['qty_b'], 0, ',', '.') }}
                                </td>
                                
                                <!-- Qty Diff -->
                                <td class="py-3.5 px-4 text-right font-extrabold whitespace-nowrap {{ $row['qty_diff'] > 0 ? 'text-emerald-600' : ($row['qty_diff'] < 0 ? 'text-rose-600' : 'text-slate-600') }}">
                                    {{ $row['qty_diff'] > 0 ? '+' : '' }}{{ number_format($row['qty_diff'], 0, ',', '.') }}
                                </td>

                                <!-- Revenue Diff -->
                                <td class="py-3.5 px-4 text-right font-extrabold whitespace-nowrap {{ $row['rev_diff'] > 0 ? 'text-emerald-600' : ($row['rev_diff'] < 0 ? 'text-rose-600' : 'text-slate-600') }}">
                                    {{ $row['rev_diff'] > 0 ? '+' : '' }}Rp {{ number_format($row['rev_diff'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center text-slate-400">
            <i data-lucide="git-compare" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h4 class="text-base font-bold text-slate-800">Pilih 2 Sesi Analisis</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Pilih sesi analisis A dan B pada formulir di atas untuk memulai evaluasi perbandingan trajektori penjualan.</p>
        </div>
    @endif

</div>
@endsection
