<?php

namespace App\Http\Controllers;

use App\Models\RawLemonStock;
use Illuminate\Http\Request;

class RawLemonStockController extends Controller
{
    public function index()
    {
        $stocks = RawLemonStock::orderBy('period_month', 'desc')->paginate(12);

        $totalOverstockKg = RawLemonStock::where('status', 'Kelebihan')->sum('quantity_kg');
        $totalStockoutKg = RawLemonStock::where('status', 'Kekurangan')->sum('quantity_kg');
        $balancedMonths = RawLemonStock::where('status', 'Seimbang')->count();

        return view('lemon_stocks.index', compact('stocks', 'totalOverstockKg', 'totalStockoutKg', 'balancedMonths'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_month' => ['required', 'string', 'max:7', 'unique:raw_lemon_stocks,period_month'],
            'status' => ['required', 'in:Kelebihan,Kekurangan,Seimbang'],
            'quantity_kg' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'period_month.required' => 'Bulan periode wajib diisi (YYYY-MM).',
            'period_month.unique' => 'Data untuk periode bulan ini sudah ada.',
            'status.required' => 'Status kondisi stok wajib dipilih.',
        ]);

        RawLemonStock::create($validated);

        return redirect()->route('lemon-stocks.index')->with('success', 'Catatan stok lemon segar berhasil ditambahkan.');
    }

    public function update(Request $request, RawLemonStock $lemonStock)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Kelebihan,Kekurangan,Seimbang'],
            'quantity_kg' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $lemonStock->update($validated);

        return redirect()->route('lemon-stocks.index')->with('success', 'Catatan stok lemon segar berhasil diperbarui.');
    }

    public function destroy(RawLemonStock $lemonStock)
    {
        $lemonStock->delete();
        return redirect()->route('lemon-stocks.index')->with('success', 'Catatan stok lemon segar berhasil dihapus.');
    }
}
