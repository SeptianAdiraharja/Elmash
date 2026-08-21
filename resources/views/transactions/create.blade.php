@extends('layouts.app')

@section('title', 'Catat Transaksi Penjualan')
@section('page_title', 'Catat Transaksi Penjualan')
@section('page_subtitle', 'Input faktur penjualan harian / purchase order produk olahan lemon')

@section('content')
<div class="space-y-6" x-data="transactionForm()">

    <div class="flex items-center justify-between">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Riwayat Transaksi</span>
        </a>
    </div>

    <form method="POST" action="{{ route('transactions.store') }}" @submit="validateForm($event)" class="space-y-6">
        @csrf

        <!-- Customer & Order Meta Information -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900">Informasi Faktur & Pelanggan</h4>
                    <p class="text-xs text-slate-500">Detail identitas pembeli dan tanggal transaksi</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                
                <!-- Invoice Number -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No Faktur / Invoice</label>
                    <input type="text" 
                           name="invoice_number" 
                           value="{{ old('invoice_number', $invoiceNumber) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Transaction Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                    <input type="date" 
                           name="transaction_date" 
                           value="{{ old('transaction_date', date('Y-m-d')) }}" 
                           required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Customer Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Pelanggan / Mitra <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           name="customer_name" 
                           value="{{ old('customer_name') }}" 
                           required 
                           placeholder="Contoh: Toko Berkah / Bu Rahma"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Customer Phone -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. Telepon / WA Pelanggan</label>
                    <input type="text" 
                           name="customer_phone" 
                           value="{{ old('customer_phone') }}" 
                           placeholder="0812-xxxx-xxxx"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Sales Channel -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Saluran Penjualan <span class="text-rose-500">*</span></label>
                    <select name="sales_channel" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Toko Offline">Toko Offline (Outlet Sukalarang)</option>
                        <option value="WhatsApp Order">WhatsApp Order</option>
                        <option value="Reseller / Distributor">Reseller / Distributor</option>
                        <option value="Konsinyasi Kafe">Konsinyasi Kafe & Resto</option>
                        <option value="Shopee / Marketplace">Shopee / Tokopedia / Online</option>
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Cash / Tunai">Cash / Tunai</option>
                        <option value="Transfer BCA">Transfer BCA</option>
                        <option value="Transfer BRI">Transfer BRI</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Tempo / Kredit">Tempo / Konsinyasi</option>
                    </select>
                </div>

                <!-- Payment Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Status Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Lunas">Lunas</option>
                        <option value="Menunggu Pembayaran">Menunggu Pembayaran</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan</label>
                    <input type="text" 
                           name="notes" 
                           value="{{ old('notes') }}" 
                           placeholder="Keterangan pengiriman, alamat penerima, nomor resi..."
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

            </div>
        </div>

        <!-- Multi-Item Order Table (Interactive) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">Daftar Item Produk Olahan</h4>
                        <p class="text-xs text-slate-500">Pilih produk dan tentukan kuantitas yang dibeli</p>
                    </div>
                </div>

                <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl transition border border-emerald-200">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Baris Produk</span>
                </button>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider">
                            <th class="py-3 px-3 w-5/12">Produk Olahan Lemon</th>
                            <th class="py-3 px-3 text-center w-2/12">Qty</th>
                            <th class="py-3 px-3 text-right w-2/12">Harga Satuan (Rp)</th>
                            <th class="py-3 px-3 text-right w-2/12">Subtotal (Rp)</th>
                            <th class="py-3 px-3 text-center w-1/12">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/50">
                                <!-- Product Select -->
                                <td class="py-3 px-3">
                                    <select :name="'items['+index+'][product_id]'" 
                                            x-model="item.product_id" 
                                            @change="onProductChange(index)" 
                                            required 
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-emerald-500 font-medium text-slate-800">
                                        <option value="">-- Pilih Produk Olahan --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-lemon="{{ $p->raw_lemon_requirement }}" data-unit="{{ $p->unit }}">
                                                {{ $p->name }} (Stok: {{ $p->stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-[10px] text-amber-700 font-semibold block mt-1" x-show="item.raw_lemon > 0">
                                        &bull; Lemon segar: <span x-text="(item.raw_lemon * item.quantity).toFixed(2)"></span> Kg
                                    </span>
                                </td>

                                <!-- Quantity -->
                                <td class="py-3 px-3 text-center">
                                    <input type="number" 
                                           :name="'items['+index+'][quantity]'" 
                                           x-model.number="item.quantity" 
                                           @input="calculateTotals()" 
                                           min="1" 
                                           required 
                                           class="w-20 px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-center font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                                </td>

                                <!-- Unit Price -->
                                <td class="py-3 px-3 text-right">
                                    <input type="number" 
                                           :name="'items['+index+'][unit_price]'" 
                                           x-model.number="item.unit_price" 
                                           @input="calculateTotals()" 
                                           min="0" 
                                           required 
                                           class="w-28 px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-right font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500">
                                </td>

                                <!-- Subtotal -->
                                <td class="py-3 px-3 text-right font-black text-slate-900 text-sm whitespace-nowrap">
                                    Rp <span x-text="(item.quantity * item.unit_price).toLocaleString('id-ID')"></span>
                                </td>

                                <!-- Remove Action -->
                                <td class="py-3 px-3 text-center">
                                    <button type="button" @click="removeItem(index)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus baris" x-show="items.length > 1">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Calculation Summary Box -->
            <div class="pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                
                <!-- Left: Raw Material Lemon Indicator -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-200 text-amber-800 font-black text-lg flex items-center justify-center shrink-0">
                        🍋
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider block">Kebutuhan Lemon Segar Transaksi Ini:</span>
                        <div class="text-lg font-black text-amber-950 mt-0.5">
                            <span x-text="totalLemonKg.toFixed(2)"></span> Kg Lemon Segar
                        </div>
                    </div>
                </div>

                <!-- Right: Financial Grand Totals -->
                <div class="space-y-3 bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>Subtotal Produk:</span>
                        <span class="font-bold text-slate-900">Rp <span x-text="subtotalAmount.toLocaleString('id-ID')"></span></span>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>Potongan / Diskon (Rp):</span>
                        <input type="number" 
                               name="discount" 
                               x-model.number="discountAmount" 
                               @input="calculateTotals()" 
                               min="0" 
                               class="w-32 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs text-right font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm">
                        <span class="font-extrabold text-slate-900">Total Akhir:</span>
                        <span class="font-black text-lg text-emerald-700">Rp <span x-text="grandTotal.toLocaleString('id-ID')"></span></span>
                    </div>
                </div>

            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('transactions.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan & Terbitkan Faktur</span>
                </button>
            </div>

        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    function transactionForm() {
        return {
            items: [
                { product_id: '', quantity: 1, unit_price: 0, raw_lemon: 0 }
            ],
            subtotalAmount: 0,
            discountAmount: 0,
            grandTotal: 0,
            totalLemonKg: 0,

            init() {
                this.calculateTotals();
            },

            addItem() {
                this.items.push({ product_id: '', quantity: 1, unit_price: 0, raw_lemon: 0 });
                setTimeout(() => lucide.createIcons(), 50);
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                    this.calculateTotals();
                }
            },

            onProductChange(index) {
                const selectEl = document.querySelector(`select[name="items[${index}][product_id]"]`);
                if (selectEl && selectEl.selectedIndex > 0) {
                    const option = selectEl.options[selectEl.selectedIndex];
                    const price = parseFloat(option.dataset.price) || 0;
                    const lemon = parseFloat(option.dataset.lemon) || 0;
                    this.items[index].unit_price = price;
                    this.items[index].raw_lemon = lemon;
                } else {
                    this.items[index].unit_price = 0;
                    this.items[index].raw_lemon = 0;
                }
                this.calculateTotals();
            },

            calculateTotals() {
                let sum = 0;
                let lemonSum = 0;
                this.items.forEach(it => {
                    const q = parseFloat(it.quantity) || 0;
                    const p = parseFloat(it.unit_price) || 0;
                    const l = parseFloat(it.raw_lemon) || 0;
                    sum += (q * p);
                    lemonSum += (q * l);
                });
                this.subtotalAmount = sum;
                this.totalLemonKg = lemonSum;
                this.grandTotal = Math.max(0, this.subtotalAmount - (parseFloat(this.discountAmount) || 0));
            },

            validateForm(e) {
                for (let i = 0; i < this.items.length; i++) {
                    if (!this.items[i].product_id) {
                        alert('Silakan pilih produk pada setiap baris item transaksi.');
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            }
        };
    }
</script>
@endpush
