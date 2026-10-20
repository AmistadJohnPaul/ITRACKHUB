<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        $totalSales = Sale::whereBetween('created_at', [$from, $to])->sum('total_amount');
        $transactions = Sale::whereBetween('created_at', [$from, $to])->count();
        $itemsSold = SaleItem::whereBetween('created_at', [$from, $to])->sum('quantity');
        $average = $transactions ? $totalSales / $transactions : 0;

        $daily = Sale::whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total_amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $top = SaleItem::select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        $lowStock = Product::needsRestock()->orderBy('quantity')->limit(15)->get();

        return view('reports.index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'totalSales' => $totalSales,
            'transactions' => $transactions,
            'itemsSold' => $itemsSold,
            'average' => $average,
            'daily' => $daily,
            'top' => $top,
            'lowStock' => $lowStock,
        ]);
    }
}