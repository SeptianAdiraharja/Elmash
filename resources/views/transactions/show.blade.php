@extends('layouts.app')

@section('title', 'Faktur Penjualan - ' . $transaction->invoice_number)
@section('page_title', 'Faktur Penjualan')
@section('page_subtitle', 'Detail invoice transaksi dan rekapitulasi pembelian')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Action Bar (no print) -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Transaksi</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.edit', $transaction) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-xl transition border border-amber-200">
                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                <span>Edit Metadata</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Cetak Nota</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Invoice Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 shadow-sm space-y-8 print:shadow-none print:border-none print:p-0">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 pb-6 border-b border-slate-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 via-yellow-400 to-emerald-400 text-slate-950 font-black text-2xl flex items-center justify-center shadow-md">
                    🍋
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">UMKM ELMAS FRESH</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kp. Sirnagalih RT.04/RW.02, Kec. Sukalarang, Kab. Sukabumi</p>
                    <p class="text-xs text-slate-400">Telp: 0812-8899-7711 | Email: info@elmasfresh.id</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 block">FAKTUR PENJUALAN</span>
                <h3 class="text-xl font-black font-mono text-slate-900 mt-0.5">{{ $transaction->invoice_number }}</h3>
                <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold {{ $transaction->payment_status == 'Lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    STATUS: {{ strtoupper($transaction->payment_status) }}
                </span>
            </div>
        </div>

        <!-- Customer & Date Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-bold tracking-wider block mb-1">Kepada Pelanggan:</span>
                <h4 class="text-sm font-bold text-slate-900">{{ $transaction->customer_name }}</h4>
                @if($transaction->customer_phone)
                    <p class="text-slate-600 font-mono mt-0.5">{{ $transaction->customer_phone }}</p>
                @endif
            </div>

            <div>
                <span class="text-slate-400 uppercase font-bold tracking-wider block mb-1">Detail Transaksi:</span>
                <p class="text-slate-700">Tanggal: <strong class="text-slate-900">{{ $transaction->transaction_date->format('d F Y') }}</strong></p>
                <p class="text-slate-700">Saluran: <strong class="text-slate-900">{{ $transaction->sales_channel }}</strong></p>
                <p class="text-slate-700">Metode Bayar: <strong class="text-slate-900">{{ $transaction->payment_method }}</strong></p>
            </div>

            <div>
                <span class="text-slate-400 uppercase font-bold tracking-wider block mb-1">Petugas Kasir / Admin:</span>
                <p class="text-slate-900 font-semibold">{{ $transaction->user ? $transaction->user->name : 'Administrator' }}</p>
                @if($transaction->notes)
                    <p class="text-slate-500 italic mt-1">Catatan: {{ $transaction->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-700 border-b border-slate-200 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">Kode SKU</th>
                        <th class="py-3 px-4">Nama Produk Olahan Lemon</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Harga Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($transaction->items as $idx => $it)
                        <tr>
                            <td class="py-3.5 px-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $it->product_code }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ $it->product_name }}
                                <span class="block text-[10px] text-amber-700 font-normal">
                                    Serapan lemon segar: {{ number_format($it->raw_lemon_used, 2) }} Kg
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-900">{{ $it->quantity }}</td>
                            <td class="py-3.5 px-4 text-right text-slate-700">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Calculation & Lemon Footprint -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-200">
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 text-xs">
                <span class="font-bold text-amber-800 uppercase tracking-wider block mb-1">🍋 Rekapitulasi Bahan Baku Lemon:</span>
                @php $totalRawLemon = $transaction->items->sum('raw_lemon_used'); @endphp
                <p class="text-slate-700">
                    Transaksi ini menyerap estimasi <strong>{{ number_format($totalRawLemon, 2) }} Kg</strong> buah lemon segar hasil perkebunan lokal Sukabumi.
                </p>
            </div>

            <div class="space-y-2 text-xs text-right">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-900">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($transaction->discount > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Diskon / Potongan:</span>
                        <span class="font-semibold text-rose-600">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Pembayaran:</span>
                    <span class="text-emerald-700">{{ $transaction->formatted_total }}</span>
                </div>
            </div>
        </div>

        <!-- Signatures & Thank you -->
        <div class="pt-8 border-t border-slate-200 flex items-end justify-between text-xs text-slate-500">
            <div>
                <p class="font-bold text-slate-800">Terima kasih atas kerja sama Anda!</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Barang yang sudah dibeli telah dicek dalam kondisi prima & higienis.</p>
            </div>
            <div class="text-center w-40">
                <p class="mb-12 font-medium">Hormat Kami,</p>
                <p class="font-bold text-slate-900 underline">{{ $transaction->user ? $transaction->user->name : 'Admin Elmas Fresh' }}</p>
                <span class="text-[10px] text-slate-400">UMKM Elmas Fresh</span>
            </div>
        </div>

    </div>

</div>
@endsection
