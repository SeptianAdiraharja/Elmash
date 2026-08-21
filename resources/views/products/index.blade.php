@extends('layouts.app')

@section('title', 'Data Master Produk Olahan Lemon')
@section('page_title', 'Master Produk Olahan Lemon')
@section('page_subtitle', 'Manajemen katalog produk, harga, stok, dan spesifikasi kebutuhan bahan baku lemon')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        
        <!-- Search & Filter Bar -->
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[220px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari SKU, nama produk..." 
                       class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-2xs">
            </div>

            <!-- Category Filter -->
            <select name="category_id" class="px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition shadow-2xs">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs sm:text-sm font-semibold transition">
                Filter
            </button>

            @if($search || $categoryId || $status !== null)
                <a href="{{ route('products.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 hover:underline">
                    Reset
                </a>
            @endif
        </form>

        <!-- Add Button -->
        <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-sm shadow-emerald-600/20 shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Produk Baru</span>
        </a>
    </div>

    <!-- Products Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-200/80 font-bold uppercase tracking-wider">
                        <th class="py-4 px-5">Kode SKU</th>
                        <th class="py-4 px-5">Nama Produk</th>
                        <th class="py-4 px-5">Kategori</th>
                        <th class="py-4 px-5">Kebutuhan Lemon</th>
                        <th class="py-4 px-5 text-right">HPP</th>
                        <th class="py-4 px-5 text-right">Harga Jual</th>
                        <th class="py-4 px-5 text-center">Stok</th>
                        <th class="py-4 px-5 text-center">Status</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $prod)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 font-mono font-bold text-slate-700">
                                {{ $prod->code }}
                            </td>
                            <td class="py-4 px-5 font-semibold text-slate-900">
                                <a href="{{ route('products.show', $prod) }}" class="hover:text-emerald-600 transition">
                                    {{ $prod->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400 font-normal">{{ $prod->unit }}</span>
                            </td>
                            <td class="py-4 px-5 text-slate-600">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-medium">
                                    {{ $prod->category ? $prod->category->name : '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-5 font-semibold text-amber-700">
                                {{ number_format($prod->raw_lemon_requirement, 3) }} <span class="text-[10px] text-slate-500 font-normal">Kg/unit</span>
                            </td>
                            <td class="py-4 px-5 text-right text-slate-600 font-medium">
                                {{ $prod->formatted_cost }}
                            </td>
                            <td class="py-4 px-5 text-right font-bold text-emerald-700">
                                {{ $prod->formatted_price }}
                            </td>
                            <td class="py-4 px-5 text-center">
                                @if($prod->stock <= $prod->min_stock_alert)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[11px] font-bold">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        {{ $prod->stock }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                        {{ $prod->stock }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center">
                                @if($prod->is_active)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-semibold">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('products.show', $prod) }}" title="Lihat Detail" class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $prod) }}" title="Edit Produk" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $prod) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Produk" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i data-lucide="package-x" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                <p class="text-sm font-medium">Tidak ada data produk yang sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
