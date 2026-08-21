@extends('layouts.app')

@section('title', 'Kategori Produk Olahan Lemon')
@section('page_title', 'Kategori Produk Olahan')
@section('page_subtitle', 'Klasifikasi kelompok produk olahan lemon UMKM Elmas Fresh')

@section('content')
<div class="space-y-6" x-data="{ editModal: false, editId: null, editName: '', editDesc: '' }">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 1 Col: Create New Category Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs h-fit">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="tag" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900">Tambah Kategori</h4>
                    <p class="text-xs text-slate-500">Buat klasifikasi kelompok baru</p>
                </div>
            </div>

            <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required 
                           placeholder="Contoh: Makanan & Selai Lemon"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('name') border-rose-500 @enderror">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi / Keterangan</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Deskripsi singkat kategori..."
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-emerald-600/20 flex items-center justify-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Simpan Kategori</span>
                </button>
            </form>
        </div>

        <!-- Right 2 Cols: Category List Table -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h4 class="text-base font-bold text-slate-900">Daftar Kategori Terdaftar</h4>
                <p class="text-xs text-slate-500">Semua kategori produk olahan lemon</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-100 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-5">Nama Kategori</th>
                            <th class="py-3.5 px-5">Deskripsi</th>
                            <th class="py-3.5 px-5 text-center">Jumlah Produk</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5 font-bold text-slate-900">
                                    {{ $cat->name }}
                                </td>
                                <td class="py-4 px-5 text-slate-600 max-w-xs">
                                    {{ $cat->description ?: '-' }}
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold">
                                        {{ $cat->products_count }} Produk
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button @click="editModal = true; editId = {{ $cat->id }}; editName = '{{ addslashes($cat->name) }}'; editDesc = '{{ addslashes($cat->description) }}'" 
                                                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        @if($cat->products_count == 0)
                                            <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Belum ada kategori terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Edit Category Modal -->
    <div x-show="editModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-200 space-y-4" @click.outside="editModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-base font-bold text-slate-900">Edit Kategori</h4>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('categories') }}/' + editId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Kategori</label>
                    <input type="text" name="name" x-model="editName" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" x-model="editDesc" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
