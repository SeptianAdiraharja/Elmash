@extends('layouts.app')

@section('title', 'Data Transaksi Penjualan')
@section('page_title', 'Transaksi Penjualan & PO')
@section('page_subtitle', 'Kelola catatan transaksi penjualan produk olahan lemon dan export laporan')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary for Current Filter -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Transaksi</span>
                <h4 class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalCount, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Nota</span></h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Omset Terfilter</span>
                <h4 class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Rata-rata per Nota</span>
                @php $avgPerTx = $totalCount > 0 ? $totalOmset / $totalCount : 0; @endphp
                <h4 class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($avgPerTx, 0, ',', '.') }}</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="calculator" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Actions Bar -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs space-y-4">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">

            <!-- Search -->
            <div class="lg:col-span-1">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Faktur / Pembeli</label>
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="No faktur, pelanggan..."
                           class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3"></i>
                </div>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date"
                       name="start_date"
                       value="{{ $startDate }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date"
                       name="end_date"
                       value="{{ $endDate }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Channel -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Saluran</label>
                <select name="channel" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Saluran</option>
                    @foreach($channels as $ch)
                        <option value="{{ $ch }}" {{ $channel == $ch ? 'selected' : '' }}>{{ $ch }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if($search || $startDate || $endDate || $channel || $status)
                    <a href="{{ route('transactions.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>

        <!-- Export & Add Quick Action Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
            <div class="flex items-center gap-2">
                <a href="{{ route('transactions.export.pdf', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition border border-rose-200">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Export PDF</span>
                </a>
                <a href="{{ route('transactions.export.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl transition border border-emerald-200">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Export Excel</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('transactions.import') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-200">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <span>Import Excel</span>
                </a>

                <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Catat Transaksi Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-200/80 font-bold uppercase tracking-wider">
                        <th class="py-4 px-5">No Faktur</th>
                        <th class="py-4 px-5">Tanggal</th>
                        <th class="py-4 px-5">Pelanggan</th>
                        <th class="py-4 px-5">Saluran</th>
                        <th class="py-4 px-5">Produk & Qty</th>
                        <th class="py-4 px-5">Pembayaran</th>
                        <th class="py-4 px-5 text-right">Total Transaksi</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 font-mono font-bold text-slate-800">
                                <a href="{{ route('transactions.show', $tx) }}" class="text-emerald-700 hover:underline">
                                    {{ $tx->invoice_number }}
                                </a>
                            </td>
                            <td class="py-4 px-5 text-slate-600 whitespace-nowrap">
                                {{ $tx->transaction_date->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-5 font-semibold text-slate-900 max-w-[160px] truncate">
                                {{ $tx->customer_name }}
                                @if($tx->customer_phone)
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $tx->customer_phone }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-slate-600">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-medium">
                                    {{ $tx->sales_channel }}
                                </span>
                            </td>
                            <td class="py-4 px-5 max-w-[220px]">
                                <div class="space-y-1">
                                    @foreach($tx->items->take(2) as $it)
                                        <div class="text-[11px] text-slate-700 truncate">
                                            &bull; {{ $it->product_name }} <strong class="text-slate-900">({{ $it->quantity }}x)</strong>
                                        </div>
                                    @endforeach
                                    @if($tx->items->count() > 2)
                                        <span class="text-[10px] text-slate-400 italic">+{{ $tx->items->count() - 2 }} item lainnya</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-5 text-slate-600 whitespace-nowrap">
                                <span class="font-medium text-slate-700 block">{{ $tx->payment_method }}</span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tx->payment_status == 'Lunas' ? 'bg-emerald-100 text-emerald-800' : ($tx->payment_status == 'Dibatalkan' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $tx->payment_status }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right font-black text-slate-900 whitespace-nowrap">
                                {{ $tx->formatted_total }}
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('transactions.show', $tx) }}" title="Lihat Faktur" class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('transactions.edit', $tx) }}" title="Edit Transaksi" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('transactions.destroy', $tx) }}" onsubmit="return confirm('Hapus transaksi ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Transaksi" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="receipt-text" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                <p class="text-sm font-medium">Tidak ada data transaksi yang sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
