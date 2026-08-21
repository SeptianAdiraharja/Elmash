@extends('layouts.app')

@section('title', 'Tambah Produk Baru')
@section('page_title', 'Tambah Produk Olahan Lemon')
@section('page_subtitle', 'Masukkan detail produk olahan lemon baru ke dalam sistem master data')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Produk</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                
                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kategori Produk <span class="text-rose-500">*</span></label>
                    <select id="category_id" name="category_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('category_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product SKU Code -->
                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kode SKU Produk <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           id="code" 
                           name="code" 
                           value="{{ old('code') }}" 
                           required 
                           placeholder="Contoh: ELM-SR250"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('code') border-rose-500 @enderror">
                    @error('code')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product Name -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           placeholder="Contoh: Sari Lemon Murni 250ml"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('name') border-rose-500 @enderror">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit & Raw Lemon Requirement -->
                <div>
                    <label for="unit" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Satuan Kemasan <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           id="unit" 
                           name="unit" 
                           value="{{ old('unit', 'Botol 250ml') }}" 
                           required 
                           placeholder="Contoh: Botol 250ml / Pouch / Jar"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <div>
                    <label for="raw_lemon_requirement" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kebutuhan Lemon Segar (Kg/Unit) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="number" 
                               step="0.001" 
                               id="raw_lemon_requirement" 
                               name="raw_lemon_requirement" 
                               value="{{ old('raw_lemon_requirement', '1.000') }}" 
                               required 
                               class="w-full pl-4 pr-12 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-xs font-bold text-slate-400">Kg</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Bobot buah lemon segar yang dibutuhkan untuk membuat 1 unit produk.</p>
                </div>

                <!-- Cost Price & Selling Price -->
                <div>
                    <label for="cost_price" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Harga Pokok / HPP (Rp) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" 
                               id="cost_price" 
                               name="cost_price" 
                               value="{{ old('cost_price', 0) }}" 
                               required 
                               class="w-full pl-12 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <div>
                    <label for="selling_price" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" 
                               id="selling_price" 
                               name="selling_price" 
                               value="{{ old('selling_price', 0) }}" 
                               required 
                               class="w-full pl-12 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-emerald-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <!-- Stock & Min Stock Alert -->
                <div>
                    <label for="stock" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Stok Awal (Unit) <span class="text-rose-500">*</span></label>
                    <input type="number" 
                           id="stock" 
                           name="stock" 
                           value="{{ old('stock', 0) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <div>
                    <label for="min_stock_alert" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Batas Minimum Peringatan Stok <span class="text-rose-500">*</span></label>
                    <input type="number" 
                           id="min_stock_alert" 
                           name="min_stock_alert" 
                           value="{{ old('min_stock_alert', 10) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Produk</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Keterangan kandungan, manfaat, atau spesifikasi kemasan..." 
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">{{ old('description') }}</textarea>
                </div>

                <!-- Is Active -->
                <div class="sm:col-span-2 flex items-center gap-3 pt-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm font-semibold text-slate-700 cursor-pointer">Status Produk Aktif (Dapat Dijual & Dianalisis)</label>
                </div>

            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Produk</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
