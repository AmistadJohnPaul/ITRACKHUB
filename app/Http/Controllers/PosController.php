<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function index()
    {
        $autoAdd = (bool) Setting::get('pos_auto_add', 0);

        return view('pos.index', compact('autoAdd'));
    }

    // Called every time a barcode is scanned or typed (Web & Mobile Lookup)
    public function lookup(Request $request)
    {
        $rawBarcode = (string) $request->query('barcode');
        
        // Strip all invisible formatting characters, spaces, and line breaks from the camera
        $barcode = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', trim($rawBarcode));
        
        // Log to storage/logs/laravel.log so you can inspect what the phone actually sent
        Log::info("Phone/Scanner scanned raw: [{$rawBarcode}] cleaned: [{$barcode}]");

        $product = Product::with('category')->where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'found' => false,
                'message' => "No item found for barcode [{$barcode}].",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'category' => $product->category->name ?? '-',
                'aisle' => $product->aisle ?: 'Not specified',
                'price' => (float) $product->price,
                'stock' => (int) $product->quantity,
                'image' => $product->image_url,
            ]
        ]);
    }

    // Single item quick-sell / stock deduction endpoint for the Flutter mobile app
    public function sell(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $rawBarcode = (string) $request->input('barcode');
        $barcode = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', trim($rawBarcode));
        $qty = (int) $request->input('quantity', 1);

        try {
            $sale = DB::transaction(function () use ($barcode, $qty, $request) {
                $product = Product::where('barcode', $barcode)->lockForUpdate()->first();

                if (!$product) {
                    throw new \RuntimeException("Product not found for barcode: {$barcode}");
                }

                if ($product->quantity < $qty) {
                    throw new \RuntimeException("Not enough stock for {$product->name} (only {$product->quantity} left).");
                }

                $subtotal = (float) ($product->price * $qty);

                // Dynamically find an existing user ID so it never hits foreign key violations
                $fallbackUserId = User::value('id') ?? 1;

                $sale = Sale::create([
                    'receipt_no' => 'TMP' . uniqid(),
                    'user_id' => $request->user() ? $request->user()->id : $fallbackUserId, 
                    'total_amount' => $subtotal,
                    'payment_method' => 'Cash',
                ]);
                $sale->update(['receipt_no' => '#' . str_pad($sale->id, 7, '0', STR_PAD_LEFT)]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('quantity', $qty);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $qty,
                    'scanned_by' => ($request->user() ? $request->user()->name : 'Mobile API') . ' (sale ' . $sale->receipt_no . ')',
                ]);

                return $sale;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Checkout successful! Stock updated.',
            'receipt_no' => $sale->receipt_no,
            'total' => (float) $sale->total_amount,
        ]);
    }

    // Standard web multi-item checkout
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'nullable|in:Cash,GCash,Card',
        ]);

        // Merge duplicate lines of the same product
        $wanted = [];
        foreach ($request->items as $item) {
            $id = (int) $item['product_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $item['quantity'];
        }

        try {
            $sale = DB::transaction(function () use ($wanted, $request) {
                $products = Product::whereIn('id', array_keys($wanted))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $total = 0;
                foreach ($wanted as $id => $qty) {
                    $product = $products[$id];
                    if ($product->quantity < $qty) {
                        throw new \RuntimeException("Not enough stock for {$product->name} (only {$product->quantity} left).");
                    }
                    $total += $product->price * $qty;
                }

                $sale = Sale::create([
                    'receipt_no' => 'TMP' . uniqid(),
                    'user_id' => $request->user()->id,
                    'total_amount' => $total,
                    'payment_method' => $request->input('payment_method', 'Cash'),
                ]);
                $sale->update(['receipt_no' => '#' . str_pad($sale->id, 7, '0', STR_PAD_LEFT)]);

                foreach ($wanted as $id => $qty) {
                    $product = $products[$id];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $product->price,
                        'subtotal' => $product->price * $qty,
                    ]);

                    $product->decrement('quantity', $qty);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $qty,
                        'scanned_by' => $request->user()->name . ' (sale ' . $sale->receipt_no . ')',
                    ]);
                }

                return $sale;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'receipt_no' => $sale->receipt_no,
            'total' => (float) $sale->total_amount,
        ]);
    }
}