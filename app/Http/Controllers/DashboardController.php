<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'staff') {
            return redirect()->route('pos.index');
        }
        if ($user->role !== 'admin') {
            return redirect()->route('lookup.index');
        }

        // Sales overview chart: this week (7 days) or this month (30 days)
        $range = $request->query('range') === 'month' ? 'month' : 'week';
        $days = $range === 'month' ? 30 : 7;
        $start = now()->subDays($days - 1)->startOfDay();

        $daily = Sale::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $date->format('M j');
            $values[] = (float) ($daily[$date->toDateString()] ?? 0);
        }

        // Stat cards
        $todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $itemsSold = SaleItem::whereDate('created_at', today())->sum('quantity');
        $totalProducts = Product::count();
        $lowStock = Product::needsRestock()->count();

        // Top selling products
        $topProducts = SaleItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as sold'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');

        return view('dashboard.index', compact(
            'greeting', 'todaySales', 'itemsSold', 'totalProducts', 'lowStock',
            'topProducts', 'labels', 'values', 'range'
        ));
    }
}