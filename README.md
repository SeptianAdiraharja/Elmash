# Sistem Informasi Segmentasi Penjualan Produk Olahan Lemon Menggunakan Algoritma K-Means Clustering pada UMKM Elmas Fresh

Sistem berbasis web modern yang dibangun menggunakan **Laravel 12**, **Tailwind CSS**, **Alpine.js**, dan **Chart.js** untuk memenuhi kebutuhan operasional dan analisis data mining pada **UMKM Elmas Fresh (Sukabumi)**.

---

## 🌟 Fitur Utama Sistem

### 1. Autentikasi & Keamanan (Admin)
- **Login Administrator** dengan tampilan elegan bertema *fresh citrus*.
- **Manajemen Akun / Profil**: Ubah nama, email, nomor telepon, dan ganti kata sandi dengan validasi keamanan.
- **Logout** aman dengan regenerasi sesi.

### 2. Manajemen Data Master Produk Olahan Lemon
- **CRUD Produk Lengkap**: SKU / Kode Produk, Nama Produk, Kategori, Satuan Kemasan, Kebutuhan Lemon Segar per Unit ($Kg$), HPP (Harga Pokok), Harga Jual, Stok Aktual, dan Batas Peringatan Stok Minimum.
- **Pencarian Real-time & Filter Kategori**: Memudahkan pencarian katalog produk olahan.
- **Manajemen Kategori Produk**: Pengelompokan varian produk.

### 3. Manajemen Transaksi Penjualan
- **Pencatatan Faktur / PO Harian**: Multi-item produk per transaksi, kalkulator otomatis subtotal, diskon, grand total, dan estimasi serapan buah lemon segar ($Kg$).
- **Multi-Saluran Penjualan**: Toko Offline, WhatsApp Order, Reseller / Distributor, Konsinyasi Kafe, Shopee / Marketplace.
- **Cetak Faktur Penjualan (Invoice)** & **Export Rekap Transaksi ke format PDF & Excel**.

### 4. Eksekusi Analisis K-Means Clustering
- **Studio Segmentasi**: Pemilihan rentang tanggal analisis ($StartDate$ s/d $EndDate$), pemilihan parameter jumlah klaster $k \in [2, 5]$, inisialisasi $K\text{-Means++}$, dan batas maksimum iterasi.
- **Proses Data Mining Transparan**:
  - Normalisasi Min-Max $[0, 1]$
  - Perhitungan Jarak Euclidean Distance
  - Iterasi Pusat Centroid hingga Konvergen
  - Evaluasi Kualitas Klaster: *Sum of Squared Errors (SSE / Inertia)* dan *Davies-Bouldin Index (DBI)*
- **Pelabelan Otomatis Klaster**:
  - **C1**: Penjualan Tinggi (Sangat Laris / Produk Unggulan) - Emerald Green
  - **C2**: Penjualan Sedang (Cukup Diminati) - Amber Yellow
  - **C3**: Penjualan Rendah (Kurang Diminati / Slow Moving) - Rose Red
- **Visualisasi Interaktif**: Grafik Scatter Plot 2D (Volume Terjual vs Omset Penjualan).
- **Rekomendasi Manajerial Persediaan**: Strategi alokasi pengadaan bahan baku buah lemon segar untuk mencegah *overstock* dan *stockout*.

### 5. Monitoring & Evaluasi Antarperiode
- **Executive Dashboard**: Ringkasan omset penjualan, total unit terjual, lemon segar terserap, grafik tren bulanan, komposisi saluran penjualan, serta Top 5 produk terlaris.
- **Riwayat Analisis Clustering**: Log tersimpan dari setiap proses segmentasi yang pernah dijalankan.
- **Komparasi Antarperiode**: Membandingkan hasil analisis periode A vs periode B dengan indikator trajektori pergeseran performa produk (Naik Kelas 🚀, Turun Kelas 🔻, atau Stabil ⏸).
- **Pengawasan Fluktuasi Bahan Baku Lemon Segar**: Pelacak data overstock dan stockout riil (Sesuai Bab 1 Skripsi).

### 6. Pelaporan & Export
- **Export Hasil Segmentasi K-Means ke PDF**: Format cetak elegan berlogo UMKM Elmas Fresh dan kolom tanda tangan pimpinan.
- **Export Hasil Segmentasi K-Means ke Excel (XLSX)**: Format spreadsheet multi-sheet (Hasil Segmentasi + Ringkasan Klaster).
- **Export Laporan Penjualan ke PDF & Excel**: Rekapitulasi transaksi penjualan terfilter rentang tanggal.

---

## 🔑 Kredensial Pengguna Default

| Role | Email | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@elmasfresh.id` | `admin123` | Administrator Utama (Salsabila Rifa'i) |
| **Admin 2** | `alif@elmasfresh.id` | `password` | Operator Transaksi (Alif Syada Mukti) |

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Masuk ke Direktori Proyek
```bash
cd C:\Users\user\OneDrive\Desktop\Salsabila\elmas_fresh
```

### 2. Jalankan Server Pengembangan Laravel
```bash
php artisan serve
```

Aplikasi dapat langsung diakses melalui browser di alamat:
👉 **`http://127.0.0.1:8000`** atau **`http://localhost:8000`**

### 3. (Opsional) Menjalankan Uji Otomatis
```bash
php artisan test
```
