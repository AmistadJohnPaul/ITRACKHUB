<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = $this->filtered($request)
            ->withSum('items', 'quantity')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $periodTotal = $this->filtered($request)->sum('total_amount');

        return view('sales.index', compact('sales', 'periodTotal'));
    }

    public function show(Sale $sale)
    {
        $sale->load('items', 'user');

        return view('sales.show', compact('sale'));
    }

    // Download the filtered list as a CSV file (opens in Excel)
    public function export(Request $request)
    {
        $sales = $this->filtered($request)->withSum('items', 'quantity')->latest()->get();

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Receipt No', 'Date & Time', 'Items', 'Total Amount', 'Payment Method']);
            foreach ($sales as $sale) {
                fputcsv($out, [
                    $sale->receipt_no,
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->items_sum_quantity,
                    $sale->total_amount,
                    $sale->payment_method,
                ]);
            }
            fclose($out);
        }, 'sales-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function filtered(Request $request)
    {
        $query = Sale::query();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($search = trim((string) $request->search)) {
            $query->where('receipt_no', 'like', "%{$search}%");
        }

        return $query;
    }
}