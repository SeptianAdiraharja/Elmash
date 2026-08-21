<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesTransactionController extends Controller
{
    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $channel = $request->get('channel');
        $status = $request->get('status');

        $query = SalesTransaction::with(['items.product', 'user']);

        if ($search) {
            $query->search($search);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        if ($channel) {
            $query->where('sales_channel', $channel);
        }

        if ($status) {
            $query->where('payment_status', $status);
        }

        // Clone query for metrics
        $metricQuery = clone $query;
        $totalOmset = (float) $metricQuery->where('payment_status', '!=', 'Dibatalkan')->sum('total_amount');
        $totalCount = $metricQuery->count();

        $transactions = $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $channels = ['Toko Offline', 'WhatsApp Order', 'Reseller / Distributor', 'Konsinyasi Kafe', 'Shopee / Marketplace'];
        $statuses = ['Lunas', 'Menunggu Pembayaran', 'Dibatalkan'];

        return view('transactions.index', compact(
            'transactions',
            'search',
            'startDate',
            'endDate',
            'channel',
            'status',
            'channels',
            'statuses',
            'totalOmset',
            'totalCount'
        ));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(SalesTransaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        return view('transactions.create', compact('products', 'invoiceNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'sales_channel' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'payment_status' => ['required', 'string'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'items.required' => 'Minimal harus menambahkan 1 item produk.',
        ]);

        DB::beginTransaction();
        try {
            $itemsData = $request->input('items', []);
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($itemsData as $row) {
                $product = Product::find($row['product_id']);
                if (!$product) continue;

                $qty = (int) $row['quantity'];
                $price = (float) $row['unit_price'];
                $rowSubtotal = $qty * $price;
                $rawLemon = $qty * (float) $product->raw_lemon_requirement;

                $subtotal += $rowSubtotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'cost_price' => (float) $product->cost_price,
                    'subtotal' => $rowSubtotal,
                    'raw_lemon_used' => $rawLemon,
                ];

                // Decrement stock if active & paid
                if ($request->input('payment_status') !== 'Dibatalkan') {
                    $product->decrement('stock', $qty);
                }
            }

            $discount = (float) $request->input('discount', 0);
            $totalAmount = max(0, $subtotal - $discount);

            $invoiceNumber = $request->input('invoice_number');
            if (!$invoiceNumber) {
                $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(SalesTransaction::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            $transaction = SalesTransaction::create([
                'invoice_number' => $invoiceNumber,
                'transaction_date' => $request->input('transaction_date'),
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'sales_channel' => $request->input('sales_channel'),
                'payment_method' => $request->input('payment_method'),
                'payment_status' => $request->input('payment_status'),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total_amount' => $totalAmount,
                'notes' => $request->input('notes'),
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsToCreate as $it) {
                $it['sales_transaction_id'] = $transaction->id;
                SalesTransactionItem::create($it);
            }

            DB::commit();
            return redirect()->route('transactions.show', $transaction)->with('success', "Transaksi {$transaction->invoice_number} berhasil dicatat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function show(SalesTransaction $transaction)
    {
        $transaction->load(['items.product.category', 'user']);
        return view('transactions.show', compact('transaction'));
    }

    public function edit(SalesTransaction $transaction)
    {
        $transaction->load('items.product');
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('transactions.edit', compact('transaction', 'products'));
    }

    public function update(Request $request, SalesTransaction $transaction)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'sales_channel' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'payment_status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.show', $transaction)->with('success', "Informasi transaksi {$transaction->invoice_number} berhasil diperbarui.");
    }

    public function destroy(SalesTransaction $transaction)
    {
        $invoice = $transaction->invoice_number;
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', "Transaksi {$invoice} berhasil dihapus.");
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        return $this->exportService->exportSalesPdf($startDate, $endDate);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        return $this->exportService->exportSalesExcel($startDate, $endDate);
    }
}
