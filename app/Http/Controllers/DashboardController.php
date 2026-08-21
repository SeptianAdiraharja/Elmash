<?php

namespace App\Http\Controllers;

use App\Models\ClusteringAnalysis;
use App\Models\Product;
use App\Models\RawLemonStock;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Overall & Current Month Sales Metrics
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        $totalRevenue = (float) SalesTransaction::where('payment_status', '!=', 'Dibatalkan')->sum('total_amount');
        $totalTransactions = SalesTransaction::where('payment_status', '!=', 'Dibatalkan')->count();
        $totalUnitsSold = (int) SalesTransactionItem::whereHas('transaction', function ($q) {
            $q->where('payment_status', '!=', 'Dibatalkan');
        })->sum('quantity');

        $totalRawLemonKg = (float) SalesTransactionItem::whereHas('transaction', function ($q) {
            $q->where('payment_status', '!=', 'Dibatalkan');
        })->sum('raw_lemon_used');

        $monthRevenue = (float) SalesTransaction::where('payment_status', '!=', 'Dibatalkan')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $totalProducts = Product::where('is_active', true)->count();

        // 2. Latest Clustering Analysis
        $latestAnalysis = ClusteringAnalysis::with(['results.product.category'])->latest()->first();

        // 3. Top 5 Best-Selling Products
        $topProducts = SalesTransactionItem::whereHas('transaction', function ($q) {
                $q->where('payment_status', '!=', 'Dibatalkan');
            })
            ->select('product_id', 'product_name', 'product_code', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_omset'))
            ->groupBy('product_id', 'product_name', 'product_code')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 4. Monthly Sales Trends (Last 12 Months)
        $monthlyData = DB::table('sales_transactions')
            ->where('payment_status', '!=', 'Dibatalkan')
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key"),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(id) as tx_count')
            )
            ->groupBy('month_key')
            ->orderBy('month_key', 'asc')
            ->get();

        $chartMonths = [];
        $chartRevenues = [];
        $chartTxCounts = [];

        foreach ($monthlyData as $row) {
            try {
                $dateObj = Carbon::createFromFormat('Y-m', $row->month_key);
                $chartMonths[] = $dateObj->translatedFormat('M Y');
            } catch (\Exception $e) {
                $chartMonths[] = $row->month_key;
            }
            $chartRevenues[] = (float) $row->revenue;
            $chartTxCounts[] = (int) $row->tx_count;
        }

        // 5. Sales Channel Distribution
        $channelDistribution = DB::table('sales_transactions')
            ->where('payment_status', '!=', 'Dibatalkan')
            ->select('sales_channel', DB::raw('SUM(total_amount) as total_omset'), DB::raw('COUNT(id) as count'))
            ->groupBy('sales_channel')
            ->get();

        // 6. Raw Lemon Stock Condition (from thesis table)
        $lemonStocks = RawLemonStock::orderBy('period_month', 'desc')->limit(6)->get();

        // 7. Recent Transactions
        $recentTransactions = SalesTransaction::with('items')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        return view('dashboard.index', compact(
            'totalRevenue',
            'totalTransactions',
            'totalUnitsSold',
            'totalRawLemonKg',
            'monthRevenue',
            'totalProducts',
            'latestAnalysis',
            'topProducts',
            'chartMonths',
            'chartRevenues',
            'chartTxCounts',
            'channelDistribution',
            'lemonStocks',
            'recentTransactions'
        ));
    }
}
