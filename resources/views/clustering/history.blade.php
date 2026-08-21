@extends('layouts.app')

@section('title', 'Riwayat Analisis K-Means')
@section('page_title', 'Riwayat Analisis Clustering')
@section('page_subtitle', 'Daftar catatan hasil segmentasi data penjualan produk olahan lemon yang telah disimpan')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('clustering.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs sm:text-sm rounded-xl transition shadow-sm shadow-amber-500/20">
            <i data-lucide="play" class="w-4 h-4 fill-slate-950"></i>
            <span>Buka Studio Clustering Baru</span>
        </a>

        <a href="{{ route('clustering.compare') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs sm:text-sm rounded-xl transition">
            <i data-lucide="git-compare" class="w-4 h-4"></i>
            <span>Komparasi Antarperiode</span>
        </a>
    </div>

    <!-- History Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                        <th class="py-4 px-5">Judul Sesi Analisis</th>
                        <th class="py-4 px-5">Rentang Periode Data</th>
                        <th class="py-4 px-5 text-center">Parameter (k)</th>
                        <th class="py-4 px-5 text-center">Iterasi</th>
                        <th class="py-4 px-5 text-right">Nilai SSE</th>
                        <th class="py-4 px-5 text-right">Davies-Bouldin</th>
                        <th class="py-4 px-5">Dieksekusi Oleh</th>
                        <th class="py-4 px-5 text-right">Aksi & Export</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($analyses as $an)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 font-bold text-slate-900">
                                <a href="{{ route('clustering.show', $an) }}" class="text-emerald-700 hover:underline">
                                    {{ $an->title }}
                                </a>
                                @if($an->notes)
                                    <span class="block text-[10px] text-slate-400 font-normal truncate max-w-xs">{{ $an->notes }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-slate-700 font-medium whitespace-nowrap">
                                {{ $an->period_start->format('d/m/Y') }} s/d {{ $an->period_end->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-black text-xs">
                                    k = {{ $an->k_value }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center font-semibold text-slate-700">
                                {{ $an->iterations_count }} iterasi
                                <span class="block text-[10px] text-emerald-600 font-bold">Konvergen</span>
                            </td>
                            <td class="py-4 px-5 text-right font-mono text-slate-700">
                                {{ $an->sse_inertia }}
                            </td>
                            <td class="py-4 px-5 text-right font-mono text-slate-700">
                                {{ $an->davies_bouldin_index }}
                            </td>
                            <td class="py-4 px-5 text-slate-600">
                                {{ $an->user ? $an->user->name : 'Admin' }}
                                <span class="block text-[10px] text-slate-400">{{ $an->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('clustering.show', $an) }}" title="Lihat Hasil Lengkap" class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('clustering.export.pdf', $an) }}" title="Download Laporan PDF" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('clustering.export.excel', $an) }}" title="Download Laporan Excel" class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('clustering.destroy', $an) }}" onsubmit="return confirm('Hapus riwayat analisis ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Riwayat" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="history" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                <p class="text-sm font-medium">Belum ada riwayat analisis clustering tersimpan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($analyses->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $analyses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
