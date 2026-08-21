@extends('layouts.app')

@section('title', 'Edit Transaksi - ' . $transaction->invoice_number)
@section('page_title', 'Edit Transaksi Penjualan')
@section('page_subtitle', 'Perbarui data status dan informasi transaksi ' . $transaction->invoice_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('transactions.show', $transaction) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Faktur</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
        <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No Faktur</label>
                    <input type="text" value="{{ $transaction->invoice_number }}" disabled class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                    <input type="date" 
                           name="transaction_date" 
                           value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Pelanggan <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           name="customer_name" 
                           value="{{ old('customer_name', $transaction->customer_name) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. Telepon</label>
                    <input type="text" 
                           name="customer_phone" 
                           value="{{ old('customer_phone', $transaction->customer_phone) }}" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Saluran Penjualan <span class="text-rose-500">*</span></label>
                    <select name="sales_channel" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach(['Toko Offline', 'WhatsApp Order', 'Reseller / Distributor', 'Konsinyasi Kafe', 'Shopee / Marketplace'] as $ch)
                            <option value="{{ $ch }}" {{ old('sales_channel', $transaction->sales_channel) == $ch ? 'selected' : '' }}>{{ $ch }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach(['Cash / Tunai', 'Transfer BCA', 'Transfer BRI', 'QRIS', 'Tempo / Kredit'] as $pm)
                            <option value="{{ $pm }}" {{ old('payment_method', $transaction->payment_method) == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Status Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach(['Lunas', 'Menunggu Pembayaran', 'Dibatalkan'] as $ps)
                            <option value="{{ $ps }}" {{ old('payment_status', $transaction->payment_status) == $ps ? 'selected' : '' }}>{{ $ps }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan</label>
                    <input type="text" 
                           name="notes" 
                           value="{{ old('notes', $transaction->notes) }}" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('transactions.show', $transaction) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
