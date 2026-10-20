<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function scanPage()
    {
        return view('scan.index');
    }

    public function handleScan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'type' => 'required|in:in,out',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::where('barcode', $request->barcode)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'No product found for barcode ' . $request->barcode,
            ], 404);
        }

        $qty = $request->quantity ?? 1;

        if ($request->type === 'in') {
            $product->increment('quantity', $qty);
        } else {
            if ($product->quantity < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock to remove.',
                ], 422);
            }
            $product->decrement('quantity', $qty);
        }

        StockMovement::create([
            'product_id' => $product->id,
            'type' => $request->type,
            'quantity' => $qty,
            'scanned_by' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$product->name} updated.",
            'product' => $product->fresh(),
        ]);
    }

    public function quickAdd(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:products,barcode',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => "{$product->name} added with {$product->quantity} in stock.",
        ]);
    }

    public function bulkRestockPage()
    {
        $products = Product::orderBy('name')->get();
        return view('scan.bulk-restock', compact('products'));
    }

    public function bulkRestockSave(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $product->increment('quantity', $item['quantity']);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $item['quantity'],
                'scanned_by' => auth()->user()->name . ' (bulk restock)',
            ]);
        }

        return redirect()->route('scan.index')->with('success', 'Truck unloaded — inventory updated.');
    }
}