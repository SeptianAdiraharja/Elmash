<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | Elmas Fresh - K-Means Clustering</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shrink-0 shadow-xl lg:shadow-none border-r border-slate-800">

            <!-- Brand Header -->
            <div class="h-20 flex items-center px-6 gap-3.5 border-b border-slate-800/80 bg-slate-950/40">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-amber-500 via-yellow-400 to-emerald-400 flex items-center justify-center shadow-lg shadow-amber-500/20 text-slate-950 font-black text-xl">
                    🍋
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-white flex items-center gap-1.5">
                        ELMAS FRESH
                        <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Admin</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium">Sistem Segmentasi Penjualan</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-7">

                <!-- Main Menu -->
                <div>
                    <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Utama</div>
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard</span>
                        </a>
                    </nav>
                </div>

                <!-- Master Data -->
                <div>
                    <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Manajemen Data</div>
                    <nav class="space-y-1">
                        <a href="{{ route('categories.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <span>Kategori Produk</span>
                        </a>
                        <a href="{{ route('products.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="package" class="w-4 h-4"></i>
                            <span>Data Master Produk</span>
                        </a>
                        <a href="{{ route('transactions.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('transactions.*') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            <span>Transaksi Penjualan</span>
                        </a>
                        <a href="{{ route('lemon-stocks.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('lemon-stocks.*') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="scale" class="w-4 h-4"></i>
                            <span>Stok Lemon Segar</span>
                        </a>
                    </nav>
                </div>

                <!-- K-Means Data Mining -->
                <div>
                    <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Analisis & Data Mining</div>
                    <nav class="space-y-1">
                        <a href="{{ route('clustering.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('clustering.index') ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Proses Clustering K-Means</span>
                        </a>
                        <a href="{{ route('clustering.history') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('clustering.history') || request()->routeIs('clustering.show') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="history" class="w-4 h-4"></i>
                            <span>Riwayat Analisis</span>
                        </a>
                        <a href="{{ route('clustering.compare') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('clustering.compare') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="git-compare" class="w-4 h-4"></i>
                            <span>Komparasi Antarperiode</span>
                        </a>
                    </nav>
                </div>

                <!-- Account -->
                <div>
                    <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pengaturan</div>
                    <nav class="space-y-1">
                        <a href="{{ route('profile') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="user-cog" class="w-4 h-4"></i>
                            <span>Manajemen Akun</span>
                        </a>
                    </nav>
                </div>

            </div>

            <!-- User Info Footer -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/60 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-emerald-600/30 border border-emerald-500/40 text-emerald-400 font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email ?? 'admin@elmasfresh.id' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800/80 rounded-lg transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Topbar Header -->
            <header class="h-20 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">

                <!-- Left: Mobile Trigger & Page Title -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard')</h2>
                        <p class="text-xs text-slate-500 hidden sm:block">@yield('page_subtitle', 'UMKM Elmas Fresh - Sukabumi')</p>
                    </div>
                </div>

                <!-- Right: Date & Quick Actions -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 bg-slate-100 rounded-xl text-xs font-medium text-slate-600 border border-slate-200">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-sm shadow-emerald-600/20">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Input Transaksi</span>
                    </a>

                    <!-- Account Quick link -->
                    <a href="{{ route('profile') }}" class="p-2 text-slate-600 hover:text-emerald-700 rounded-xl hover:bg-slate-100 transition" title="Pengaturan Akun">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                    </a>
                </div>
            </header>

            <!-- Main Page Body -->
            <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3 text-emerald-800 shadow-xs" x-data="{ show: true }" x-show="show">
                        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1 text-sm font-medium">
                            {{ session('success') }}
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3 text-rose-800 shadow-xs" x-data="{ show: true }" x-show="show">
                        <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1 text-sm font-medium">
                            {{ session('error') }}
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-6 p-4 bg-sky-50 border border-sky-200 rounded-2xl flex items-start gap-3 text-sky-800 shadow-xs" x-data="{ show: true }" x-show="show">
                        <div class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="info" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1 text-sm font-medium">
                            {{ session('info') }}
                        </div>
                        <button @click="show = false" class="text-sky-500 hover:text-sky-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @yield('content')

            </main>

            <!-- Footer -->
            <footer class="mt-auto border-t border-slate-200 bg-white py-4 px-6 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} <strong>UMKM Elmas Fresh</strong> Sukabumi. Sistem Segmentasi Penjualan Menggunakan Algoritma K-Means Clustering.</p>
            </footer>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
