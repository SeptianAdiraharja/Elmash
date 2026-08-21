@extends('layouts.app')

@section('title', 'Detail Produk - ' . $product->name)
@section('page_title', 'Detail Produk Olahan Lemon')
@section('page_subtitle', 'Informasi spesifikasi, riwayat penjualan, dan klasifikasi klaster')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Master Produk</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-xl transition border border-amber-200">
                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                <span>Edit Produk</span>
            </a>
        </div>
    </div>

    <!-- Product Dossier Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Main Info -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold font-mono">
                        {{ $product->code }}
                    </span>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $product->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Kategori: <strong class="text-slate-700">{{ $product->category ? $product->category->name : '-' }}</strong> | Kemasan: <strong class="text-slate-700">{{ $product->unit }}</strong></p>
                </div>

                <div>
                    @if($product->is_active)
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Produk Aktif</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">Non-Aktif</span>
                    @endif
                </div>
            </div>

            @if($product->description)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600 leading-relaxed">
                    <strong class="text-slate-800 block mb-1">Deskripsi Produk:</strong>
                    {{ $product->description }}
                </div>
            @endif

            <!-- Pricing & Lemon Requirement Metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-slate-100">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Harga Pokok (HPP)</span>
                    <span class="text-base font-extrabold text-slate-800 mt-1 block">{{ $product->formatted_cost }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                    <span class="text-[11px] text-emerald-700 font-bold uppercase tracking-wider block">Harga Jual</span>
                    <span class="text-base font-black text-emerald-800 mt-1 block">{{ $product->formatted_price }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Margin Keuntungan</span>
                    <span class="text-base font-extrabold text-slate-800 mt-1 block">+{{ $product->profit_margin_percent }}%</span>
                </div>

                <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100">
                    <span class="text-[11px] text-amber-700 font-bold uppercase tracking-wider block">Kebutuhan Lemon</span>
                    <span class="text-base font-extrabold text-amber-800 mt-1 block">{{ number_format($product->raw_lemon_requirement, 3) }} Kg</span>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Performance & Stock Widget -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            <h4 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Ringkasan Penjualan</h4>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Stok Saat Ini</span>
                    <span class="text-sm font-extrabold text-slate-900">{{ $product->stock }} {{ $product->unit }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Total Akumulasi Terjual</span>
                    <span class="text-sm font-extrabold text-emerald-700">{{ number_format($totalUnitsSold, 0, ',', '.') }} Unit</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Total Omset Diperoleh</span>
                    <span class="text-sm font-extrabold text-emerald-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">Lemon Segar Terpakai</span>
                    <span class="text-sm font-extrabold text-amber-700">{{ number_format($totalRawLemonUsed, 1, ',', '.') }} Kg</span>
                </div>
            </div>

            <!-- Historical Cluster Badges -->
            <div class="pt-4 border-t border-slate-100">
                <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Riwayat Klasifikasi Klaster</h5>
                <div class="space-y-2">
                    @forelse($product->clusteringResults->take(3) as $cr)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs flex items-center justify-between">
                            <span class="font-medium text-slate-600 truncate max-w-[150px]">{{ $cr->analysis ? $cr->analysis->title : 'Analisis' }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $cr->cluster_code == 'C1' ? 'bg-emerald-100 text-emerald-800' : ($cr->cluster_code == 'C2' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ $cr->cluster_code }} - {{ $cr->cluster_label }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada riwayat klaster.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Transactions Table for this product -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
        <h4 class="text-base font-bold text-slate-900 mb-4">10 Transaksi Terakhir yang Memuat Produk Ini</h4>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-100 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No Faktur</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4 text-right">Lemon Digunakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSales as $sale)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono font-bold text-emerald-700">
                                @if($sale->transaction)
                                    <a href="{{ route('transactions.show', $sale->transaction) }}" class="hover:underline">
                                        {{ $sale->transaction->invoice_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $sale->transaction ? $sale->transaction->transaction_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-800 font-medium">
                                {{ $sale->transaction ? $sale->transaction->customer_name : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-900">
                                {{ $sale->quantity }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700">
                                Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-amber-700">
                                {{ number_format($sale->raw_lemon_used, 2) }} Kg
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada transaksi penjualan untuk produk ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
