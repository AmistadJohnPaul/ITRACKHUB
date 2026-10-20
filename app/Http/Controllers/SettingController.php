<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:100',
            'owner_name' => 'nullable|string|max:100',
            'store_email' => 'nullable|email|max:150',
            'contact_number' => 'nullable|string|max:30',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        foreach (['store_name', 'owner_name', 'store_email', 'contact_number'] as $key) {
            Setting::set($key, $data[$key] ?? null);
        }

        if ($request->hasFile('logo')) {
            if ($old = Setting::get('store_logo')) {
                File::delete(public_path('uploads/logo/' . $old));
            }
            $name = Str::random(20) . '.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('uploads/logo'), $name);
            Setting::set('store_logo', $name);
        }

        return back()->with('success', 'Store information saved.');
    }

    public function barcode()
    {
        return view('settings.barcode');
    }

    public function barcodeUpdate(Request $request)
    {
        $data = $request->validate([
            'low_stock_threshold' => 'required|integer|min:1|max:1000',
        ]);

        Setting::set('low_stock_threshold', $data['low_stock_threshold']);
        Setting::set('pos_auto_add', $request->boolean('pos_auto_add') ? 1 : 0);

        return back()->with('success', 'Barcode settings saved.');
    }
}