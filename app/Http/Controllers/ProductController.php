<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SalesTransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $status = $request->get('status');

        $query = Product::with('category');

        if ($search) {
            $query->search($search);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status == '1');
        }

        $products = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();
        $categories = Category::all();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId', 'status'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'raw_lemon_requirement' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'code.required' => 'Kode SKU produk wajib diisi.',
            'code.unique' => 'Kode SKU sudah terdaftar.',
            'name.required' => 'Nama produk wajib diisi.',
            'raw_lemon_requirement.required' => 'Kebutuhan lemon segar wajib diisi.',
            'cost_price.required' => 'Harga pokok / modal wajib diisi.',
            'selling_price.required' => 'Harga jual wajib diisi.',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['is_active'] = $request->has('is_active');

        $product = Product::create($validated);

        return redirect()->route('products.index')->with('success', "Produk '{$product->name}' berhasil ditambahkan.");
    }

    public function show(Product $product)
    {
        $product->load(['category']);

        $recentSales = SalesTransactionItem::with('transaction')
            ->where('product_id', $product->id)
            ->whereHas('transaction', function ($q) {
                $q->where('payment_status', '!=', 'Dibatalkan');
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $totalUnitsSold = SalesTransactionItem::where('product_id', $product->id)
            ->whereHas('transaction', function ($q) {
                $q->where('payment_status', '!=', 'Dibatalkan');
            })->sum('quantity');

        $totalRevenue = SalesTransactionItem::where('product_id', $product->id)
            ->whereHas('transaction', function ($q) {
                $q->where('payment_status', '!=', 'Dibatalkan');
            })->sum('subtotal');

        $totalRawLemonUsed = SalesTransactionItem::where('product_id', $product->id)
            ->whereHas('transaction', function ($q) {
                $q->where('payment_status', '!=', 'Dibatalkan');
            })->sum('raw_lemon_used');

        return view('products.show', compact('product', 'recentSales', 'totalUnitsSold', 'totalRevenue', 'totalRawLemonUsed'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:50', 'unique:products,code,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'raw_lemon_requirement' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', "Data produk '{$product->name}' berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        // Check if product has transactions
        if ($product->transactionItems()->count() > 0) {
            // Soft deactivate instead of hard delete to preserve historical integrity
            $product->update(['is_active' => false]);
            return redirect()->route('products.index')->with('info', "Produk '{$name}' telah memiliki riwayat transaksi, status diubah menjadi Non-Aktif.");
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', "Produk '{$name}' berhasil dihapus.");
    }
}
