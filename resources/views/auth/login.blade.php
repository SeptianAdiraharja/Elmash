@extends('layouts.guest')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="w-full max-w-md" x-data="{ email: '{{ old('email', 'admin@elmasfresh.id') }}', password: 'admin123' }">
    
    <!-- Brand Box -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 via-yellow-400 to-emerald-400 text-slate-950 font-black text-3xl shadow-xl shadow-amber-500/20 mb-4 transform hover:scale-105 transition-transform duration-300">
            🍋
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-white">UMKM ELMAS FRESH</h1>
        <p class="text-sm text-emerald-400 font-medium mt-1">Sistem Segmentasi Penjualan K-Means</p>
        <p class="text-xs text-slate-400 mt-1">Kp. Sirnagalih, Sukalarang, Kabupaten Sukabumi</p>
    </div>

    <!-- Login Card -->
    <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-black/50">
        
        <div class="mb-6">
            <h2 class="text-lg font-bold text-white">Masuk Administrator</h2>
            <p class="text-xs text-slate-400 mt-0.5">Silakan masukkan kredensial akun untuk mengakses sistem.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl flex items-start gap-3 text-rose-300">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"></i>
                <div class="text-xs leading-relaxed">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-start gap-3 text-emerald-300">
                <i data-lucide="check" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5"></i>
                <div class="text-xs leading-relaxed">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Email Akun</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           x-model="email"
                           required 
                           autofocus
                           placeholder="admin@elmasfresh.id"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           x-model="password"
                           required 
                           placeholder="••••••••"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500/20">
                    <span class="text-xs text-slate-300">Ingat Saya</span>
                </label>
                <span class="text-xs text-emerald-400 hover:underline cursor-pointer" @click="email = 'admin@elmasfresh.id'; password = 'admin123'">
                    Isi Demo Akun
                </span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full mt-2 py-3 px-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition duration-200 flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Credentials Info Helper -->
        <div class="mt-6 pt-5 border-t border-slate-800 text-center">
            <p class="text-[11px] text-slate-400">
                Kredensial Default: <strong class="text-slate-200">admin@elmasfresh.id</strong> / <strong class="text-slate-200">admin123</strong>
            </p>
        </div>

    </div>
</div>
@endsection
