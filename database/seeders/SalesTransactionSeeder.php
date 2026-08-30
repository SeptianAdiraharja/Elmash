<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $driedLemon = Product::where('code', 'DRIED-LMN-001')->firstOrFail();
        $manisanLemon = Product::where('code', 'MANISAN-LMN-001')->firstOrFail();
        $sariLemon = Product::where('code', 'SARI-LMN-001')->firstOrFail();

        $startDate = Carbon::parse('2025-01-02');
        $endDate = Carbon::parse('2025-10-28');
        $totalDays = $startDate->diffInDays($endDate) + 1; // = 300

        // Proporsi kira-kira mengikuti Tabel 3.12 skripsi: Tinggi 39,7% / Rendah 32,7% / Sedang 27,7%
        $tinggiCount = (int) round($totalDays * 0.397); // ~119
        $rendahCount = (int) round($totalDays * 0.327); // ~98
        $sedangCount = $totalDays - $tinggiCount - $rendahCount; // sisanya ~83

        $dayTypes = array_merge(
            array_fill(0, $tinggiCount, 'tinggi'),
            array_fill(0, $rendahCount, 'rendah'),
            array_fill(0, $sedangCount, 'sedang')
        );

        mt_srand(42); // agar hasil seeder reproducible
        shuffle($dayTypes);

        // Rentang nilai per tipe hari, disusun agar berpusat di sekitar rata-rata Tabel 3.12
        $ranges = [
            'tinggi' => ['x1' => [20, 30], 'x2' => [1600, 2900], 'x3' => [1900, 3000]],
            'rendah' => ['x1' => [3, 14],  'x2' => [1100, 2000], 'x3' => [900, 2200]],
            'sedang' => ['x1' => [8, 20],  'x2' => [1900, 2900], 'x3' => [2400, 4000]],
        ];

        $invoiceCounter = 1;
        $date = $startDate->copy();

        for ($i = 0; $i < $totalDays; $i++) {
            $range = $ranges[$dayTypes[$i]];

            $x1 = rand($range['x1'][0], $range['x1'][1]);
            $x2 = rand($range['x2'][0], $range['x2'][1]);
            $x3 = rand($range['x3'][0], $range['x3'][1]);

            $transaction = SalesTransaction::create([
                'invoice_number' => 'INV-' . $date->format('Ymd') . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT),
                'transaction_date' => $date->toDateString(),
                'customer_name' => 'Pelanggan Harian',
                'sales_channel' => 'Toko Offline',
                'payment_method' => 'Cash / Tunai',
                'payment_status' => 'Lunas',
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total_amount' => 0,
            ]);

            $items = [
                ['product' => $driedLemon, 'qty' => $x1],
                ['product' => $manisanLemon, 'qty' => $x2],
                ['product' => $sariLemon, 'qty' => $x3],
            ];

            $subtotal = 0;
            foreach ($items as $it) {
                $p = $it['product'];
                $lineSubtotal = $p->selling_price * $it['qty'];
                $subtotal += $lineSubtotal;

                SalesTransactionItem::create([
                    'sales_transaction_id' => $transaction->id,
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'product_code' => $p->code,
                    'quantity' => $it['qty'],
                    'unit_price' => $p->selling_price,
                    'cost_price' => $p->cost_price,
                    'subtotal' => $lineSubtotal,
                    'raw_lemon_used' => round($it['qty'] * $p->raw_lemon_requirement, 3),
                ]);
            }

            $transaction->update(['subtotal' => $subtotal, 'total_amount' => $subtotal]);

            $date->addDay();
        }
    }
}