@extends('layouts.app')

@section('title', 'Manajemen Akun & Profil')
@section('page_title', 'Manajemen Akun')
@section('page_subtitle', 'Kelola informasi profil dan pengaturan kata sandi administrator')

@section('content')
<div class="space-y-8">

    <!-- User Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-emerald-500/20 border-2 border-emerald-400/40 text-emerald-300 font-extrabold text-2xl flex items-center justify-center shadow-inner">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-xl font-bold tracking-tight">{{ $user->name }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500 text-slate-950">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <p class="text-sm text-slate-300 mt-1 flex items-center gap-2">
                    <i data-lucide="mail" class="w-4 h-4 text-emerald-400"></i>
                    <span>{{ $user->email }}</span>
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    Terdaftar sejak: {{ $user->created_at->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700 text-xs font-medium text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Akun Aktif
            </span>
        </div>
    </div>

    <!-- 2 Column Forms -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Form 1: Edit Profile Information -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900">Informasi Pribadi</h4>
                    <p class="text-xs text-slate-500">Perbarui nama dan alamat email akun Anda</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('name') border-rose-500 @enderror">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. Telepon / WhatsApp</label>
                    <input type="text" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $user->phone) }}" 
                           placeholder="0812-xxxx-xxxx"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Hak Akses / Role</label>
                    <input type="text" value="{{ ucfirst($user->role) }} (Akses Penuh)" disabled class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-emerald-600/20">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: Update Password -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900">Keamanan & Kata Sandi</h4>
                    <p class="text-xs text-slate-500">Ubah kata sandi untuk menjaga keamanan akun</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Saat Ini</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           required 
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition @error('current_password') border-rose-500 @enderror">
                    @error('current_password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Minimal 6 karakter"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition @error('password') border-rose-500 @enderror">
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required 
                           placeholder="Ulangi kata sandi baru"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-amber-600/20">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        <span>Perbarui Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
