@extends('layouts.app')

@section('title', 'Import Transaksi')
@section('page_title', 'Import Data Penjualan')
@section('page_subtitle', 'Upload file Excel untuk mengimpor data Dried Lemoen, Manisan Lemon, dan Sari Lemon')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Back link -->
    <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-emerald-700 transition">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Kembali ke Transaksi</span>
    </a>

    <!-- Validation errors -->
    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3 text-rose-800 shadow-xs">
            <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 text-sm font-medium">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Import card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-br from-emerald-50/60 to-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Import dari File Excel</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Format .xlsx dengan 3 sheet produk</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-5">

            <!-- Sheet checklist -->
            <div class="grid sm:grid-cols-3 gap-3">
                <div class="flex items-center gap-2 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span class="text-xs font-semibold text-slate-700">Dried Lemoen</span>
                </div>
                <div class="flex items-center gap-2 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span class="text-xs font-semibold text-slate-700">Manisan Lemon</span>
                </div>
                <div class="flex items-center gap-2 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span class="text-xs font-semibold text-slate-700">Sari Lemon</span>
                </div>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Setiap sheet wajib memiliki kolom
                <code class="px-1 py-0.5 bg-slate-100 rounded text-[11px] font-mono text-slate-700">TANGGAL</code>,
                <code class="px-1 py-0.5 bg-slate-100 rounded text-[11px] font-mono text-slate-700">MASUK P.O</code>,
                <code class="px-1 py-0.5 bg-slate-100 rounded text-[11px] font-mono text-slate-700">KIRIM</code>, dan
                <code class="px-1 py-0.5 bg-slate-100 rounded text-[11px] font-mono text-slate-700">SISA</code>.
            </p>

            <!-- Info box -->
            <div class="p-4 bg-sky-50 border border-sky-200 rounded-xl flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="info" class="w-4 h-4"></i>
                </div>
                <p class="text-xs text-sky-800 leading-relaxed">
                    Sistem otomatis membuat produk baru bila belum ada, menggabungkan data ketiga produk per tanggal
                    menjadi satu transaksi harian, dan memperbarui stok dari nilai SISA terakhir. Proses ini aman
                    dijalankan berulang — tanggal yang sudah diimpor tidak akan digandakan.
                </p>
            </div>

            <form action="{{ route('transactions.import.store') }}" method="POST" enctype="multipart/form-data"
                  class="space-y-4" x-data="{ fileName: null }">
                @csrf

                <div>
                    <label for="file" class="block text-xs font-bold text-slate-700 mb-2">File Excel</label>

                    <label for="file"
                           class="flex flex-col items-center justify-center gap-2 px-6 py-8 border-2 border-dashed border-slate-300 hover:border-emerald-400 hover:bg-emerald-50/40 rounded-2xl cursor-pointer transition group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-emerald-100 text-slate-500 group-hover:text-emerald-600 flex items-center justify-center transition">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-slate-700" x-text="fileName || 'Klik untuk pilih file .xlsx / .xls'"></p>
                            <p class="text-xs text-slate-400 mt-0.5">Maksimal ukuran sesuai konfigurasi server</p>
                        </div>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls" required
                               class="hidden" @change="fileName = $event.target.files[0]?.name">
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('transactions.index') }}"
                       class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Import Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection