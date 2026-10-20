<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($search = trim((string) $request->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        switch ($request->status) {
            case 'in':  $query->inStock(); break;
            case 'low': $query->lowStock(); break;
            case 'out': $query->outOfStock(); break;
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('image')) {
            $data['image'] = $this->saveImage($request->file('image'));
        }

        $product = Product::create($data);

        if ($product->quantity > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $product->quantity,
                'scanned_by' => $request->user()->name . ' (new item)',
            ]);
        }

        return redirect()->route('products.index')->with('success', "\"{$product->name}\" was added.");
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->rules($product));

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image);
            $data['image'] = $this->saveImage($request->file('image'));
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', "\"{$product->name}\" was updated.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $this->deleteImage($product->image);
        $product->delete();

        return redirect()->route('products.index')->with('success', "\"{$name}\" was deleted.");
    }

    private function rules(?Product $product = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'barcode' => ['required', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product?->id)],
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'aisle' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    private function saveImage($file): string
    {
        $name = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/products'), $name);

        return $name;
    }

    private function deleteImage(?string $name): void
    {
        if ($name) {
            File::delete(public_path('uploads/products/' . $name));
        }
    }
}