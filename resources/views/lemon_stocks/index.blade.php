@extends('layouts.app')

@section('title', 'Keseimbangan Stok Lemon Segar')
@section('page_title', 'Stok Bahan Baku Lemon Segar')
@section('page_subtitle', 'Monitoring fluktuasi persediaan lemon segar untuk mengatasi overstock dan stockout (Studi Kasus UMKM Elmas Fresh)')

@section('content')
<div class="space-y-8" x-data="{ createModal: false }">

    <!-- Thesis Case Problem Context Card -->
    <div class="bg-gradient-to-r from-amber-500/15 via-emerald-500/10 to-teal-500/15 rounded-3xl p-6 sm:p-8 border border-amber-300/40 shadow-xs">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-400 text-slate-950 font-black text-2xl flex items-center justify-center shrink-0 shadow-md">
                🍋
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Identifikasi Masalah Persediaan Bahan Baku Buah Lemon</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Data pada UMKM Elmas Fresh (Jan 2025 - Apr 2026) menunjukkan ketidakseimbangan ekstrem antara pasokan buah dan serapan olahan: <strong>overstock</strong> hingga 4.500 Kg menyebabkan pembusukan buah dan kerugian finansial, sementara <strong>stockout</strong> hingga 1.000 Kg menghentikan produksi produk laris. Segmentasi K-Means memitigasi risiko ini melalui perencanaan berbasis data.
                </p>
            </div>
        </div>
    </div>

    <!-- 3 Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-3xl p-6 border border-rose-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Akumulasi Overstock (Kelebihan)</span>
                <h4 class="text-2xl font-black text-rose-700 mt-1">{{ number_format($totalOverstockKg, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Kg</span></h4>
                <p class="text-[11px] text-slate-500 mt-1">Potensi kerugian pembusukan lemon</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-amber-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">Akumulasi Stockout (Kekurangan)</span>
                <h4 class="text-2xl font-black text-amber-700 mt-1">{{ number_format($totalStockoutKg, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Kg</span></h4>
                <p class="text-[11px] text-slate-500 mt-1">Produksi terhenti & kehilangan omset</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-emerald-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Periode Seimbang (Ideal)</span>
                <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ $balancedMonths }} <span class="text-xs font-semibold text-slate-400">Bulan</span></h4>
                <p class="text-[11px] text-slate-500 mt-1">Stok sesuai kebutuhan produksi aktual</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-base font-bold text-slate-900">Tabel Kondisi Persediaan Lemon Segar Per Periode</h4>
                <p class="text-xs text-slate-500">Rekam jejak fluktuasi bahan baku UMKM Elmas Fresh</p>
            </div>

            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Catatan Periode</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-5">Periode Bulan</th>
                        <th class="py-3.5 px-5 text-center">Status Kondisi</th>
                        <th class="py-3.5 px-5 text-right">Jumlah Fluktuasi (Kg)</th>
                        <th class="py-3.5 px-5">Keterangan / Dampak Operasional</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stocks as $s)
                        @php
                            $badge = $s->status == 'Kelebihan' ? 'bg-rose-100 text-rose-800' : ($s->status == 'Kekurangan' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 font-bold text-slate-900">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $s->period_month)->translatedFormat('F Y') }}
                                <span class="text-[10px] text-slate-400 block font-mono">({{ $s->period_month }})</span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-black {{ $badge }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right font-black text-slate-900 text-sm">
                                {{ number_format($s->quantity_kg, 0, ',', '.') }} Kg
                            </td>
                            <td class="py-4 px-5 text-slate-600 max-w-sm">
                                {{ $s->notes ?: '-' }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <form method="POST" action="{{ route('lemon-stocks.destroy', $s) }}" onsubmit="return confirm('Hapus catatan ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">Belum ada data catatan persediaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>

    <!-- Create Stock Modal -->
    <div x-show="createModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-200 space-y-4" @click.outside="createModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-base font-bold text-slate-900">Tambah Catatan Stok Lemon</h4>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('lemon-stocks.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Periode Bulan (Format YYYY-MM) <span class="text-rose-500">*</span></label>
                    <input type="month" name="period_month" required value="{{ date('Y-m') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kondisi Persediaan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                        <option value="Kelebihan">Kelebihan (Overstock)</option>
                        <option value="Kekurangan">Kekurangan (Stockout / Defisit)</option>
                        <option value="Seimbang">Seimbang (Ideal)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Bobot (Kg) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.1" name="quantity_kg" required value="0" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Operasional</label>
                    <textarea name="notes" rows="2" placeholder="Penyebab fluktuasi atau dampak terhadap produksi..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
