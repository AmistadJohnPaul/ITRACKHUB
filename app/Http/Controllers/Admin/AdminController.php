<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $products = Product::with('category')->latest()->get();
        $lowStock = Product::where('quantity', '<', 5)->count();
        $totalStaff = User::where('role', 'staff')->count();
        $recentMovements = StockMovement::with('product')->latest()->take(10)->get();

        return view('admin.dashboard', compact('products', 'lowStock', 'totalStaff', 'recentMovements'));
    }

    public function destroyProduct(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.dashboard')->with('success', "\"{$name}\" was removed from inventory.");
    }

    public function staffIndex()
    {
        $staff = User::whereIn('role', ['staff', 'admin'])->get();
        return view('admin.staff', compact('staff'));
    }

    public function staffStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:staff,admin',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.staff')->with('success', 'Account created.');
    }

    public function staffDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "You can't delete your own account.");
        }

        $user->delete();

        return redirect()->route('admin.staff')->with('success', 'Account removed.');
    }
}