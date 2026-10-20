<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index()
    {
        return view('lookup.index');
    }

    public function check(Request $request)
    {
        $request->validate(['barcode' => 'required|string']);

        $product = Product::where('barcode', $request->barcode)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'name' => $product->name,
            'price' => number_format($product->price, 2),
            'aisle' => $product->aisle ?? 'Not specified',
            'location' => $product->location,
        ]);
    }
}