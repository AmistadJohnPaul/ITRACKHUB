<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['in', 'low', 'out']) ? $request->query('status') : 'all';

        $query = Product::with('category');

        if ($search = trim((string) $request->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        switch ($status) {
            case 'in':  $query->inStock(); break;
            case 'low': $query->lowStock(); break;
            case 'out': $query->outOfStock(); break;
        }

        $products = $query->orderBy('name')->paginate(10)->withQueryString();

        $counts = [
            'all' => Product::count(),
            'in' => Product::inStock()->count(),
            'low' => Product::lowStock()->count(),
            'out' => Product::outOfStock()->count(),
        ];

        return view('inventory.index', compact('products', 'counts', 'status'));
    }

    // Manual stock adjustment: add stock (delivery) or remove stock (damaged / lost)
    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1|max:100000',
        ]);

        if ($data['type'] === 'out' && $data['quantity'] > $product->quantity) {
            return back()->with('error', "Only {$product->quantity} in stock. Cannot remove {$data['quantity']}.");
        }

        DB::transaction(function () use ($product, $data, $request) {
            if ($data['type'] === 'in') {
                $product->increment('quantity', $data['quantity']);
            } else {
                $product->decrement('quantity', $data['quantity']);
            }

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'scanned_by' => $request->user()->name . ' (manual adjust)',
            ]);
        });

        $verb = $data['type'] === 'in' ? 'added to' : 'removed from';

        return back()->with('success', "{$data['quantity']} unit(s) {$verb} \"{$product->name}\".");
    }
}